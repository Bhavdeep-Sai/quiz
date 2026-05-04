Flexible Question System
========================

Overview
--------
This project already had a strategy-based question system. Changes made improve clarity, validation and admin UX to support:

- True/False
- MCQ (single correct)
- MCQ (multiple correct)
- Short answer (keyword/exact)
- Long answer (manual grading)

Database
--------

Questions table (existing)
- id, quiz_id, type (enum: boolean, single_choice, multiple_choice, number, text), question_text, marks, settings (json), ...

Options table (existing)
- id, question_id, label, is_correct, image_url, sort_order

Answers table (existing)
- id, attempt_id, question_id, user_answer (json), question_type, score, is_correct, ...

Compatibility
-------------
To keep backward compatibility while providing friendly constants, `Question` model exposes type constants:

- `Question::TYPE_TRUE_FALSE` -> 'boolean'
- `Question::TYPE_MCQ_SINGLE` -> 'single_choice'
- `Question::TYPE_MCQ_MULTIPLE` -> 'multiple_choice'
- `Question::TYPE_SHORT_ANSWER` -> 'text'
- `Question::TYPE_LONG_ANSWER` -> 'text'

Validation & Business Rules
---------------------------
- MCQ: requires at least 2 options and at least 1 correct option. Single choice requires exactly 1 correct option.
- True/False: auto-generates True/False options if not provided (can set correct via `settings.correct`).
- Short answer: supports keyword-based autochecking via `settings.keywords` and `settings.grade_mode`.
- Long answer: treated as manual grading (pending) unless `settings.grade_mode` set to `keyword`.

Admin UI
--------

Added a simple dynamic admin form at `resources/views/admin/questions/form.blade.php` and client helper at `public/js/admin-question-builder.js`.

Frontend connections
--------------------

- Admin quiz page: `resources/views/quizzes/show.blade.php` now includes the dynamic builder partial and submits to `QuizController@storeQuestion`.
- Quiz taking page: `app/Http/Controllers/AttemptController.php` now routes both the start flow and the active attempt flow to `resources/views/attempts/show-new.blade.php`.
- Shared question rendering: `resources/views/attempts/partials/question-input.blade.php` switches the input control by question type and subtype.
- Question creation endpoints accept either `options` arrays or the builder's `options_payload` JSON.

API
---
Existing API endpoints remain compatible. The `ApiQuestionController` now returns validation errors (422) for invalid question payloads produced by the service.

Next steps
----------
- Integrate `form.blade.php` into your admin layout and wire form submission to `/api/quizzes/{quiz}/questions` (POST/PUT). The form produces `options_payload` which you should parse on submit.
- Add richer admin visuals and polish JS to match your frontend stack (Vue/React) if used.
