@extends('layouts.app')

@section('title', 'Manage Quizzes')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>Quiz Management</h1>
            <a href="{{ route('quizzes.create') }}" class="btn">+ Create New Quiz</a>
        </div>

        @if($quizzes->isEmpty())
            <p style="text-align: center; color: #999; padding: 40px;">
                No quizzes yet. 
                <a href="{{ route('quizzes.create') }}" style="color: #667eea;">Create one now</a>
            </p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Questions</th>
                        <th>Attempts</th>
                        <th>Pass %</th>
                        <th>Status</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizzes as $quiz)
                        <tr>
                            <td>
                                <strong>{{ $quiz->title }}</strong>
                                <br>
                                <small style="color: #999;">{{ Str::limit($quiz->description, 50) }}</small>
                            </td>
                            <td>{{ $quiz->questions_count ?? 0 }}</td>
                            <td>{{ $quiz->attempts_count ?? 0 }}</td>
                            <td>{{ $quiz->pass_percentage }}%</td>
                            <td>
                                @if($quiz->is_published)
                                    <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        Published
                                    </span>
                                @else
                                    <span style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('quizzes.show', $quiz) }}" class="btn" style="padding: 6px 12px; font-size: 12px; margin-right: 5px;">Edit</a>
                                <a href="{{ route('attempts.list', $quiz) }}" class="btn secondary" style="padding: 6px 12px; font-size: 12px; margin-right: 5px;">Results</a>
                                <form method="POST" action="{{ route('quizzes.destroy', $quiz) }}" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn danger" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>
@endsection
