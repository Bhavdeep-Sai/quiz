# Quiz System Architecture

## 🎯 Overview

The Dynamic Quiz System is built with **clean architecture principles** and **SOLID design patterns**, specifically using the **Strategy Pattern** for question type handling. This document explains the system design and how to extend it.

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                        HTTP Request                          │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────┐
        │      Controllers (HTTP Layer)         │
        │  - QuizController                    │
        │  - AttemptController                 │
        └──────────────┬───────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────────────┐
        │       Services (Business Logic)       │
        │  - EvaluationService                 │
        │  - QuizService                       │
        └──────────────┬───────────────────────┘
                       │
        ┌──────────────┴───────────────┐
        │                              │
        ▼                              ▼
    ┌─────────────┐    ┌──────────────────────────────┐
    │  Resolver   │───▶│  Question Type Handlers      │
    │  (Strategy  │    │  ┌─────────────────────────┐ │
    │   Pattern)  │    │  │ BooleanType             │ │
    │             │    │  │ SingleChoiceType        │ │
    │             │    │  │ MultipleChoiceType      │ │
    │             │    │  │ NumberType              │ │
    │             │    │  │ TextType                │ │
    │             │    │  └─────────────────────────┘ │
    └─────────────┘    └──────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────────┐
                    │   Models (Data)      │
                    │  - Quiz              │
                    │  - Question          │
                    │  - Option            │
                    │  - Attempt           │
                    │  - Answer            │
                    └──────────────────────┘
                              │
                              ▼
                    ┌──────────────────────┐
                    │   Database (MySQL)   │
                    └──────────────────────┘
```

---

## 🎪 Strategy Pattern: Question Type System

### Problem This Solves

Without the Strategy Pattern, handling different question types would require massive if-else chains:

```php
// ❌ BAD: Hardcoded logic
if ($question->type === 'boolean') {
    $result = evaluateBoolean($question, $answer);
} elseif ($question->type === 'single_choice') {
    $result = evaluateSingleChoice($question, $answer);
} elseif ($question->type === 'multiple_choice') {
    $result = evaluateMultipleChoice($question, $answer);
}
// ... many more conditions
```

This is:
- ❌ Hard to maintain
- ❌ Hard to extend
- ❌ Violates Open/Closed Principle
- ❌ Violates Single Responsibility Principle

### Solution: Strategy Pattern

Each question type is a **separate strategy** implementing the same interface:

```php
// ✅ GOOD: Strategy Pattern
$handler = QuestionTypeResolver::resolve($question->type);
$result = $handler->evaluate($question, $userAnswer);
```

### Architecture Components

#### 1. **QuestionTypeInterface** (Contract)

Defines the behavior all question types must implement:

```php
interface QuestionTypeInterface {
    public function evaluate(Question $question, mixed $userAnswer): array;
    public function validate(mixed $userAnswer, Question $question): array;
    public function renderData(Question $question): array;
    public function getType(): string;
    public function supportsPartialScoring(): bool;
}
```

#### 2. **BaseQuestionType** (Abstract Base)

Provides common functionality for all types:

```php
abstract class BaseQuestionType implements QuestionTypeInterface {
    protected function calculateScore(...);
    protected function normalizeAnswer(...);
    protected function renderCommonData(...);
}
```

#### 3. **Concrete Handlers**

Each question type extends `BaseQuestionType`:

- **BooleanType** - True/False questions (no partial scoring)
- **SingleChoiceType** - One correct option
- **MultipleChoiceType** - Multiple correct options (with partial scoring)
- **NumberType** - Numeric input with tolerance
- **TextType** - Free-form text (manual/keyword grading)

#### 4. **QuestionTypeResolver** (Factory)

Maps type strings to handler classes:

```php
private static array $typeMap = [
    'boolean' => BooleanType::class,
    'single_choice' => SingleChoiceType::class,
    'multiple_choice' => MultipleChoiceType::class,
    'number' => NumberType::class,
    'text' => TextType::class,
];

public static function resolve(string $type): QuestionTypeInterface {
    return new self::$typeMap[$type]();
}
```

---

## 🔄 Data Flow

### 1. **Quiz Creation Flow**

```
Controller → QuizService → Quiz Model
              ↓
         Question Model + Options
```

### 2. **Quiz Attempt Flow**

```
Start Quiz
    ↓
Create Attempt Record
    ↓
Render Questions (via Resolver → Handler.renderData())
    ↓
User Answers Questions
    ↓
Submit Answers
    ↓
EvaluationService.evaluateAttempt()
    ├─ For each answer:
    │  ├─ Resolver.resolve(question.type)
    │  ├─ Handler.validate(answer)
    │  ├─ Handler.evaluate(question, answer)
    │  └─ Create Answer record
    ├─ Calculate total score
    ├─ Check if passed
    └─ Update Attempt status
    ↓
Show Results
```

### 3. **Answer Evaluation Flow**

```
EvaluationService.evaluateAnswer(question, userAnswer)
    ↓
QuestionTypeResolver.resolve(question.type)
    ↓
[Handler].evaluate(question, userAnswer)
    ├─ validate(userAnswer, question)
    ├─ calculate score
    ├─ check correctness
    └─ build feedback
    ↓
