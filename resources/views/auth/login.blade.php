@extends('layouts.app')

@section('title', 'Admin Login - QuizMaster')

@section('content')
    <div style="max-width: 520px; margin: 0 auto;">
        <div class="card" style="padding: 2rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 72px; height: 72px; border-radius: 20px; margin: 0 auto 1rem; display: grid; place-items: center; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); color: white; font-size: 1.75rem; box-shadow: var(--shadow-lg);">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 style="font-size: 1.9rem; margin-bottom: 0.5rem;">Admin Sign In</h1>
                <p style="color: var(--text-secondary);">Log in to manage quizzes, questions, and results.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus placeholder="admin@quizmaster.test">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="form-control" required placeholder="Your password">
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-size: 0.95rem;">
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <span style="color: var(--text-light); font-size: 0.9rem;">Admin access only</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="width: 100%; justify-content: center;">
                    <i class="fas fa-right-to-bracket"></i>
                    Sign In
                </button>
            </form>
        </div>
    </div>
@endsection
