@php
    $answerSubtype = $question->settings['answer_subtype'] ?? $question->type;
@endphp

<div class="question-panel" data-question-type="{{ $question->type }}" data-answer-subtype="{{ $answerSubtype }}">
    <div class="question-type-badge">
        {{ strtoupper(str_replace('_', ' ', $answerSubtype)) }}
    </div>

    <div class="question-text">
        {!! $question->question_text !!}
    </div>

    @if($question->image_url)
        <img src="{{ $question->image_url }}" alt="Question image" class="question-image">
    @endif

    @if($question->type === 'boolean')
        <div class="answer-stack">
            <label class="option-label">
                <input type="radio" name="answers[{{ $question->id }}]" value="true" required>
                <span class="option-text">True</span>
            </label>
            <label class="option-label">
                <input type="radio" name="answers[{{ $question->id }}]" value="false" required>
                <span class="option-text">False</span>
            </label>
        </div>
    @elseif(in_array($question->type, ['single_choice', 'multiple_choice'], true))
        <div class="answer-stack">
            @foreach($question->options as $option)
                <label class="option-label {{ $question->type === 'multiple_choice' ? 'option-label-multi' : '' }}">
                    <input
                        type="{{ $question->type === 'single_choice' ? 'radio' : 'checkbox' }}"
                        name="answers[{{ $question->id }}]{{ $question->type === 'multiple_choice' ? '[]' : '' }}"
                        value="{{ $option->id }}"
                        {{ $question->type === 'single_choice' ? 'required' : '' }}
                    >
                    <span class="option-text">{{ $option->label }}</span>
                </label>
            @endforeach
        </div>
    @elseif($answerSubtype === 'short_answer')
        <div class="answer-stack">
            <input type="text" name="answers[{{ $question->id }}]" placeholder="Enter your answer" required>
        </div>
    @else
        <div class="answer-stack">
            <textarea name="answers[{{ $question->id }}]" placeholder="Enter your answer" rows="5" required></textarea>
        </div>
    @endif
</div>