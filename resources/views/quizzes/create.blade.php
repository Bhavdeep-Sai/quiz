@extends('layouts.app')

@section('title', 'Create New Quiz')

@section('content')
    <div class="card">
        <h1 style="margin:0 0 1rem;">Create New Quiz</h1>

        <form method="POST" action="{{ route('quizzes.store') }}">
            @csrf

            <div class="form-group">
                <label for="title">Quiz Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" 
                       placeholder="Enter quiz title" required>
                @error('title')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Enter quiz description (optional)">{{ old('description') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pass_percentage">Pass Percentage (%) *</label>
                    <input type="number" id="pass_percentage" name="pass_percentage" min="0" max="100" 
                           value="{{ old('pass_percentage', 60) }}" required>
                    <small style="color: #666;">Percentage required to pass (default: 60%)</small>
                </div>

                <div class="form-group">
                    <label for="is_published">
                        <input type="checkbox" id="is_published" name="is_published" value="1" 
                               {{ old('is_published') ? 'checked' : '' }}>
                        Publish this quiz immediately
                    </label>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Published quizzes can be taken by users. Keep unchecked to save as draft.
                    </small>
                </div>
            </div>

            <div style="display: flex; gap: .75rem; margin-top: 1rem;">
                <button type="submit" class="btn">Create Quiz</button>
                <a href="{{ route('quizzes.manage') }}" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top: 1rem;">
        <h3 style="margin:0 0 .75rem;">Next Steps</h3>
        <ol style="margin:0 0 0 1.1rem; line-height:1.6; color: var(--muted);">
            <li>Fill in the quiz details above</li>
            <li>Click "Create Quiz" to save</li>
            <li>Add questions of different types (True/False, Multiple Choice, etc.)</li>
            <li>Configure scoring and settings for each question</li>
            <li>Publish the quiz to make it available to users</li>
        </ol>
    </div>
@endsection
