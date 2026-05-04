@php
    $types = app(\App\Services\QuizService::class)->getAvailableQuestionTypes();
@endphp

<div class="question-builder">
    <div class="form-row">
        <label for="type">Question Type</label>
        <select id="question-type" name="type" class="form-control">
            @foreach($types as $t)
                <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
            @endforeach
        </select>
    </div>

    <div id="question-type-hint" style="margin-top:8px; font-size:0.9rem; opacity:0.8;"></div>

    <div class="form-row">
        <label for="question_text">Question Text</label>
        <textarea name="question_text" id="question_text" class="form-control" rows="3"></textarea>
    </div>

    <div id="options-section" style="display:none; margin-top:10px;">
        <label>Options</label>
        <div id="options-list"></div>
        <button type="button" id="add-option" class="btn btn-sm btn-outline-primary">Add option</button>
    </div>

    <div id="short-answer-section" style="display:none; margin-top:10px;">
        <label>Correct Answer</label>
        <input type="text" name="settings[correct_answer]" class="form-control" placeholder="Enter the expected short answer" />
        <label style="margin-top:8px; display:block;">Optional Keywords</label>
        <input type="text" name="settings[keywords_input]" class="form-control" placeholder="Comma-separated keywords" />
        <input type="hidden" name="settings[grade_mode]" value="exact" />
    </div>

    <div id="long-answer-section" style="display:none; margin-top:10px;">
        <label>Model Answer</label>
        <textarea name="settings[model_answer]" class="form-control" rows="4" placeholder="Enter the reference answer for manual grading"></textarea>
        <label style="margin-top:8px; display:block;">Grading Notes</label>
        <textarea name="settings[notes]" class="form-control" rows="2" placeholder="Optional rubric or grading guidance"></textarea>
        <input type="hidden" name="settings[grade_mode]" value="manual" />
    </div>

    <input type="hidden" name="options_payload" id="options_payload" />

</div>

<script src="/js/admin-question-builder.js"></script>
