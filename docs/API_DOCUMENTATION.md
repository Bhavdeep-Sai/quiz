# Dynamic Quiz System - API Documentation

## Overview

The Dynamic Quiz System provides a comprehensive REST API for programmatic access to all quiz functionality. The API is designed to support mobile applications, third-party integrations, and advanced customizations.

**Base URL**: `http://localhost/api/v1`  
**Format**: JSON  
**Authentication**: Public API (no authentication required)  

---

## API Response Format

All API responses follow a consistent format:

### Success Response (2xx)
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { /* response data */ },
  "meta": {
    "timestamp": "2026-05-02T10:00:00Z"
  }
}
```

### Error Response (4xx, 5xx)
```json
{
  "success": false,
  "error": "Error code",
  "message": "Human-readable error message",
  "errors": { /* validation errors if applicable */ }
}
```

---

## HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET, PUT requests |
| 201 | Created | Successful POST requests creating resources |
| 400 | Bad Request | Invalid request format |
| 403 | Forbidden | Access denied (e.g., unpublished quiz) |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Server Error | Internal server error |

---

## Endpoints

### Health & Status

#### Check Health
```
GET /api/health
```

Check application health status.

**Response:**
```json
{
  "success": true,
  "status": "ok",
  "timestamp": "2026-05-02T10:00:00Z",
  "app": {
    "name": "Dynamic Quiz System",
    "environment": "production",
    "version": "1.0.0"
  },
  "checks": {
    "database": "ok",
    "cache": "ok",
    "queue": "ok"
  }
}
```

---

#### Get System Status
```
GET /api/status
```

Get detailed system status and metrics.

**Response:**
```json
{
  "success": true,
  "status": "operational",
  "timestamp": "2026-05-02T10:00:00Z",
  "metrics": {
    "quizzes": 10,
    "questions": 150,
    "attempts": 500,
    "uptime": "∞"
  },
  "components": {
    "database": { "status": "ok" },
    "api": { "status": "ok" },
    "cache": { "status": "ok" }
  }
}
```

---

### Quizzes

#### List All Published Quizzes
```
GET /api/v1/quizzes
```

Retrieve all published quizzes with pagination.

**Query Parameters:**
- `per_page` (integer, optional): Results per page (default: 15)
- `page` (integer, optional): Page number (default: 1)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Python Basics Quiz",
      "description": "Test your Python knowledge",
      "pass_percentage": 60,
      "is_published": true,
      "question_count": 10,
      "total_marks": 100,
      "attempt_count": 25,
      "created_at": "2026-05-01T10:00:00Z",
      "updated_at": "2026-05-02T10:00:00Z"
    }
  ],
  "meta": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4
  }
}
```

---

#### Get Quiz Details
```
GET /api/v1/quizzes/{id}
```

Retrieve a specific quiz with all questions and options.

**Path Parameters:**
- `id` (integer, required): Quiz ID

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Python Basics Quiz",
    "description": "Test your Python knowledge",
    "pass_percentage": 60,
    "total_marks": 100,
    "question_count": 10,
    "questions": [
      {
        "id": 1,
        "type": "multiple_choice",
        "question_text": "Which is not a Python data type?",
        "image_url": null,
        "video_url": null,
        "marks": 10,
        "options": [
          {
            "id": 1,
            "label": "List",
            "image_url": null
          },
          {
            "id": 2,
            "label": "Dictionary",
            "image_url": null
          }
        ]
      }
    ],
    "statistics": {
      "total_attempts": 25,
      "successful_attempts": 15,
      "success_rate": 60.0,
      "average_score": 65.5,
      "average_percentage": 65.5
    }
  }
}
```

---

#### Create Quiz (Admin)
```
POST /api/v1/admin/quizzes
```

Create a new quiz. Requires admin access.

**Request Body:**
```json
{
  "title": "New Quiz",
  "description": "Quiz description",
  "pass_percentage": 70,
  "is_published": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Quiz created successfully",
  "data": {
    "id": 11,
    "title": "New Quiz",
    "pass_percentage": 70,
    "is_published": false
  }
}
```

---

#### Update Quiz (Admin)
```
PUT /api/v1/admin/quizzes/{id}
```

Update an existing quiz.

**Path Parameters:**
- `id` (integer, required): Quiz ID

**Request Body:**
```json
{
  "title": "Updated Title",
  "pass_percentage": 75,
  "is_published": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Quiz updated successfully",
  "data": { /* updated quiz object */ }
}
```

---

#### Delete Quiz (Admin)
```
DELETE /api/v1/admin/quizzes/{id}
```

Delete a quiz and all its associated data.

**Response:**
```json
{
  "success": true,
  "message": "Quiz deleted successfully"
}
```

---

#### Get Quiz Statistics
```
GET /api/v1/quizzes/{id}/statistics
```

Get statistics for a specific quiz.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_attempts": 100,
    "successful_attempts": 60,
    "success_rate": 60.0,
    "average_score": 67.5,
    "average_percentage": 67.5,
    "total_marks": 100
  }
}
```

