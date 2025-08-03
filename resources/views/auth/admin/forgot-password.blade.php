@extends('layouts.auth_admin')

@section('title', 'Quên mật khẩu Admin')

@section('body-class', 'hold-transition login-page')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="{{ route('admin.login') }}" class="h1"><b>Admin</b>Panel</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Nhập email để lấy lại mật khẩu</p>

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

            <form action="{{ route('admin.password.email') }}" method="post">
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
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Gửi liên kết đặt lại</button>
                    </div>
                </div>
            </form>

            <p class="mt-3 mb-1">
                <a href="{{ route('admin.login') }}">Quay lại đăng nhập</a>
            </p>
        </div>
    </div>
</div>

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
</style>
@endpush
@endsection