@extends('layouts.app')

@section('title', 'Edit Quiz')

@section('content')
    <div class="card">
        <h1 style="margin-bottom: 30px;">Edit Quiz: {{ $quiz->title }}</h1>

        <form method="POST" action="{{ route('quizzes.update', $quiz) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Quiz Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
                @error('title')
                    <small style="color: #dc3545;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description">{{ old('description', $quiz->description) }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pass_percentage">Pass Percentage (%) *</label>
                    <input type="number" id="pass_percentage" name="pass_percentage" min="0" max="100" 
                           value="{{ old('pass_percentage', $quiz->pass_percentage) }}" required>
                </div>

                <div class="form-group">
                    <label for="is_published">
                        <input type="checkbox" id="is_published" name="is_published" value="1" 
                               {{ old('is_published', $quiz->is_published) ? 'checked' : '' }}>
                        Publish this quiz
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn">Update Quiz</button>
                <a href="{{ route('quizzes.manage') }}" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
