@extends('layouts.professional')

@section('title', 'Available Quizzes - QuizMaster')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">
                <i class="fas fa-book-open" style="color: var(--accent-primary); margin-right: 0.75rem;"></i>
                Available Quizzes
            </h1>
            <p style="color: var(--text-secondary);">Choose a quiz to begin your learning journey</p>
        </div>
        <a href="/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    @if(count($quizzes) > 0)
        <div class="grid grid-3">
            @foreach($quizzes as $quiz)
                <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <!-- Card Header with Color -->
                    <div style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); padding: 2rem; color: white;">
                        <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: white;">
                            <i class="fas fa-scroll" style="margin-right: 0.5rem;"></i>
                            {{ $quiz->title }}
                        </h2>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                        <p style="color: var(--text-secondary); margin-bottom: 1rem; line-height: 1.6;">
                            {{ Str::limit($quiz->description, 100) }}
                        </p>

                        <!-- Stats -->
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--text-secondary); font-weight: 500;">
                                    <i class="fas fa-question" style="color: var(--accent-primary); margin-right: 0.5rem;"></i>
                                    Questions
                                </span>
                                <span style="font-weight: 600; color: var(--accent-primary);">{{ $quiz->questions_count ?? count($quiz->questions) }}</span>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--text-secondary); font-weight: 500;">
                                    <i class="fas fa-star" style="color: var(--warning); margin-right: 0.5rem;"></i>
                                    Total Marks
                                </span>
                                <span style="font-weight: 600; color: var(--warning);">{{ $quiz->questions->sum('marks') ?? 0 }}</span>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--text-secondary); font-weight: 500;">
                                    <i class="fas fa-percentage" style="color: var(--success); margin-right: 0.5rem;"></i>
                                    Pass %
                                </span>
                                <span style="font-weight: 600; color: var(--success);">{{ $quiz->pass_percentage }}%</span>
                            </div>

                            @if($quiz->time_limit)
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-secondary); font-weight: 500;">
                                        <i class="fas fa-hourglass" style="color: var(--danger); margin-right: 0.5rem;"></i>
                                        Time Limit
                                    </span>
                                    <span style="font-weight: 600; color: var(--danger);">{{ $quiz->time_limit }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Status Badge -->
                        <div style="margin-bottom: 1.5rem;">
                            @if($quiz->is_published)
                                <span class="badge badge-success" style="width: 100%; justify-content: center;">
                                    <i class="fas fa-check-circle"></i>
                                    Published
                                </span>
                            @else
                                <span class="badge" style="background: var(--warning); color: white; width: 100%; justify-content: center;">
                                    <i class="fas fa-eye-slash"></i>
                                    Draft
                                </span>
                            @endif
                        </div>

                        <!-- Action Button -->
                        <a href="/quizzes/{{ $quiz->id }}/start" class="btn btn-primary btn-block" style="width: 100%; justify-content: center; margin-top: auto;">
                            <i class="fas fa-play"></i>
                            Start Quiz
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem; display: block;"></i>
            <h2 style="color: var(--text-secondary); margin-bottom: 0.5rem;">No Quizzes Available</h2>
            <p style="color: var(--text-light); margin-bottom: 2rem;">
                There are no quizzes available at the moment. Check back soon!
            </p>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Return to Dashboard
            </a>
        </div>
    @endif
@endsection