return ['score' => float, 'is_correct' => bool, 'feedback' => string]
```

---

## 📂 Directory Structure

```
app/
├── Models/
│   ├── Quiz.php              # Quiz aggregate
│   ├── Question.php          # Question entity
│   ├── Option.php            # Answer option
│   ├── Attempt.php           # Quiz attempt
│   ├── Answer.php            # User answer
│   ├── ResultBreakdown.php   # Analytics
│   └── AuditLog.php          # Change tracking

├── QuestionTypes/            # ⭐ Question Type System
│   ├── Contracts/
│   │   └── QuestionTypeInterface.php  # Strategy interface
│   ├── BaseQuestionType.php          # Abstract base
│   ├── Types/
│   │   ├── BooleanType.php           # Strategy 1
│   │   ├── SingleChoiceType.php      # Strategy 2
│   │   ├── MultipleChoiceType.php    # Strategy 3
│   │   ├── NumberType.php            # Strategy 4
│   │   └── TextType.php              # Strategy 5
│   └── QuestionTypeResolver.php      # Factory

├── Services/
│   ├── EvaluationService.php         # Evaluation logic
│   └── QuizService.php               # Quiz operations

├── Http/
│   ├── Controllers/
│   │   ├── QuizController.php
│   │   └── AttemptController.php
│   ├── Requests/                     # Form requests (validation)
│   └── Resources/                    # API responses

├── Repositories/                     # (Optional) Data access layer
└── Exceptions/

database/
├── migrations/
│   ├── create_quizzes_table
│   ├── create_questions_table
│   ├── create_options_table
│   ├── create_attempts_table
│   ├── create_answers_table
│   ├── create_result_breakdowns_table
│   └── create_quiz_audit_logs_table
└── factories/
    ├── QuizFactory.php
    ├── QuestionFactory.php
    ├── OptionFactory.php
    └── AttemptFactory.php

tests/
└── Unit/QuestionTypes/
    ├── BooleanTypeTest.php
    └── MultipleChoiceTypeTest.php
```

---

## 🚀 How to Add a New Question Type

The beauty of the Strategy Pattern is that **adding new question types requires minimal changes**.

### Step 1: Create a New Handler

Create a new file in `app/QuestionTypes/Types/YourNewType.php`:

```php
<?php

namespace App\QuestionTypes\Types;

use App\Models\Question;
use App\QuestionTypes\BaseQuestionType;

class ImageMatchType extends BaseQuestionType
{
    protected string $type = 'image_match';
    protected bool $partialScoringSupported = true;

    public function evaluate(Question $question, mixed $userAnswer): array
    {
        // Your evaluation logic
        return [
            'score' => $score,
            'is_correct' => $isCorrect,
            'feedback' => $feedback,
        ];
    }

    public function validate(mixed $userAnswer, Question $question): array
    {
        // Your validation logic
        return ['valid' => true];
    }

    public function renderData(Question $question): array
    {
        return array_merge($this->renderCommonData($question), [
            'type' => 'image_match',
            // Your custom data
        ]);
    }
}
```

### Step 2: Register in Resolver

Add one line to `app/QuestionTypes/QuestionTypeResolver.php`:

```php
private static array $typeMap = [
    'boolean' => BooleanType::class,
    'single_choice' => SingleChoiceType::class,
    'multiple_choice' => MultipleChoiceType::class,
    'number' => NumberType::class,
    'text' => TextType::class,
    'image_match' => ImageMatchType::class,  // ← Add this
];
```

### Step 3: Done!

That's it! No other code changes needed:
- ✅ EvaluationService automatically uses your handler
- ✅ Controllers automatically support your type
- ✅ Database migrations support JSON settings for flexibility

### Example: Adding "Drag & Drop" Question Type

```php
// app/QuestionTypes/Types/DragDropType.php
class DragDropType extends BaseQuestionType
{
    protected string $type = 'drag_drop';
    protected bool $partialScoringSupported = true;

    public function evaluate(Question $question, mixed $userAnswer): array
    {
        // $userAnswer is an array like: [1 => 4, 2 => 5, 3 => 6]
        // Where key is question item ID, value is target ID
        
        $correctMappings = $question->settings['correct_mappings'] ?? [];
        $matches = 0;
        
        foreach ($userAnswer as $itemId => $targetId) {
            if (($correctMappings[$itemId] ?? null) === $targetId) {
                $matches++;
            }
        }
        
        $percentage = count($correctMappings) > 0 ? $matches / count($correctMappings) : 0;
        $score = $this->calculateScore($question, true, $percentage);
        
        return [
            'score' => $score,
            'is_correct' => $percentage === 1.0,
            'feedback' => "You matched $matches out of " . count($correctMappings),
        ];
    }

    public function validate(mixed $userAnswer, Question $question): array
    {
        if (!is_array($userAnswer)) {
            return ['valid' => false, 'error' => 'Invalid format'];
        }
        return ['valid' => true];
    }