---

### Questions

#### List Questions for Quiz
```
GET /api/v1/quizzes/{quiz_id}/questions
```

Get all questions for a specific quiz.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "multiple_choice",
      "question_text": "Question text here?",
      "image_url": null,
      "marks": 10,
      "settings": {},
      "sort_order": 0,
      "options": [
        { "id": 1, "label": "Option A", "is_correct": false },
        { "id": 2, "label": "Option B", "is_correct": true }
      ]
    }
  ]
}
```

---

#### Get Question Details
```
GET /api/v1/quizzes/{quiz_id}/questions/{id}
```

Get a specific question with all options.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "type": "multiple_choice",
    "question_text": "Which is not a Python data type?",
    "marks": 10,
    "settings": {
      "strict_mode": false,
      "min_selections": 1,
      "max_selections": 3
    },
    "options": [...]
  }
}
```

---

#### Create Question (Admin)
```
POST /api/v1/admin/quizzes/{quiz_id}/questions
```

Add a new question to a quiz.

**Request Body:**
```json
{
  "type": "multiple_choice",
  "question_text": "What is 2 + 2?",
  "marks": 5,
  "image_url": null,
  "settings": {
    "min_selections": 1,
    "max_selections": 2
  },
  "options": [
    { "label": "3", "is_correct": false },
    { "label": "4", "is_correct": true },
    { "label": "5", "is_correct": false }
  ]
}
```

**Valid Types:**
- `boolean` - True/False
- `single_choice` - One correct answer
- `multiple_choice` - Multiple correct answers
- `number` - Numeric input
- `text` - Free text

**Response:**
```json
{
  "success": true,
  "message": "Question created successfully",
  "data": {
    "id": 25,
    "type": "multiple_choice",
    "question_text": "What is 2 + 2?",
    "marks": 5
  }
}
```

---

#### Update Question (Admin)
```
PUT /api/v1/admin/quizzes/{quiz_id}/questions/{id}
```

Update an existing question.

**Response:** Same as create

---

#### Delete Question (Admin)
```
DELETE /api/v1/admin/quizzes/{quiz_id}/questions/{id}
```

Delete a question from a quiz.

---

### Attempts (Quiz Taking)

#### Start Quiz Attempt
```
POST /api/v1/quizzes/{quiz_id}/attempts
```

Initialize a new quiz attempt for a user.

**Request Body:**
```json
{
  "user_name": "John Doe",
  "user_email": "john@example.com",
  "user_identifier": "STU123456"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Attempt started",
  "data": {
    "attempt_id": 42,
    "quiz_id": 1,
    "started_at": "2026-05-02T10:00:00Z",
    "questions": [
      {
        "id": 1,
        "type": "multiple_choice",
        "question_text": "What is 2 + 2?",
        "marks": 10,
        "options": [...]
      }
    ]
  }
}
```

---

#### Submit Quiz Answers
```
POST /api/v1/attempts/{attempt_id}/submit
```

Submit all answers for a quiz attempt.

**Request Body:**
```json
{
  "answers": {
    "1": ["2", "4"],
    "2": "3.14",
    "3": "The capital of France is Paris",
    "4": true
  },
  "time_spent": 1230
}
```

**Notes:**
- `answers` object must have all question IDs as keys
- Answer value format depends on question type:
  - Boolean: `true` or `false`
  - Single Choice: Option ID (string or integer)
  - Multiple Choice: Array of option IDs
  - Number: Numeric value
  - Text: String value
- `time_spent`: Total time in seconds

**Response:**
```json
{
  "success": true,
  "message": "Quiz submitted successfully",
  "data": {
    "attempt_id": 42,
    "score": 75,
    "marks": 100,
    "percentage": 75.0,
    "is_passed": true,
    "performance_level": "good",
    "time_spent_seconds": 1230,
    "submitted_at": "2026-05-02T10:30:00Z"
  }
}
```

---

#### Get Attempt Details
```
GET /api/v1/attempts/{attempt_id}
```

