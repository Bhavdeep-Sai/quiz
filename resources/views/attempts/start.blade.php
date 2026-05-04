@extends('layouts.app')

@section('title', 'Start Quiz - ' . $quiz->title)

@section('content')
    <div style="margin-bottom: 2rem;">
        <a href="/quizzes" style="color: var(--accent-primary); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i>
            Back to Quizzes
        </a>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h1 class="card-title" style="font-size: 2rem;">
                <i class="fas fa-scroll"></i>
                {{ $quiz->title }}
            </h1>
        </div>

        <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 1.1rem;">
            {{ $quiz->description }}
        </p>

        <!-- Quiz Details Grid -->
        <div class="grid grid-4" style="margin-bottom: 2rem;">
            <div class="stat-box">
                <div class="stat-label">
                    <i class="fas fa-question stat-icon"></i>
                    Questions
                </div>
                <div class="stat-value">{{ count($quiz->questions) }}</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">
                    <i class="fas fa-star stat-icon"></i>
                    Total Marks
                </div>
                <div class="stat-value">{{ $quiz->questions->sum('marks') }}</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">
                    <i class="fas fa-percentage stat-icon"></i>
                    Pass %
                </div>
                <div class="stat-value">{{ $quiz->pass_percentage }}%</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">
                    <i class="fas fa-hourglass stat-icon"></i>
                    Time Limit
                </div>
                <div class="stat-value">{{ $quiz->time_limit ?? '∞' }}</div>
            </div>
        </div>
    </div>

    <!-- User Information Form -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-user-circle"></i>
                Your Information
            </h2>
        </div>

        <form method="POST" action="/attempts/{{ $quiz->id }}" style="max-width: 500px;">
            @csrf

            <div class="form-group">
                <label for="user_name">
                    <i class="fas fa-user" style="margin-right: 0.5rem; color: var(--accent-primary);"></i>
                    Full Name
                </label>
                <input type="text" id="user_name" name="user_name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="user_email">
                    <i class="fas fa-envelope" style="margin-right: 0.5rem; color: var(--accent-primary);"></i>
                    Email Address
                </label>
                <input type="email" id="user_email" name="user_email" placeholder="Enter your email (optional)">
            </div>

            <div class="form-group">
                <label for="user_identifier">
                    <i class="fas fa-id-card" style="margin-right: 0.5rem; color: var(--accent-primary);"></i>
                    Student ID
                </label>
                <input type="text" id="user_identifier" name="user_identifier" placeholder="Enter your student ID (optional)">
            </div>

            <div style="background: var(--bg-tertiary); padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem;">
                <p style="color: var(--text-secondary); font-size: 0.95rem; margin: 0;">
                    <i class="fas fa-info-circle" style="color: var(--info); margin-right: 0.5rem;"></i>
                    <strong>Quiz Information:</strong> This quiz contains {{ count($quiz->questions) }} questions with a total of {{ $quiz->questions->sum('marks') }} marks. You must score at least {{ $quiz->pass_percentage }}% to pass.
                </p>
            </div>

            <div style="display: flex; gap: 1rem;">
                <a href="/quizzes" class="btn btn-secondary btn-block">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-play"></i>
                    Start Quiz
                </button>
            </div>
        </form>
    </div>
@endsection
