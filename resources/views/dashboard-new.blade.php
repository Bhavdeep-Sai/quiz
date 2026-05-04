@extends('layouts.professional')

@section('title', 'Dashboard - QuizMaster')

@section('content')
    <div style="margin-bottom: 3rem;">
        <h1 style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--text-primary);">
            <i class="fas fa-chart-line" style="color: var(--accent-primary); margin-right: 0.75rem;"></i>
            Dashboard
        </h1>
        <p style="color: var(--text-secondary);">Overview of your quiz system</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-4" style="margin-bottom: 3rem;">
        <div class="stat-box">
            <div class="stat-label">
                <i class="fas fa-book stat-icon"></i>
                Total Quizzes
            </div>
            <div class="stat-value">{{ $stats['total_quizzes'] ?? 0 }}</div>
        </div>

        <div class="stat-box">
            <div class="stat-label">
                <i class="fas fa-question stat-icon"></i>
                Total Questions
            </div>
            <div class="stat-value">{{ $stats['total_questions'] ?? 0 }}</div>
        </div>

        <div class="stat-box">
            <div class="stat-label">
                <i class="fas fa-users stat-icon"></i>
                Total Attempts
            </div>
            <div class="stat-value">{{ $stats['total_attempts'] ?? 0 }}</div>
        </div>

        <div class="stat-box">
            <div class="stat-label">
                <i class="fas fa-chart-pie stat-icon"></i>
                Avg Score
            </div>
            <div class="stat-value">{{ $stats['avg_score'] ?? '0' }}%</div>
        </div>
    </div>

    <!-- Recent Quizzes -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-star"></i>
                Featured Quizzes
            </h2>
            <a href="/quizzes" class="btn btn-primary btn-small">
                <i class="fas fa-arrow-right"></i>
                View All
            </a>
        </div>

        @if(count($quizzes) > 0)
            <div class="grid grid-3">
                @foreach($quizzes as $quiz)
                    <a href="/quizzes/{{ $quiz->id }}/start" class="quiz-item" style="text-decoration: none; color: inherit;">
                        <div class="quiz-item-title">
                            <i class="fas fa-lightbulb" style="color: var(--accent-primary);"></i>
                            {{ $quiz->title }}
                        </div>
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                            {{ Str::limit($quiz->description, 80) }}
                        </p>
                        <div class="quiz-item-meta">
                            <span>
                                <i class="fas fa-question-circle"></i>
                                {{ $quiz->questions_count }} Questions
                            </span>
                            <span>
                                <i class="fas fa-stopwatch"></i>
                                {{ $quiz->time_limit ?? 'Unlimited' }}
                            </span>
                            <span>
                                <i class="fas fa-percentage"></i>
                                {{ $quiz->pass_percentage }}% Pass
                            </span>
                        </div>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                            <span class="badge badge-primary">
                                <i class="fas fa-play"></i>
                                Take Quiz
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; display: block;"></i>
                <p style="margin-bottom: 1.5rem;">No quizzes available yet.</p>
                <a href="/admin/quizzes" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create First Quiz
                </a>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h2>
        </div>

        <div class="grid grid-3">
            <a href="/quizzes" class="quiz-item" style="text-decoration: none; color: inherit; text-align: center;">
                <i class="fas fa-book" style="font-size: 2rem; color: var(--accent-primary); margin-bottom: 1rem; display: block;"></i>
                <div class="quiz-item-title" style="justify-content: center;">Browse Quizzes</div>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Explore and take available quizzes</p>
            </a>

            <a href="/admin/quizzes" class="quiz-item" style="text-decoration: none; color: inherit; text-align: center;">
                <i class="fas fa-plus-circle" style="font-size: 2rem; color: var(--success); margin-bottom: 1rem; display: block;"></i>
                <div class="quiz-item-title" style="justify-content: center;">Create Quiz</div>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Design new quiz and add questions</p>
            </a>

            <a href="/admin/quizzes" class="quiz-item" style="text-decoration: none; color: inherit; text-align: center;">
                <i class="fas fa-chart-bar" style="font-size: 2rem; color: var(--info); margin-bottom: 1rem; display: block;"></i>
                <div class="quiz-item-title" style="justify-content: center;">Analytics</div>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">View quiz performance and statistics</p>
            </a>
        </div>
    </div>
@endsection
