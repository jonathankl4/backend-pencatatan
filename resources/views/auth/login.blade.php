@extends('layouts.app')

@section('title', 'Login - Pencatatan App')

@section('content')
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="w-100" style="max-width: 400px; padding: 24px;">
        
        <div class="text-center mb-4">
            <i class="bi bi-wallet2 text-primary-custom" style="font-size: 80px;"></i>
        </div>
        
        <h2 class="text-center fw-bold mb-2">Selamat Datang</h2>
        <p class="text-center text-secondary mb-5">Masuk untuk mengelola keuangan Anda</p>
        
        <form id="loginForm">
            <div class="mb-3">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email">Email</label>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // If already logged in, redirect to dashboard
        if(localStorage.getItem('token')) {
            window.location.href = '/dashboard';
        }

        $('#loginForm').submit(function(e) {
            e.preventDefault();
            
            const email = $('#email').val();
            const password = $('#password').val();
            
            showLoading();
            
            $.ajax({
                url: '/api/login',
                type: 'POST',
                data: JSON.stringify({ email: email, password: password }),
                contentType: 'application/json',
                success: function(response) {
                    hideLoading();
                    if(response.token) {
                        localStorage.setItem('token', response.token);
                        if (response.user && response.user.email) {
                            localStorage.setItem('user_email', response.user.email);
                        } else {
                            localStorage.setItem('user_email', email);
                        }
                        window.location.href = '/dashboard';
                    } else {
                        showError('Token not received.');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    let msg = 'Login failed';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showError(msg);
                }
            });
        });
    });
</script>
@endpush
