@extends('layouts.app')

@section('title', 'Admin Login - QuizMaster')

@section('content')
    <div style="max-width: 440px; margin: 2rem auto 0;">
        <div class="card">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="width: 56px; height: 56px; border-radius: 18px; margin: 0 auto .85rem; display: grid; place-items: center; background: linear-gradient(135deg, var(--brand), var(--brand-2)); color: #fff; font-size: 1.3rem; box-shadow: var(--shadow-sm);">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 style="font-size: 1.55rem; margin: 0 0 .35rem; letter-spacing: -0.03em;">Admin Sign In</h1>
                <p style="color: var(--muted); margin: 0;">Access quizzes, questions, and results.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@quizmaster.test">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required placeholder="Your password">
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom: 1rem; color: var(--muted); font-size: .92rem;">
                    <label style="display:flex; align-items:center; gap:.5rem; margin:0; font-weight:600;">
                        <input type="checkbox" name="remember" value="1"> Remember me
                    </label>
                    <span>Admin only</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="width:100%;">Sign In</button>
            </form>
        </div>
    </div>
@endsection
