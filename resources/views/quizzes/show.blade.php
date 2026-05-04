@extends('layouts.app')

@section('title', isset($quiz) ? 'Edit Quiz' : 'Create Quiz')

@section('content')
    <div class="card">
        <h1 style="margin-bottom: 30px;">{{ isset($quiz) ? 'Edit Quiz' : 'Create New Quiz' }}</h1>

        <form method="POST" action="{{ isset($quiz) ? route('quizzes.update', $quiz) : route('quizzes.store') }}">
            @csrf
            @if(isset($quiz))
                @method('PUT')
            @endif

            <div class="form-group">
                <label for="title">Quiz Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $quiz->title ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description">{{ old('description', $quiz->description ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pass_percentage">Pass Percentage (%)*</label>
                    <input type="number" id="pass_percentage" name="pass_percentage" min="0" max="100" 
                           value="{{ old('pass_percentage', $quiz->pass_percentage ?? 60) }}" required>
                </div>

                <div class="form-group">
                    <label for="is_published">
                        <input type="checkbox" id="is_published" name="is_published" value="1" 
                               {{ old('is_published', $quiz->is_published ?? false) ? 'checked' : '' }}>
                        Publish this quiz
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn">{{ isset($quiz) ? 'Update Quiz' : 'Create Quiz' }}</button>
                <a href="{{ route('quizzes.manage') }}" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>

    @if(isset($quiz))
        <div class="card">
            <h2 style="margin-bottom: 20px;">Questions in this Quiz</h2>

            @if($quiz->questions->isEmpty())
                <p style="color: #999; text-align: center; padding: 40px;">
                    No questions added yet.
                </p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Question</th>
                            <th>Type</th>
                            <th>Options</th>
                            <th>Marks</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quiz->questions as $index => $question)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ Str::limit($question->question_text, 50) }}</td>
                                <td>
                                    <span style="background: #e7f3ff; color: #004085; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                                    </span>
                                </td>
                                <td>{{ $question->options_count ?? 0 }}</td>
                                <td>{{ $question->marks }}</td>
                                <td style="text-align: center;">
                                    <a href="#edit-question-{{ $question->id }}" class="btn" style="padding: 4px 8px; font-size: 11px; margin-right: 5px;">Edit</a>
                                    <form method="POST" action="{{ route('questions.destroy', [$quiz, $question]) }}" style="display: inline;" onsubmit="return confirm('Delete this question?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn danger" style="padding: 4px 8px; font-size: 11px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #ddd;">
                <h3 style="margin-bottom: 20px;">Add New Question</h3>
                
                <form method="POST" action="{{ route('questions.store', $quiz) }}">
                    @csrf

                    <div class="form-group">
                        <label for="type">Question Type *</label>
                        <select id="type" name="type" required onchange="updateQuestionForm()">
                            <option value="">Select Type</option>
                            <option value="boolean">Boolean (True/False)</option>
                            <option value="single_choice">Single Choice</option>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="number">Number Input</option>
                            <option value="text">Text Input</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="question_text">Question Text *</label>
                        <textarea id="question_text" name="question_text" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input type="url" id="image_url" name="image_url">
                        </div>
                        <div class="form-group">
                            <label for="marks">Marks *</label>
                            <input type="number" id="marks" name="marks" min="1" value="1" required>
                        </div>
                    </div>

                    <button type="submit" class="btn">Add Question</button>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        function updateQuestionForm() {
            const type = document.getElementById('type').value;
            console.log('Question type selected:', type);
            // This would be enhanced with JavaScript to show/hide fields
            // based on the selected question type
        }
    </script>
@endsection
