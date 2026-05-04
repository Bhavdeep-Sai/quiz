@extends('layouts.app')

@section('title', 'Quiz Results - ' . $quiz->title)

@section('content')
    <div style="margin-bottom: 1rem;">
        <div class="score-card">
            <div style="margin-bottom: 1rem;">
                @if($attempt->is_passed)
                    <i class="fas fa-trophy" style="font-size: 2.5rem; margin-bottom: .75rem; display: block; opacity: 0.95;"></i>
                    <h1 style="font-size: 1.8rem; margin-bottom: .35rem; letter-spacing:-0.03em;">Congratulations!</h1>
                    <p style="font-size: 1rem; opacity: 0.95; margin:0;">You have successfully passed the quiz</p>
                @else
                    <i class="fas fa-clipboard-list" style="font-size: 2.5rem; margin-bottom: .75rem; display: block; opacity: 0.95;"></i>
                    <h1 style="font-size: 1.8rem; margin-bottom: .35rem; letter-spacing:-0.03em;">Quiz Completed</h1>
                    <p style="font-size: 1rem; opacity: 0.95; margin:0;">Review your answers below.</p>
                @endif
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; text-align: center;">
                <div>
                    <div class="score-label">Your Score</div>
                    <div class="score-value">{{ $attempt->total_score }}/{{ $attempt->total_marks }}</div>
                    <div class="score-percentage">{{ $percentage }}%</div>
                </div>

                <div>
                    <div class="score-label">Pass Required</div>
                    <div class="score-value">{{ $quiz->pass_percentage }}%</div>
                    <div style="font-size: 1rem; margin-top: 0.5rem; opacity: 0.9;">
                        @if($attempt->is_passed)
                            <i class="fas fa-check-circle"></i> Passed
                        @else
                            <i class="fas fa-times-circle"></i> Not Passed
                        @endif
                    </div>
                </div>

                <div>
                    <div class="score-label">Time Taken</div>
                    <div class="score-value">{{ $attempt->getDurationFormatted() }}</div>
                    <div style="font-size: 1rem; margin-top: 0.5rem; opacity: 0.9;">
                        <i class="fas fa-hourglass-end"></i>
                    </div>
                </div>

                <div>
                    <div class="score-label">Performance Level</div>
                    <div class="score-value" style="font-size: 2rem;">{{ ucfirst($performanceLevel) }}</div>
                    <div style="font-size: 1rem; margin-top: 0.5rem; opacity: 0.9;">
                        @switch($performanceLevel)
                            @case('excellent')
                                <i class="fas fa-star"></i> Excellent
                            @break
                            @case('good')
                                <i class="fas fa-thumbs-up"></i> Good
                            @break
                            @case('average')
                                <i class="fas fa-minus-circle"></i> Average
                            @break
                            @default
                                <i class="fas fa-arrow-down"></i> Needs Work
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    @if($analytics)
        <div class="grid grid-2" style="margin-bottom: 1rem;">
            <!-- By Question Type -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Performance by Question Type
                    </h2>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-list" style="margin-right: 0.5rem;"></i>Type</th>
                            <th><i class="fas fa-check" style="margin-right: 0.5rem; color: var(--success);"></i>Correct</th>
                            <th><i class="fas fa-question" style="margin-right: 0.5rem;"></i>Total</th>
                            <th><i class="fas fa-percent" style="margin-right: 0.5rem;"></i>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analytics['by_type'] as $type => $data)
                            <tr>
                                <td>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $type)) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i>
                                        {{ $data['correct'] }}
                                    </span>
                                </td>
                                <td>{{ $data['total'] }}</td>
                                <td>
                                    <span style="font-weight: 600; color: @if($data['total'] > 0 && ($data['correct']/$data['total']) >= 0.8) var(--success) @else var(--warning) @endif">
                                        {{ $data['total'] > 0 ? round(($data['correct']/$data['total']*100), 1) : 0 }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Breakdown -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-pie-chart"></i>
                        Overall Performance
                    </h2>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-list" style="margin-right: 0.5rem;"></i>Category</th>
                            <th><i class="fas fa-calculator" style="margin-right: 0.5rem;"></i>Count</th>
                            <th><i class="fas fa-percent" style="margin-right: 0.5rem;"></i>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), transparent);">
                            <td>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i>
                                    Correct
                                </span>
                            </td>
                            <td><strong>{{ $analytics['correct'] }}</strong></td>
                            <td>
                                <strong style="color: var(--success);">
                                    {{ round(($analytics['correct']/$analytics['total']*100), 1) }}%
                                </strong>
                            </td>
                        </tr>
                        <tr style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), transparent);">
                            <td>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times-circle"></i>
                                    Incorrect
                                </span>
                            </td>
                            <td><strong>{{ $analytics['incorrect'] }}</strong></td>
                            <td>
                                <strong style="color: var(--danger);">
                                    {{ round(($analytics['incorrect']/$analytics['total']*100), 1) }}%
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Answer Review -->
    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-list-check"></i>
                Detailed Answer Review
            </h2>
        </div>

        <div style="margin-top: 1rem;">
            @foreach($answers as $index => $answer)
                <div style="background: linear-gradient(135deg, {{ $answer->is_correct ? 'rgba(16, 185, 129, 0.05)' : 'rgba(239, 68, 68, 0.05)' }}, transparent); border-left: 4px solid {{ $answer->is_correct ? 'var(--success)' : 'var(--danger)' }}; padding: 1rem 1rem; margin-bottom: .85rem; border-radius: 16px;">
                    
                    <div style="display: flex; justify-content: space-between; align-items: start; gap:1rem; margin-bottom: .75rem;">
                        <div>
                            <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">
                                <span style="color: var(--text-secondary); font-weight: 500;">Question {{ $index + 1 }}</span>
                            </h3>
                            <p style="color: var(--text-primary); margin-top: 0.5rem;">
                                {{ Str::limit($answer->question->question_text, 150) }}
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                                @if($answer->is_correct)
                                    <span style="color: var(--success);">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                @else
                                    <span style="color: var(--danger);">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                {{ $answer->score }}/{{ $answer->question->marks }} marks
                            </div>
                        </div>
                    </div>

                    @if($answer->feedback)
                        <div style="background: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border-left: 3px solid var(--info);">
                            <strong style="color: var(--info);">
                                <i class="fas fa-lightbulb" style="margin-right: 0.5rem;"></i>
                                Feedback:
                            </strong>
                            <p style="color: var(--text-secondary); margin-top: 0.5rem; margin-bottom: 0;">
                                {{ $answer->feedback }}
                            </p>
                        </div>
                    @endif

                    <div style="color: var(--text-secondary); font-size: 0.95rem;">
                        <strong>Your answer:</strong>
                        <span style="display: inline-block; margin-left: 0.5rem; color: var(--text-primary);">
                            {{ $answer->getUserAnswerText() }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Actions -->
    <div style="display: flex; gap: .75rem; justify-content: center; margin-top: 1rem; flex-wrap:wrap;">
        <a href="/quizzes" class="btn btn-primary">
            <i class="fas fa-book"></i>
            Take Another Quiz
        </a>
        <a href="/" class="btn btn-secondary">
            <i class="fas fa-home"></i>
            Go to Dashboard
        </a>
    </div>
@endsection
