{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ERP Accurate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}

        /* Animated background */
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(79,70,229,0.15),transparent 70%);border-radius:50%;top:-100px;left:-100px;animation:float 8s ease-in-out infinite;}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(124,58,237,0.1),transparent 70%);border-radius:50%;bottom:-100px;right:-100px;animation:float 10s ease-in-out infinite reverse;}
        @keyframes float{0%,100%{transform:translateY(0);}50%{transform:translateY(-30px);}}

        .login-wrapper{position:relative;z-index:10;width:100%;max-width:440px;padding:20px;}

        .login-card{background:rgba(255,255,255,0.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.1);border-radius:24px;padding:40px;box-shadow:0 25px 60px rgba(0,0,0,0.4);}

        .login-logo{text-align:center;margin-bottom:32px;}
        .logo-icon{width:64px;height:64px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;font-weight:800;color:white;box-shadow:0 10px 30px rgba(79,70,229,0.4);}
        .logo-title{font-size:24px;font-weight:800;color:white;letter-spacing:-0.5px;}
        .logo-subtitle{font-size:13px;color:rgba(255,255,255,0.5);margin-top:4px;}

        .form-group{margin-bottom:18px;}
        .form-label{display:block;font-size:13px;font-weight:500;color:rgba(255,255,255,0.7);margin-bottom:8px;}
        .input-wrapper{position:relative;}
        .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.3);font-size:14px;}
        .form-control{width:100%;padding:12px 14px 12px 40px;background:rgba(255,255,255,0.07);border:1.5px solid rgba(255,255,255,0.1);border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;color:white;outline:none;transition:all 0.2s;}
        .form-control::placeholder{color:rgba(255,255,255,0.25);}
        .form-control:focus{border-color:#4f46e5;background:rgba(255,255,255,0.1);box-shadow:0 0 0 3px rgba(79,70,229,0.2);}
        .form-control.no-icon{padding-left:14px;}

        .toggle-pw{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.3);cursor:pointer;background:none;border:none;font-size:14px;padding:0;}
        .toggle-pw:hover{color:rgba(255,255,255,0.6);}

        .checkbox-group{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;}
        .checkbox-label{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,0.6);cursor:pointer;}
        .checkbox-label input[type=checkbox]{width:16px;height:16px;border-radius:4px;accent-color:#4f46e5;}
        .forgot-link{font-size:13px;color:#818cf8;text-decoration:none;}
        .forgot-link:hover{color:#a5b4fc;}

        .btn-login{width:100%;padding:14px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border:none;border-radius:12px;font-size:15px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all 0.2s;letter-spacing:0.3px;}
        .btn-login:hover{transform:translateY(-1px);box-shadow:0 10px 30px rgba(79,70,229,0.5);}
        .btn-login:active{transform:translateY(0);}

        .divider{text-align:center;margin:20px 0;color:rgba(255,255,255,0.2);font-size:12px;position:relative;}
        .divider::before,.divider::after{content:'';position:absolute;top:50%;width:43%;height:1px;background:rgba(255,255,255,0.1);}
        .divider::before{left:0;} .divider::after{right:0;}

        .demo-info{background:rgba(79,70,229,0.15);border:1px solid rgba(79,70,229,0.3);border-radius:12px;padding:14px;margin-top:20px;}
        .demo-info h4{font-size:12px;font-weight:600;color:#818cf8;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px;}
        .demo-item{display:flex;justify-content:space-between;font-size:12.5px;color:rgba(255,255,255,0.6);margin-bottom:4px;}
        .demo-item span:last-child{font-family:monospace;color:#a5b4fc;}

        .alert-error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:12px;margin-bottom:18px;color:#fca5a5;font-size:13px;display:flex;align-items:center;gap:8px;}

        .copyright{text-align:center;margin-top:24px;font-size:12px;color:rgba(255,255,255,0.2);}
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon">E</div>
                <div class="logo-title">ERP Accurate</div>
                <div class="logo-subtitle">Enterprise Resource Planning System</div>
            </div>

            @if($errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('status'))
            <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:10px;padding:12px;margin-bottom:18px;color:#6ee7b7;font-size:13px;">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@perusahaan.com" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword()">
                            <i class="fas fa-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="divider">Demo Account</div>

            <div class="demo-info">
                <h4><i class="fas fa-key" style="margin-right:4px;"></i> Akun Demo</h4>
                <div class="demo-item"><span>Admin</span><span>admin@erp.com / password</span></div>
                <div class="demo-item"><span>Akuntan</span><span>budi@erp.com / password</span></div>
                <div class="demo-item"><span>Sales</span><span>dewi@erp.com / password</span></div>
            </div>
        </div>

        <div class="copyright">
            © {{ date('Y') }} ERP Accurate System. All rights reserved.
        </div>
    </div>

    <script>
    function togglePassword(){
        const input=document.getElementById('passwordInput');
        const icon=document.getElementById('pwIcon');
        if(input.type==='password'){input.type='text';icon.className='fas fa-eye-slash';}
        else{input.type='password';icon.className='fas fa-eye';}
    }
    </script>
</body>
</html>