Retrieve complete details of a quiz attempt including all answers.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "quiz_id": 1,
    "user_name": "John Doe",
    "user_email": "john@example.com",
    "user_identifier": "STU123456",
    "score": 75,
    "marks": 100,
    "percentage": 75.0,
    "is_passed": true,
    "performance_level": "good",
    "time_spent_seconds": 1230,
    "started_at": "2026-05-02T10:00:00Z",
    "submitted_at": "2026-05-02T10:30:00Z",
    "answers": [
      {
        "question_id": 1,
        "question_text": "What is 2 + 2?",
        "question_type": "multiple_choice",
        "user_answer": ["2", "4"],
        "score": 10,
        "marks": 10,
        "is_correct": true,
        "feedback": "Excellent!"
      }
    ]
  }
}
```

---

#### Get Attempt Statistics
```
GET /api/v1/attempts/{attempt_id}/statistics
```

Get analytics and breakdown for an attempt.

**Response:**
```json
{
  "success": true,
  "data": {
    "score": 75,
    "marks": 100,
    "percentage": 75.0,
    "is_passed": true,
    "performance_level": "good",
    "time_spent_seconds": 1230,
    "analytics": {
      "correct": 7,
      "incorrect": 3,
      "total": 10,
      "by_type": {
        "multiple_choice": {
          "correct": 3,
          "total": 4,
          "percentage": 75
        },
        "number": {
          "correct": 2,
          "total": 3,
          "percentage": 66.67
        }
      }
    }
  }
}
```

---

#### Auto-Save Answer
```
POST /api/v1/attempts/{attempt_id}/save-answer
```

Save a single answer during quiz taking (useful for auto-save feature).

**Request Body:**
```json
{
  "question_id": 1,
  "answer": ["2", "4"]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Answer saved"
}
```

---

#### List Attempts for Quiz (Admin)
```
GET /api/v1/admin/quizzes/{quiz_id}/attempts
```

Get all attempts for a specific quiz with pagination.

**Query Parameters:**
- `per_page` (integer, optional): Default 20

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 42,
      "user_name": "John Doe",
      "score": 75,
      "marks": 100,
      "percentage": 75.0,
      "is_passed": true,
      "submitted_at": "2026-05-02T10:30:00Z"
    }
  ],
  "meta": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5
  }
}
```

---

### Question Types

#### Get Available Question Types
```
GET /api/v1/quiz-types
```

Retrieve all supported question types.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "value": "boolean",
      "label": "Boolean (True/False)",
      "description": "Single true or false answer"
    },
    {
      "value": "single_choice",
      "label": "Single Choice",
      "description": "One correct answer from multiple options"
    },
    {
      "value": "multiple_choice",
      "label": "Multiple Choice",
      "description": "Multiple correct answers with partial scoring"
    },
    {
      "value": "number",
      "label": "Number Input",
      "description": "Numeric answer with tolerance/range"
    },
    {
      "value": "text",
      "label": "Text Input",
      "description": "Free text with manual or auto-grading"
    }
  ]
}
```

---

## Error Handling

### Validation Errors
```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "title": ["The title field is required"],
    "pass_percentage": ["The pass percentage must be between 0 and 100"]
  }
}
```

### Resource Not Found
```json
{
  "success": false,
  "error": "Not found",
  "message": "Quiz not found"
}
```

### Access Denied
```json
{
  "success": false,
  "error": "Quiz not available",
  "message": "This quiz is not published"
}
```

---

## Request Examples

### Using cURL

**List Quizzes:**
```bash
curl -X GET "http://localhost/api/v1/quizzes" \
  -H "Accept: application/json"
```

**Create Quiz:**
```bash
curl -X POST "http://localhost/api/v1/admin/quizzes" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Quiz",
    "pass_percentage": 70,
    "is_published": true
  }'
```

**Start Attempt:**
```bash
curl -X POST "http://localhost/api/v1/quizzes/1/attempts" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "user_name": "John Doe",
    "user_email": "john@example.com"
  }'
```

**Submit Answers:**
```bash
curl -X POST "http://localhost/api/v1/attempts/42/submit" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "answers": {
      "1": ["2", "4"],
      "2": "3.14"
    },
    "time_spent": 1230
  }'
```

---

## Rate Limiting

Currently, there is no rate limiting. Future versions may implement rate limiting based on:
- IP address
- User account
- API key

---

## Versioning

Current API version: **v1**

The API version is specified in the URL path: `/api/v1/`

Future versions will be available at: `/api/v2/`, `/api/v3/`, etc.

---

## Changelog

### Version 1.0.0 (May 2, 2026)
- Initial release
- Quiz CRUD operations
- Question management
- Quiz attempt workflow
- Result analytics
- Health check endpoints

---

## Support

For API issues or questions, please contact support or open an issue in the project repository.
