@extends('layouts.auth-admin')

@section('title', 'Đăng nhập Admin')

@section('body-class', 'hold-transition login-page')

@section('content')
<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="{{ route('admin.login') }}" class="h1"><b>Admin</b>Panel</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Đăng nhập để bắt đầu phiên làm việc</p>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email"
                           name="email" 
                           value="{{ old('email') }}" 
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="input-group mb-3">
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Mật khẩu"
                           name="password"
                           id="password"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                        <div class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                            <span class="fas fa-eye" id="togglePasswordIcon"></span>
                        </div>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <p class="mb-0 mt-3 text-center">
                <a href="{{ route('admin.password.request') }}">Quên mật khẩu?</a>
            </p>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!-- /.login-box -->

@push('styles')
<style>
    .login-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-box {
        width: 400px;
    }
    .card {
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        border: none;
        border-radius: 15px;
    }
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        border-bottom: none;
    }
    .card-header a {
        color: white !important;
        text-decoration: none;
    }
    .input-group-text:hover {
        background-color: #e9ecef;
    }
</style>
@endpush

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endpush
@endsection