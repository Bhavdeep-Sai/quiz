@extends('layouts.app')

@section('title', 'Quiz Attempts: ' . $quiz->title)

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>{{ $quiz->title }} - Attempts</h1>
                <p style="color: #666; margin-top: 5px;">{{ $attempts->total() }} total attempts</p>
            </div>
            <a href="{{ route('quizzes.show', $quiz) }}" class="btn secondary">← Back to Quiz</a>
        </div>

        @if($attempts->isEmpty())
            <p style="text-align: center; color: #999; padding: 40px;">
                No attempts yet for this quiz.
            </p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Duration</th>
                        <th>Date</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                        <tr>
                            <td>
                                <strong>{{ $attempt->user_name }}</strong>
                                @if($attempt->user_identifier)
                                    <br><small style="color: #999;">{{ $attempt->user_identifier }}</small>
                                @endif
                            </td>
                            <td>{{ $attempt->user_email ?? '-' }}</td>
                            <td>
                                <strong>{{ $attempt->total_score }}/{{ $attempt->total_marks }}</strong>
                            </td>
                            <td>
                                <strong style="color: {{ $attempt->is_passed ? '#28a745' : '#dc3545' }};">
                                    {{ $attempt->getPercentage() }}%
                                </strong>
                            </td>
                            <td>
                                @if($attempt->is_passed)
                                    <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        ✓ Passed
                                    </span>
                                @else
                                    <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        ✗ Failed
                                    </span>
                                @endif
                            </td>
                            <td>{{ $attempt->getDurationFormatted() }}</td>
                            <td style="font-size: 12px; color: #999;">
                                {{ $attempt->submitted_at->format('M d, Y H:i') }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('attempts.result', $attempt) }}" class="btn" style="padding: 6px 12px; font-size: 12px;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $attempts->links() }}
            </div>

            <!-- Summary Stats -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 30px; padding-top: 30px; border-top: 1px solid #ddd;">
                @php
                    $passed = $attempts->getCollection()->where('is_passed', true)->count();
                    $failed = $attempts->total() - $passed;
                    $avgScore = $attempts->getCollection()->avg('total_score');
                    $avgPercentage = $attempts->getCollection()->avg(function($a) { return $a->getPercentage(); });
                @endphp

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $passed }}</div>
                    <div style="color: #666; margin-top: 5px;">Passed</div>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #dc3545;">{{ $failed }}</div>
                    <div style="color: #666; margin-top: 5px;">Failed</div>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #667eea;">{{ round($avgScore, 1) }}</div>
                    <div style="color: #666; margin-top: 5px;">Avg Score</div>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #667eea;">{{ round($avgPercentage, 1) }}%</div>
                    <div style="color: #666; margin-top: 5px;">Avg Percentage</div>
                </div>
            </div>
        @endif
    </div>
@endsection
