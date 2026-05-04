# User Manual

## Audience

- Quiz administrators
- Quiz participants

## 1. Getting Started

1. Open the app: `http://localhost:8000`
2. Navigate using the top menu.
3. Admin operations are under quiz management screens.

## 2. Administrator Guide

## 2.1 Create a Quiz

1. Go to Admin Quiz Management.
2. Click Create Quiz.
3. Fill in:
   - title
   - description
   - pass percentage
   - publish status
4. Save.

## 2.2 Add Questions

For each quiz, add questions with one of five types:

- `boolean`
- `single_choice`
- `multiple_choice`
- `number`
- `text`

You can configure per-question marks and optional settings.

## 2.3 Manage Questions

- Edit question text, marks, options, and settings.
- Delete obsolete questions.
- Verify order using `sort_order`.

## 2.4 Publish a Quiz

- Set quiz to published to make it available to participants.
- Unpublished quizzes remain hidden from public quiz listing.

## 2.5 Review Attempts

- Open Attempts list for a quiz.
- Review score, pass/fail status, timing, and detailed answers.
- Use statistics to identify low-performing areas.

## 3. Participant Guide

## 3.1 Start a Quiz

1. Open quiz list.
2. Select a published quiz.
3. Enter user details (name/email/identifier where requested).
4. Start attempt.

## 3.2 Answer Questions

- Boolean: choose True/False.
- Single choice: choose one option.
- Multiple choice: choose one or more options.
- Number: enter numeric value.
- Text: enter free-form answer.

## 3.3 Submit Attempt

- Review answers.
- Submit when done.
- Time spent is recorded in seconds.

## 3.4 View Result

Result view includes:

- total score
- total marks
- percentage
- pass/fail
- performance level
- answer-level breakdown

## 4. Scoring Rules

- Boolean and Single Choice are exact-match.
- Multiple Choice supports partial scoring in non-strict mode.
- Number supports tolerance/range behavior based on settings.
- Text supports keyword-based scoring behavior based on settings.

## 5. API Users

If you integrate from mobile/frontend clients, use:

- Base: `/api/v1`
- Documentation: `/api/docs`
- Full reference: `API_DOCUMENTATION.md`

## 6. Common Tips

- Keep question text explicit.
- Assign marks based on difficulty.
- Use published/unpublished to stage content safely.
- Validate quiz flow by doing one test attempt before sharing.
