@extends('layouts.app')
@section('title', 'Login — Product Inventory System')

@section('content')
<div class="login-wrapper">
    <div class="login-card glass-card animate-fade-up">
        {{-- Logo --}}
        <div class="login-header">
            <span class="login-icon"></span>
            <h1 class="login-title">Product Inventory System</h1>
            <p class="login-subtitle">Sign in to manage your inventory</p>
        </div>

        {{-- Form --}}
        <form id="loginForm" class="login-form" autocomplete="on">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    placeholder="admin@inventory.com"
                    required
                    autofocus
                >
                <span class="form-error" id="emailError"></span>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    required
                    minlength="6"
                >
                <span class="form-error" id="passwordError"></span>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                <span class="btn-text">Sign In</span>
                <span class="btn-loader" style="display:none;">
                    <span class="spinner"></span>
                </span>
            </button>

            <div class="form-error-global" id="globalError" style="display:none;"></div>
        </form>

        {{-- Demo Credentials --}}
        <div class="login-footer">
            <p class="demo-hint">Demo Credentials</p>
            <div class="demo-creds">
                <button class="demo-cred-btn" data-email="admin@inventory.com" data-password="password">
                    <span class="cred-role admin">Admin</span>
                    admin@inventory.com
                </button>
                <button class="demo-cred-btn" data-email="user@inventory.com" data-password="password">
                    <span class="cred-role user">User</span>
                    user@inventory.com
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const { login } = window.InventoryAPI;

    const form     = document.getElementById('loginForm');
    const emailIn  = document.getElementById('email');
    const passIn   = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');
    const globalErr= document.getElementById('globalError');

    // ── Demo credential buttons ──────────────────────
    document.querySelectorAll('.demo-cred-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            emailIn.value = btn.dataset.email;
            passIn.value  = btn.dataset.password;
        });
    });

    // ── Form submit ──────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        setLoading(true);

        try {
            const res = await login(emailIn.value, passIn.value);

            if (res.success) {
                localStorage.setItem('jwt_token', res.data.token);
                localStorage.setItem('user', JSON.stringify(res.data.user));
                window.location.href = '/dashboard';
            } else {
                showGlobalError(res.message || 'Login failed.');
            }
        } catch (err) {
            if (err.response?.status === 422) {
                const errors = err.response.data.errors;
                if (errors.email)    showFieldError('emailError', errors.email[0]);
                if (errors.password) showFieldError('passwordError', errors.password[0]);
            } else if (err.response?.status === 401) {
                showGlobalError('Invalid email or password.');
            } else {
                showGlobalError('Something went wrong. Please try again.');
            }
        } finally {
            setLoading(false);
        }
    });

    function setLoading(loading) {
        loginBtn.querySelector('.btn-text').style.display   = loading ? 'none' : '';
        loginBtn.querySelector('.btn-loader').style.display  = loading ? 'flex' : 'none';
        loginBtn.disabled = loading;
    }

    function showFieldError(id, msg) {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = 'block'; }
    }

    function showGlobalError(msg) {
        globalErr.textContent = msg;
        globalErr.style.display = 'block';
    }

    function clearErrors() {
        globalErr.style.display = 'none';
        document.querySelectorAll('.form-error').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    // If already logged in, redirect
    if (localStorage.getItem('jwt_token')) {
        window.location.href = '/dashboard';
    }
    });
</script>
@endsection
