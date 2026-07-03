<?php
// database/seeders/DatabaseSeeder.php (LENGKAP)

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===================== COMPANY =====================
        $company = Company::create([
            'name'           => 'PT Maju Bersama Indonesia',
            'legal_name'     => 'PT Maju Bersama Indonesia',
            'npwp'           => '01.234.567.8-900.000',
            'address'        => 'Jl. Sudirman No. 123, Jakarta Selatan',
            'city'           => 'Jakarta',
            'province'       => 'DKI Jakarta',
            'postal_code'    => '12190',
            'phone'          => '021-12345678',
            'fax'            => '021-12345679',
            'email'          => 'info@majubersama.co.id',
            'website'        => 'www.majubersama.co.id',
            'currency_code'  => 'IDR',
            'fiscal_year_start' => '01-01',
            'is_active'      => true,
        ]);

        // ===================== ROLES =====================
        $adminRole      = Role::create(['name' => 'admin',      'display_name' => 'Administrator']);
        $managerRole    = Role::create(['name' => 'manager',    'display_name' => 'Manager']);
        $accountantRole = Role::create(['name' => 'accountant', 'display_name' => 'Akuntan']);
        $salesRole      = Role::create(['name' => 'sales',      'display_name' => 'Sales']);
        $purchaseRole   = Role::create(['name' => 'purchase',   'display_name' => 'Pembelian']);

        // ===================== USERS =====================
        User::create([
            'company_id' => $company->id,
            'role_id'    => $adminRole->id,
            'name'       => 'Administrator',
            'email'      => 'admin@erp.com',
            'password'   => Hash::make('password'),
            'is_active'  => true,
        ]);
        User::create([
            'company_id' => $company->id,
            'role_id'    => $accountantRole->id,
            'name'       => 'Budi Santoso',
            'email'      => 'budi@erp.com',
            'password'   => Hash::make('password'),
            'is_active'  => true,
        ]);
        User::create([
            'company_id' => $company->id,
            'role_id'    => $salesRole->id,
            'name'       => 'Dewi Rahayu',
            'email'      => 'dewi@erp.com',
            'password'   => Hash::make('password'),
            'is_active'  => true,
        ]);

        // ===================== ACCOUNT CATEGORIES =====================
        $catAsset     = AccountCategory::create(['code'=>'1','name'=>'Aset',       'type'=>'asset',     'is_debit_normal'=>true]);
        $catLiab      = AccountCategory::create(['code'=>'2','name'=>'Kewajiban',  'type'=>'liability',  'is_debit_normal'=>false]);
        $catEquity    = AccountCategory::create(['code'=>'3','name'=>'Ekuitas',    'type'=>'equity',     'is_debit_normal'=>false]);
        $catRevenue   = AccountCategory::create(['code'=>'4','name'=>'Pendapatan', 'type'=>'revenue',    'is_debit_normal'=>false]);
        $catExpense   = AccountCategory::create(['code'=>'5','name'=>'Beban',      'type'=>'expense',    'is_debit_normal'=>true]);

        // ===================== CHART OF ACCOUNTS =====================
        $coaData = [
            // ---- ASET ----
            ['code'=>'1-0000','name'=>'ASET',                              'type'=>'asset',    'sub_type'=>null,            'is_header'=>true,  'level'=>1, 'cat_id'=>$catAsset->id,   'opening_balance'=>0],
            ['code'=>'1-1000','name'=>'Aset Lancar',                       'type'=>'asset',    'sub_type'=>null,            'is_header'=>true,  'level'=>2, 'cat_id'=>$catAsset->id,   'opening_balance'=>0],
            ['code'=>'1-1100','name'=>'Kas',                               'type'=>'asset',    'sub_type'=>'cash',          'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>50000000],
            ['code'=>'1-1110','name'=>'Kas Kecil',                         'type'=>'asset',    'sub_type'=>'cash',          'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>5000000],
            ['code'=>'1-1200','name'=>'Bank BCA',                          'type'=>'asset',    'sub_type'=>'bank',          'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>200000000],
            ['code'=>'1-1210','name'=>'Bank Mandiri',                      'type'=>'asset',    'sub_type'=>'bank',          'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>150000000],
            ['code'=>'1-1220','name'=>'Bank BNI',                          'type'=>'asset',    'sub_type'=>'bank',          'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>100000000],
            ['code'=>'1-1300','name'=>'Piutang Dagang',                    'type'=>'asset',    'sub_type'=>'receivable',    'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>80000000],
            ['code'=>'1-1310','name'=>'Cadangan Kerugian Piutang',         'type'=>'asset',    'sub_type'=>'receivable',    'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>0],
            ['code'=>'1-1400','name'=>'Persediaan Barang Dagangan',        'type'=>'asset',    'sub_type'=>'inventory',     'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>120000000],
            ['code'=>'1-1410','name'=>'Persediaan Bahan Baku',             'type'=>'asset',    'sub_type'=>'inventory',     'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>30000000],
            ['code'=>'1-1500','name'=>'Uang Muka Pembelian',               'type'=>'asset',    'sub_type'=>'other_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>0],
            ['code'=>'1-1600','name'=>'PPN Masukan',                       'type'=>'asset',    'sub_type'=>'other_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>0],
            ['code'=>'1-1700','name'=>'Biaya Dibayar Dimuka',              'type'=>'asset',    'sub_type'=>'other_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>12000000],
            ['code'=>'1-2000','name'=>'Aset Tidak Lancar',                 'type'=>'asset',    'sub_type'=>null,            'is_header'=>true,  'level'=>2, 'cat_id'=>$catAsset->id,   'opening_balance'=>0],
            ['code'=>'1-2100','name'=>'Tanah',                             'type'=>'asset',    'sub_type'=>'fixed_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>500000000],
            ['code'=>'1-2200','name'=>'Bangunan',                          'type'=>'asset',    'sub_type'=>'fixed_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>800000000],
            ['code'=>'1-2210','name'=>'Akumulasi Penyusutan Bangunan',     'type'=>'asset',    'sub_type'=>'fixed_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>-160000000],
            ['code'=>'1-2300','name'=>'Peralatan Kantor',                  'type'=>'asset',    'sub_type'=>'fixed_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>80000000],
            ['code'=>'1-2310','name'=>'Akumulasi Penyusutan Peralatan',    'type'=>'asset',    'sub_type'=>'fixed_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>-24000000],
            ['code'=>'1-2400','name'=>'Kendaraan',                         'type'=>'asset',    'sub_type'=>'fixed_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>300000000],
            ['code'=>'1-2410','name'=>'Akumulasi Penyusutan Kendaraan',    'type'=>'asset',    'sub_type'=>'fixed_asset',   'is_header'=>false, 'level'=>3, 'cat_id'=>$catAsset->id,   'opening_balance'=>-90000000],

            // ---- KEWAJIBAN ----
            ['code'=>'2-0000','name'=>'KEWAJIBAN',                         'type'=>'liability','sub_type'=>null,            'is_header'=>true,  'level'=>1, 'cat_id'=>$catLiab->id,    'opening_balance'=>0],
            ['code'=>'2-1000','name'=>'Kewajiban Lancar',                  'type'=>'liability','sub_type'=>null,            'is_header'=>true,  'level'=>2, 'cat_id'=>$catLiab->id,    'opening_balance'=>0],
            ['code'=>'2-1100','name'=>'Hutang Dagang',                     'type'=>'liability','sub_type'=>'payable',       'is_header'=>false, 'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>60000000],
            ['code'=>'2-1200','name'=>'PPN Keluaran',                      'type'=>'liability','sub_type'=>'other_liability','is_header'=>false,'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>0],
            ['code'=>'2-1300','name'=>'Hutang PPh 21',                     'type'=>'liability','sub_type'=>'other_liability','is_header'=>false,'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>0],
            ['code'=>'2-1400','name'=>'Hutang PPh 23',                     'type'=>'liability','sub_type'=>'other_liability','is_header'=>false,'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>0],
            ['code'=>'2-1500','name'=>'Uang Muka Penjualan',               'type'=>'liability','sub_type'=>'other_liability','is_header'=>false,'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>0],
            ['code'=>'2-1600','name'=>'Beban Akrual',                      'type'=>'liability','sub_type'=>'other_liability','is_header'=>false,'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>5000000],
            ['code'=>'2-1700','name'=>'Hutang Gaji',                       'type'=>'liability','sub_type'=>'other_liability','is_header'=>false,'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>15000000],
            ['code'=>'2-2000','name'=>'Kewajiban Tidak Lancar',            'type'=>'liability','sub_type'=>null,            'is_header'=>true,  'level'=>2, 'cat_id'=>$catLiab->id,    'opening_balance'=>0],
            ['code'=>'2-2100','name'=>'Hutang Bank Jangka Panjang',        'type'=>'liability','sub_type'=>'long_term_loan', 'is_header'=>false,'level'=>3, 'cat_id'=>$catLiab->id,    'opening_balance'=>400000000],

            // ---- EKUITAS ----
            ['code'=>'3-0000','name'=>'EKUITAS',                           'type'=>'equity',   'sub_type'=>null,            'is_header'=>true,  'level'=>1, 'cat_id'=>$catEquity->id,  'opening_balance'=>0],
            ['code'=>'3-1000','name'=>'Modal Disetor',                     'type'=>'equity',   'sub_type'=>'capital',       'is_header'=>false, 'level'=>2, 'cat_id'=>$catEquity->id,  'opening_balance'=>1000000000],
            ['code'=>'3-2000','name'=>'Laba Ditahan',                      'type'=>'equity',   'sub_type'=>'retained_earnings','is_header'=>false,'level'=>2,'cat_id'=>$catEquity->id, 'opening_balance'=>497000000],
            ['code'=>'3-3000','name'=>'Laba Tahun Berjalan',               'type'=>'equity',   'sub_type'=>'retained_earnings','is_header'=>false,'level'=>2,'cat_id'=>$catEquity->id, 'opening_balance'=>0],

            // ---- PENDAPATAN ----
            ['code'=>'4-0000','name'=>'PENDAPATAN',                        'type'=>'revenue',  'sub_type'=>null,            'is_header'=>true,  'level'=>1, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-1000','name'=>'Penjualan',                         'type'=>'revenue',  'sub_type'=>'sales',         'is_header'=>false, 'level'=>2, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-1100','name'=>'Penjualan Barang',                  'type'=>'revenue',  'sub_type'=>'sales',         'is_header'=>false, 'level'=>3, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-1200','name'=>'Penjualan Jasa',                    'type'=>'revenue',  'sub_type'=>'sales',         'is_header'=>false, 'level'=>3, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-1300','name'=>'Retur Penjualan',                   'type'=>'revenue',  'sub_type'=>'sales',         'is_header'=>false, 'level'=>3, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-1400','name'=>'Diskon Penjualan',                  'type'=>'revenue',  'sub_type'=>'sales',         'is_header'=>false, 'level'=>3, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-2000','name'=>'Pendapatan Lain-lain',              'type'=>'revenue',  'sub_type'=>'other_revenue', 'is_header'=>false, 'level'=>2, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-2100','name'=>'Pendapatan Bunga',                  'type'=>'revenue',  'sub_type'=>'other_revenue', 'is_header'=>false, 'level'=>3, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],
            ['code'=>'4-2200','name'=>'Laba Penjualan Aset',               'type'=>'revenue',  'sub_type'=>'other_revenue', 'is_header'=>false, 'level'=>3, 'cat_id'=>$catRevenue->id, 'opening_balance'=>0],

            // ---- BEBAN ----
            ['code'=>'5-0000','name'=>'BEBAN',                             'type'=>'expense',  'sub_type'=>null,            'is_header'=>true,  'level'=>1, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-1000','name'=>'Harga Pokok Penjualan',             'type'=>'expense',  'sub_type'=>'cogs',          'is_header'=>false, 'level'=>2, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-1100','name'=>'HPP Barang Dagangan',               'type'=>'expense',  'sub_type'=>'cogs',          'is_header'=>false, 'level'=>3, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-1200','name'=>'Biaya Angkut Pembelian',            'type'=>'expense',  'sub_type'=>'cogs',          'is_header'=>false, 'level'=>3, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-2000','name'=>'Beban Operasional',                 'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>true,'level'=>2,'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-2100','name'=>'Beban Gaji & Tunjangan',            'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-2200','name'=>'Beban Sewa',                        'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-2300','name'=>'Beban Listrik & Air',               'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-2400','name'=>'Beban Telepon & Internet',          'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-2500','name'=>'Beban Penyusutan',                  'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-2600','name'=>'Beban Iklan & Pemasaran',           'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-2700','name'=>'Beban Perjalanan Dinas',            'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-2800','name'=>'Beban Administrasi & Umum',         'type'=>'expense',  'sub_type'=>'operating_expense','is_header'=>false,'level'=>3,'cat_id'=>$catExpense->id,'opening_balance'=>0],
            ['code'=>'5-3000','name'=>'Beban Lain-lain',                   'type'=>'expense',  'sub_type'=>'other_expense', 'is_header'=>true,  'level'=>2, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-3100','name'=>'Beban Bunga Bank',                  'type'=>'expense',  'sub_type'=>'other_expense', 'is_header'=>false, 'level'=>3, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-3200','name'=>'Beban Pajak',                       'type'=>'expense',  'sub_type'=>'other_expense', 'is_header'=>false, 'level'=>3, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
            ['code'=>'5-3300','name'=>'Kerugian Piutang Tak Tertagih',     'type'=>'expense',  'sub_type'=>'other_expense', 'is_header'=>false, 'level'=>3, 'cat_id'=>$catExpense->id, 'opening_balance'=>0],
        ];

        foreach ($coaData as $item) {
            Account::create([
                'company_id'          => $company->id,
                'account_category_id' => $item['cat_id'],
                'code'                => $item['code'],
                'name'                => $item['name'],
                'type'                => $item['type'],
                'sub_type'            => $item['sub_type'],
                'is_header'           => $item['is_header'],
                'level'               => $item['level'],
                'opening_balance'     => $item['opening_balance'],
                'current_balance'     => $item['opening_balance'],
                'is_active'           => true,
                'is_system'           => in_array($item['code'], ['1-1300','1-1400','2-1100','2-1200','4-1100','5-1100']),
            ]);
        }

        // ===================== TAXES =====================
        $accPPNIn  = Account::where('company_id', $company->id)->where('code','1-1600')->first();
        $accPPNOut = Account::where('company_id', $company->id)->where('code','2-1200')->first();

        Tax::create([
            'company_id'         => $company->id,
            'code'               => 'PPN-11',
            'name'               => 'PPN 11%',
            'rate'               => 11,
            'type'               => 'ppn',
            'sales_account_id'   => $accPPNOut?->id,
            'purchase_account_id'=> $accPPNIn?->id,
            'is_active'          => true,
        ]);
        Tax::create([
            'company_id'  => $company->id,
            'code'        => 'PPH23-2',
            'name'        => 'PPh 23 (2%)',
            'rate'        => 2,
            'type'        => 'pph',
            'is_active'   => true,
        ]);
        Tax::create([
            'company_id'  => $company->id,
            'code'        => 'NO-TAX',
            'name'        => 'Tidak Kena Pajak',
            'rate'        => 0,
            'type'        => 'custom',
            'is_active'   => true,
        ]);

        // ===================== WAREHOUSES =====================
        $warehouseUtama = Warehouse::create([
            'company_id' => $company->id,
            'code'       => 'GDG-01',
            'name'       => 'Gudang Utama Jakarta',
            'address'    => 'Jl. Industri No. 45, Cakung, Jakarta Timur',
            'phone'      => '021-99887766',
            'is_active'  => true,
        ]);
        $warehouseCabang = Warehouse::create([
            'company_id' => $company->id,
            'code'       => 'GDG-02',
            'name'       => 'Gudang Cabang Surabaya',
            'address'    => 'Jl. Rungkut Industri No. 12, Surabaya',
            'phone'      => '031-55443322',
            'is_active'  => true,
        ]);

        // ===================== UNITS =====================
        $unitPcs   = Unit::create(['company_id'=>$company->id,'name'=>'Piece',    'symbol'=>'Pcs']);
        $unitBox   = Unit::create(['company_id'=>$company->id,'name'=>'Box',      'symbol'=>'Box']);
        $unitKg    = Unit::create(['company_id'=>$company->id,'name'=>'Kilogram', 'symbol'=>'Kg']);
        $unitLtr   = Unit::create(['company_id'=>$company->id,'name'=>'Liter',    'symbol'=>'Ltr']);
        $unitMeter = Unit::create(['company_id'=>$company->id,'name'=>'Meter',    'symbol'=>'Mtr']);
        $unitSet   = Unit::create(['company_id'=>$company->id,'name'=>'Set',      'symbol'=>'Set']);
        $unitUnit  = Unit::create(['company_id'=>$company->id,'name'=>'Unit',     'symbol'=>'Unit']);

        // ===================== PRODUCT CATEGORIES =====================
        $catElektronik = ProductCategory::create(['company_id'=>$company->id,'name'=>'Elektronik',       'code'=>'ELK']);
        $catFurnitur   = ProductCategory::create(['company_id'=>$company->id,'name'=>'Furnitur',         'code'=>'FRN']);
        $catAlat       = ProductCategory::create(['company_id'=>$company->id,'name'=>'Alat Tulis Kantor','code'=>'ATK']);
        $catJasa       = ProductCategory::create(['company_id'=>$company->id,'name'=>'Jasa',             'code'=>'JSA']);

        // ===================== PRODUCTS =====================
        $accSales     = Account::where('company_id',$company->id)->where('code','4-1100')->first();
        $accPurchase  = Account::where('company_id',$company->id)->where('code','5-1200')->first();
        $accInventory = Account::where('company_id',$company->id)->where('code','1-1400')->first();
        $accCOGS      = Account::where('company_id',$company->id)->where('code','5-1100')->first();
        $accSalesJasa = Account::where('company_id',$company->id)->where('code','4-1200')->first();

        $products = [
            ['code'=>'PRD-001','name'=>'Laptop Dell Inspiron 15',    'cat'=>$catElektronik,'unit'=>$unitUnit,'sell'=>12500000,'buy'=>10000000,'hpp'=>10000000,'min'=>2,'max'=>20,'stock'=>10,'type'=>'inventory'],
            ['code'=>'PRD-002','name'=>'Monitor LED 24 Inch',        'cat'=>$catElektronik,'unit'=>$unitUnit,'sell'=>2800000, 'buy'=>2200000, 'hpp'=>2200000, 'min'=>3,'max'=>30,'stock'=>15,'type'=>'inventory'],
            ['code'=>'PRD-003','name'=>'Keyboard & Mouse Wireless',  'cat'=>$catElektronik,'unit'=>$unitSet, 'sell'=>450000,  'buy'=>300000,  'hpp'=>300000,  'min'=>5,'max'=>50,'stock'=>25,'type'=>'inventory'],
            ['code'=>'PRD-004','name'=>'Printer HP LaserJet',        'cat'=>$catElektronik,'unit'=>$unitUnit,'sell'=>3200000, 'buy'=>2500000, 'hpp'=>2500000, 'min'=>2,'max'=>15,'stock'=>8, 'type'=>'inventory'],
            ['code'=>'PRD-005','name'=>'Kursi Kantor Ergonomis',     'cat'=>$catFurnitur,  'unit'=>$unitUnit,'sell'=>1800000, 'buy'=>1200000, 'hpp'=>1200000, 'min'=>3,'max'=>20,'stock'=>12,'type'=>'inventory'],
            ['code'=>'PRD-006','name'=>'Meja Kerja Minimalis',       'cat'=>$catFurnitur,  'unit'=>$unitUnit,'sell'=>2500000, 'buy'=>1800000, 'hpp'=>1800000, 'min'=>2,'max'=>15,'stock'=>7, 'type'=>'inventory'],
            ['code'=>'PRD-007','name'=>'Kertas A4 80gr',             'cat'=>$catAlat,      'unit'=>$unitBox, 'sell'=>65000,   'buy'=>50000,   'hpp'=>50000,   'min'=>10,'max'=>100,'stock'=>50,'type'=>'inventory'],
            ['code'=>'PRD-008','name'=>'Ballpoint Pilot',            'cat'=>$catAlat,      'unit'=>$unitBox, 'sell'=>35000,   'buy'=>25000,   'hpp'=>25000,   'min'=>5,'max'=>50,'stock'=>30,'type'=>'inventory'],
            ['code'=>'PRD-009','name'=>'Tinta Printer (set 4 warna)','cat'=>$catElektronik,'unit'=>$unitSet, 'sell'=>120000,  'buy'=>85000,   'hpp'=>85000,   'min'=>5,'max'=>40,'stock'=>20,'type'=>'inventory'],
            ['code'=>'JSA-001','name'=>'Jasa Instalasi & Setup',     'cat'=>$catJasa,      'unit'=>$unitUnit,'sell'=>500000,  'buy'=>0,       'hpp'=>0,       'min'=>0,'max'=>0,'stock'=>0,'type'=>'service'],
            ['code'=>'JSA-002','name'=>'Jasa Maintenance Tahunan',   'cat'=>$catJasa,      'unit'=>$unitUnit,'sell'=>1200000, 'buy'=>0,       'hpp'=>0,       'min'=>0,'max'=>0,'stock'=>0,'type'=>'service'],
        ];

        foreach ($products as $p) {
            $isInventory = $p['type'] === 'inventory';
            $product = Product::create([
                'company_id'          => $company->id,
                'product_category_id' => $p['cat']->id,
                'unit_id'             => $p['unit']->id,
                'code'                => $p['code'],
                'name'                => $p['name'],
                'type'                => $p['type'],
                'selling_price'       => $p['sell'],
                'purchase_price'      => $p['buy'],
                'hpp'                 => $p['hpp'],
                'min_stock'           => $p['min'],
                'max_stock'           => $p['max'],
                'opening_stock'       => $p['stock'],
                'is_sold'             => true,
                'is_purchased'        => $isInventory,
                'track_inventory'     => $isInventory,
                'sales_account_id'    => $p['type']==='service' ? $accSalesJasa?->id : $accSales?->id,
                'purchase_account_id' => $accPurchase?->id,
                'inventory_account_id'=> $isInventory ? $accInventory?->id : null,
                'cogs_account_id'     => $isInventory ? $accCOGS?->id : null,
                'costing_method'      => 'average',
                'is_active'           => true,
            ]);

            if ($isInventory && $p['stock'] > 0) {
                ProductStock::create([
                    'product_id'   => $product->id,
                    'warehouse_id' => $warehouseUtama->id,
                    'quantity'     => $p['stock'],
                    'avg_cost'     => $p['hpp'],
                ]);
            }
        }

        // ===================== CONTACT GROUPS =====================
        $grpRetail    = ContactGroup::create(['company_id'=>$company->id,'name'=>'Retail',         'type'=>'customer']);
        $grpWholesale = ContactGroup::create(['company_id'=>$company->id,'name'=>'Grosir',         'type'=>'customer']);
        $grpSupplier  = ContactGroup::create(['company_id'=>$company->id,'name'=>'Supplier Lokal', 'type'=>'vendor']);
        $grpImportir  = ContactGroup::create(['company_id'=>$company->id,'name'=>'Importir',       'type'=>'vendor']);

        $accAR = Account::where('company_id',$company->id)->where('code','1-1300')->first();
        $accAP = Account::where('company_id',$company->id)->where('code','2-1100')->first();

        // ===================== CONTACTS =====================
        $contactsData = [
            // Customers
            ['code'=>'CUS-001','name'=>'PT Teknologi Nusantara',    'type'=>'customer','group'=>$grpWholesale,'phone'=>'021-55667788','email'=>'purchasing@teknologi.co.id','payment_term'=>30,'city'=>'Jakarta'],
            ['code'=>'CUS-002','name'=>'CV Mandiri Sejahtera',      'type'=>'customer','group'=>$grpRetail,   'phone'=>'022-44556677','email'=>'order@mandiri.co.id',       'payment_term'=>14,'city'=>'Bandung'],
            ['code'=>'CUS-003','name'=>'PT Global Solusi Indonesia', 'type'=>'customer','group'=>$grpWholesale,'phone'=>'031-33445566','email'=>'buy@globalsolusi.co.id',    'payment_term'=>45,'city'=>'Surabaya'],
            ['code'=>'CUS-004','name'=>'Toko Elektronik Jaya',      'type'=>'customer','group'=>$grpRetail,   'phone'=>'0274-112233', 'email'=>'toko@jayaelektronik.com',   'payment_term'=>7, 'city'=>'Yogyakarta'],
            ['code'=>'CUS-005','name'=>'UD Berkah Makmur',          'type'=>'customer','group'=>$grpRetail,   'phone'=>'024-667788',  'email'=>'ud.berkah@gmail.com',       'payment_term'=>21,'city'=>'Semarang'],
            // Vendors
            ['code'=>'VND-001','name'=>'PT Distributor Prima',      'type'=>'vendor',  'group'=>$grpSupplier, 'phone'=>'021-88997766','email'=>'sales@distributoprima.co.id','payment_term'=>30,'city'=>'Jakarta'],
            ['code'=>'VND-002','name'=>'CV Sumber Rejeki',          'type'=>'vendor',  'group'=>$grpSupplier, 'phone'=>'031-77665544','email'=>'info@sumberrejeki.co.id',   'payment_term'=>14,'city'=>'Surabaya'],
            ['code'=>'VND-003','name'=>'PT Import Jaya Abadi',      'type'=>'vendor',  'group'=>$grpImportir, 'phone'=>'021-44332211','email'=>'procurement@importjaya.com','payment_term'=>60,'city'=>'Jakarta'],
        ];

        foreach ($contactsData as $c) {
            Contact::create([
                'company_id'           => $company->id,
                'contact_group_id'     => $c['group']->id,
                'code'                 => $c['code'],
                'name'                 => $c['name'],
                'type'                 => $c['type'],
                'phone'                => $c['phone'],
                'email'                => $c['email'],
                'billing_city'         => $c['city'],
                'billing_country'      => 'Indonesia',
                'payment_term'         => $c['payment_term'],
                'currency_code'        => 'IDR',
                'receivable_account_id'=> $c['type']==='customer' ? $accAR?->id : null,
                'payable_account_id'   => $c['type']==='vendor'   ? $accAP?->id : null,
                'credit_limit'         => $c['type']==='customer' ? 50000000 : 0,
                'opening_balance'      => 0,
                'is_active'            => true,
            ]);
        }
    }
}