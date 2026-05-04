@extends('layouts.professional')

@section('title', 'Available Quizzes - QuizMaster')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; margin-bottom:1rem; flex-wrap:wrap;">
        <div>
            <h1 style="font-size:1.65rem; margin:0 0 .35rem; letter-spacing:-0.03em;"><i class="fas fa-book-open" style="color: var(--brand-2); margin-right:.55rem;"></i>Available Quizzes</h1>
            <p style="color: var(--muted); margin:0;">Choose a quiz and begin.</p>
        </div>
        <a href="/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    @if(count($quizzes) > 0)
        <div class="grid grid-3">
            @foreach($quizzes as $quiz)
                <a href="/quizzes/{{ $quiz->id }}/start" class="quiz-item">
                    <div class="quiz-item-title"><i class="fas fa-scroll" style="color: var(--brand-2);"></i>{{ $quiz->title }}</div>
                    <p style="color: var(--muted); margin:0; line-height:1.55;">{{ Str::limit($quiz->description, 90) }}</p>
                    <div class="quiz-item-meta">
                        <span><i class="fas fa-question-circle"></i>{{ $quiz->questions_count ?? count($quiz->questions) }} questions</span>
                        <span><i class="fas fa-star"></i>{{ $quiz->questions->sum('marks') ?? 0 }} marks</span>
                        <span><i class="fas fa-percent"></i>{{ $quiz->pass_percentage }}% pass</span>
                    </div>
                    <div style="margin-top:.9rem; display:flex; justify-content:space-between; align-items:center; gap:.75rem;">
                        @if($quiz->is_published)
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i>Published</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-eye-slash"></i>Draft</span>
                        @endif
                        <span class="btn btn-primary btn-small">Start</span>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="card" style="text-align:center; padding: 2rem;">
            <i class="fas fa-inbox" style="font-size: 2.25rem; color: var(--muted); margin-bottom: .75rem; display:block;"></i>
            <h2 style="margin:0 0 .25rem;">No quizzes available</h2>
            <p style="color: var(--muted); margin:0 0 1rem;">There are no published quizzes yet.</p>
            <a href="/" class="btn btn-primary">Return to Dashboard</a>
        </div>
    @endif
@endsection
