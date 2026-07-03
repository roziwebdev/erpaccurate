<?php
// app/Http/Controllers/Sales/SalesReceiptController.php
namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\SalesReceipt;
use App\Models\SalesReceiptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReceiptController extends Controller
{
    public function index(Request $request)
    {
        $cid      = auth()->user()->company_id;
        $query    = SalesReceipt::where('company_id',$cid)->with(['contact','account','createdBy'])->latest();
        if ($request->filled('status'))    $query->where('status',$request->status);
        if ($request->filled('search'))    $query->where(fn($q)=>$q->where('number','like',"%{$request->search}%")->orWhereHas('contact',fn($c)=>$c->where('name','like',"%{$request->search}%")));
        if ($request->filled('date_from')) $query->whereDate('date','>=',$request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('date','<=',$request->date_to);
        $receipts = $query->paginate(15)->withQueryString();
        return view('sales.receipts.index', compact('receipts'));
    }

    public function create(Request $request)
    {
        $cid       = auth()->user()->company_id;
        $customers = Contact::where('company_id',$cid)->whereIn('type',['customer','both'])->where('is_active',true)->orderBy('name')->get();
        $cashAccounts = Account::where('company_id',$cid)->whereIn('sub_type',['cash','bank'])->where('is_active',true)->orderBy('code')->get();
        $nextNumber = $this->generateNumber($cid);

        // Pre-fill if coming from invoice
        $selectedInvoice = null;
        $selectedContact = null;
        $pendingInvoices = collect();

        if ($request->filled('invoice_id')) {
            $selectedInvoice = SalesInvoice::with('contact')->findOrFail($request->invoice_id);
            $selectedContact = $selectedInvoice->contact;
            $pendingInvoices = SalesInvoice::where('company_id',$cid)->where('contact_id',$selectedContact->id)->whereIn('status',['sent','partial','overdue'])->with('contact')->get();
        }

        return view('sales.receipts.create', compact('customers','cashAccounts','nextNumber','selectedInvoice','selectedContact','pendingInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_id'   => 'required|exists:contacts,id',
            'date'         => 'required|date',
            'account_id'   => 'required|exists:accounts,id',
            'invoices'     => 'required|array|min:1',
        ]);

        DB::transaction(function() use ($request){
            $cid    = auth()->user()->company_id;
            $total  = collect($request->invoices)->sum(fn($i)=>floatval($i['amount']??0));

            $receipt = SalesReceipt::create([
                'company_id'   => $cid,
                'contact_id'   => $request->contact_id,
                'created_by'   => auth()->id(),
                'number'       => $request->number ?? $this->generateNumber($cid),
                'date'         => $request->date,
                'status'       => 'draft',
                'reference'    => $request->reference,
                'notes'        => $request->notes,
                'amount'       => $total,
                'currency_code'=> 'IDR',
                'exchange_rate'=> 1,
                'account_id'   => $request->account_id,
            ]);

            foreach($request->invoices as $inv){
                if(floatval($inv['amount']??0) > 0){
                    SalesReceiptItem::create([
                        'sales_receipt_id' => $receipt->id,
                        'sales_invoice_id' => $inv['invoice_id'],
                        'amount'           => floatval($inv['amount']),
                        'discount'         => floatval($inv['discount']??0),
                    ]);
                }
            }
        });

        return redirect()->route('sales.receipts.index')->with('success','Penerimaan kas berhasil disimpan!');
    }

    public function show(SalesReceipt $salesReceipt)
    {
        $salesReceipt->load(['contact','account','items.invoice','createdBy']);
        return view('sales.receipts.show', compact('salesReceipt'));
    }

    public function post(SalesReceipt $salesReceipt)
    {
        if($salesReceipt->status !== 'draft') return back()->with('error','Sudah diposting!');

        DB::transaction(function() use ($salesReceipt){
            $salesReceipt->load(['items.invoice','contact','account']);

            // Create Journal
            $journal = Journal::create([
                'company_id'    => $salesReceipt->company_id,
                'created_by'    => auth()->id(),
                'number'        => 'JRN-RCV-'.date('YmdHis'),
                'date'          => $salesReceipt->date,
                'type'          => 'receipt',
                'description'   => "Penerimaan Kas #{$salesReceipt->number} - {$salesReceipt->contact->name}",
                'reference_type'=> SalesReceipt::class,
                'reference_id'  => $salesReceipt->id,
                'status'        => 'posted',
                'total_debit'   => $salesReceipt->amount,
                'total_credit'  => $salesReceipt->amount,
            ]);

            // Debit Cash/Bank
            JournalEntry::create([
                'journal_id'  => $journal->id,
                'account_id'  => $salesReceipt->account_id,
                'contact_id'  => $salesReceipt->contact_id,
                'description' => "Penerimaan - {$salesReceipt->number}",
                'debit'       => $salesReceipt->amount,
                'credit'      => 0,
            ]);

            foreach($salesReceipt->items as $item){
                // Update invoice paid amount
                $invoice = $item->invoice;
                $paid    = $invoice->paid_amount + $item->amount;
                $remaining = $invoice->total - $paid;
                $status  = $remaining <= 0 ? 'paid' : 'partial';

                $invoice->update([
                    'paid_amount'      => $paid,
                    'remaining_amount' => max(0,$remaining),
                    'status'           => $status,
                ]);

                // Credit AR
                if($invoice->ar_account_id){
                    JournalEntry::create([
                        'journal_id'  => $journal->id,
                        'account_id'  => $invoice->ar_account_id,
                        'contact_id'  => $salesReceipt->contact_id,
                        'description' => "Pelunasan Faktur {$invoice->number}",
                        'debit'       => 0,
                        'credit'      => $item->amount,
                    ]);
                }

                // Discount if any
                if($item->discount > 0){
                    $discAccount = Account::where('company_id',$salesReceipt->company_id)->where('name','like','%Diskon%')->first();
                    if($discAccount){
                        JournalEntry::create([
                            'journal_id'=>$journal->id,'account_id'=>$discAccount->id,
                            'description'=>"Diskon pelunasan {$invoice->number}",'debit'=>$item->discount,'credit'=>0,
                        ]);
                    }
                }
            }

            $salesReceipt->update(['status'=>'posted']);
        });

        return back()->with('success','Penerimaan kas berhasil diposting!');
    }

    public function cancel(SalesReceipt $salesReceipt)
    {
        if($salesReceipt->status==='cancelled') return back()->with('error','Sudah dibatalkan!');
        $salesReceipt->update(['status'=>'cancelled']);
        return back()->with('success','Penerimaan kas dibatalkan!');
    }

    public function print(SalesReceipt $salesReceipt)
    {
        $salesReceipt->load(['contact','account','items.invoice','createdBy','company']);
        return view('sales.receipts.print', compact('salesReceipt'));
    }

    public function getInvoicesByContact(Request $request)
    {
        $invoices = SalesInvoice::where('company_id',auth()->user()->company_id)
            ->where('contact_id',$request->contact_id)
            ->whereIn('status',['sent','partial','overdue'])
            ->with('contact')
            ->get()
            ->map(fn($i)=>[
                'id'=>$i->id,'number'=>$i->number,
                'date'=>$i->date->format('d/m/Y'),
                'due_date'=>$i->due_date->format('d/m/Y'),
                'total'=>$i->total,'remaining'=>$i->remaining_amount,
            ]);
        return response()->json($invoices);
    }

    private function generateNumber(int $cid): string
    {
        $last = SalesReceipt::where('company_id',$cid)->whereYear('created_at',date('Y'))->whereMonth('created_at',date('m'))->orderBy('id','desc')->first();
        $seq  = $last ? (intval(substr($last->number,-4))+1) : 1;
        return sprintf('RCV/%s/%s/%04d',date('Y'),date('m'),$seq);
    }
}