    public function renderData(Question $question): array
    {
        return array_merge($this->renderCommonData($question), [
            'type' => 'drag_drop',
            'items' => $question->settings['items'] ?? [],
            'targets' => $question->settings['targets'] ?? [],
        ]);
    }
}
```

Register it:
```php
// In QuestionTypeResolver
'drag_drop' => DragDropType::class,
```

**Done!** The system now supports drag & drop questions.

---

## 💾 Database Design

### Flexible Schema Pattern

The database uses JSON columns for flexibility:

**questions.settings** (JSON):
```json
{
  "shuffle_options": true,
  "strict_mode": true,
  "min_selections": 1,
  "max_selections": 5,
  "expected_answer": 42,
  "tolerance": 0.5,
  "keywords": ["paris", "france"],
  "grade_mode": "keyword"
}
```

**answers.user_answer** (JSON):
```json
// Boolean: true
// Single choice: 5
// Multiple choice: [2, 5, 8]
// Number: 42.5
// Text: "Paris is the capital of France"
```

This design means:
- ✅ New question types don't need schema changes
- ✅ Settings are type-specific
- ✅ Flexible and extensible

---

## 🧪 Testing

### Unit Tests for Handlers

Each handler has comprehensive tests:

```php
// tests/Unit/QuestionTypes/BooleanTypeTest.php
class BooleanTypeTest extends TestCase {
    public function test_correct_true_answer() { }
    public function test_correct_false_answer() { }
    public function test_incorrect_answer() { }
    public function test_validation_with_valid_answer() { }
    public function test_validation_with_null_answer() { }
    public function test_render_data() { }
}
```

Run tests:
```bash
docker-compose exec php php artisan test tests/Unit/QuestionTypes
```

---

## 🎯 SOLID Principles Compliance

### Single Responsibility Principle (SRP)
- **BooleanType** only handles boolean questions
- **EvaluationService** only handles evaluation orchestration
- **QuizService** only handles quiz operations

### Open/Closed Principle (OCP)
- ✅ Open for extension (add new handlers)
- ✅ Closed for modification (existing handlers unchanged)

### Liskov Substitution Principle (LSP)
- All handlers implement `QuestionTypeInterface`
- Handlers are interchangeable

### Interface Segregation Principle (ISP)
- `QuestionTypeInterface` defines minimal required methods
- Each handler implements exactly what it needs

### Dependency Inversion Principle (DIP)
- Controllers depend on interfaces, not concrete handlers
- `QuestionTypeResolver` abstracts handler creation

---

## 🔌 Integration with Laravel

### Service Container

Register services in `bootstrap/providers.php` or `AppServiceProvider`:

```php
$this->app->singleton('quiz.evaluation', function ($app) {
    return new EvaluationService();
});
```

### Using in Controllers

```php
class AttemptController extends Controller {
    public function __construct(
        private EvaluationService $evaluationService,
        private QuizService $quizService
    ) {}

    public function submit(Attempt $attempt, SubmitQuizRequest $request) {
        $result = $this->evaluationService->evaluateAttempt(
            $attempt,
            $request->answers
        );
        return view('results.show', $result);
    }
}
```

---

## 🎪 Features

### ✅ Core Features
- 5 Question Types (Boolean, SingleChoice, MultipleChoice, Number, Text)
- Rich Media Support (Images, YouTube videos)
- Full Evaluation System
- Quiz Attempts & Scoring
- Result Analytics

### ✅ Bonus Features
- **Partial Scoring**: MultipleChoice and Number types support partial credit
- **Result Breakdown**: Analytics by question type and performance level
- **Timer**: Tracks time spent per question and per attempt
- **Audit Trail**: Track all changes to quizzes and questions
- **Auto-Grading**: Keyword matching for text questions

---

## 📊 Extensibility Matrix

| Aspect | Extensibility | How |
|--------|---------------|-----|
| Question Types | ⭐⭐⭐⭐⭐ | Create new handler class |
| Evaluation Logic | ⭐⭐⭐⭐⭐ | Override handler methods |
| Scoring System | ⭐⭐⭐⭐ | Extend BaseQuestionType |
| Database Schema | ⭐⭐⭐ | Add migrations (avoid breaking) |
| UI Rendering | ⭐⭐⭐⭐⭐ | Create Blade templates per type |
| API Responses | ⭐⭐⭐⭐⭐ | Create API Resources |

---

## 🚨 Production Considerations

1. **Caching**: Cache question data and handler resolutions
2. **Rate Limiting**: Implement rate limits on quiz submission
3. **Analytics**: Store performance metrics for reporting
4. **Audit Logging**: Track all changes for compliance
5. **Error Handling**: Graceful degradation for missing handlers
6. **Performance**: Index frequently queried fields

---

## 📚 Resources

- **Design Patterns**: Refactoring.Guru Strategy Pattern
- **Laravel Documentation**: https://laravel.com/docs
- **SOLID Principles**: Uncle Bob's Clean Code
- **Repository Pattern**: Eric Evans - Domain-Driven Design

---

## 🤝 Contributing

To add a new question type:
1. Create a handler in `app/QuestionTypes/Types/`
2. Register in `QuestionTypeResolver`
3. Write unit tests
4. Update documentation

No core system changes needed!
