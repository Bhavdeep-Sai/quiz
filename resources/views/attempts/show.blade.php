@extends('layouts.app')

@section('title', 'Take Quiz - ' . $quiz->title)

@section('content')
    <style>
        .quiz-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
        }

        .quiz-sidebar {
            position: sticky;
            top: 120px;
            height: fit-content;
        }

        .progress-card {
            background: var(--bg-primary);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .progress-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .timer {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-primary);
            font-family: 'Courier New', monospace;
        }

        .question-navigator {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .question-nav-btn {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-color);
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: var(--transition);
            color: var(--text-secondary);
        }

        .question-nav-btn:hover {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
        }

        .question-nav-btn.active {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
            border-color: transparent;
        }

        .question-container {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .question-container.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .question-number {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-primary);
        }

        .question-marks {
            background: var(--bg-tertiary);
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .question-text {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .question-image {
            max-width: 100%;
            max-height: 300px;
            margin: 1.5rem 0;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
        }

        .option-group {
            margin-bottom: 1rem;
        }

        .option-label {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-color);
            border-radius: 0.375rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .option-label:hover {
            background: var(--bg-secondary);
            border-color: var(--accent-primary);
        }

        .option-label input {
            margin-right: 0.75rem;
            cursor: pointer;
            width: 20px;
            height: 20px;
        }

        .option-label input:checked + .option-text {
            color: var(--accent-primary);
            font-weight: 600;
        }

        .option-text {
            flex: 1;
        }

        .navigation-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }

        .nav-btn {
            flex: 1;
            padding: 1rem;
        }

        @media (max-width: 1024px) {
            .quiz-container {
                grid-template-columns: 1fr;
            }

            .quiz-sidebar {
                position: static;
            }

            .progress-card {
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 2rem;
                align-items: start;
            }

            .question-navigator {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media (max-width: 768px) {
            .navigation-buttons {
                flex-direction: column;
            }

            .nav-btn {
                width: 100%;
            }
        }
    </style>

    <div class="quiz-container">
        <!-- Sidebar -->
        <div class="quiz-sidebar">
            <div class="progress-card">
                <div class="progress-header">
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 0.5rem;">
                            <i class="fas fa-stopwatch" style="margin-right: 0.5rem;"></i>
                            TIME
                        </div>
                        <div class="timer" id="timer">00:00:00</div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 0.75rem;">
                        <i class="fas fa-question" style="margin-right: 0.5rem;"></i>
                        PROGRESS
                    </div>
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--accent-secondary);">
                        <span id="currentQuestion">1</span>/<span id="totalQuestions">{{ $totalQuestions }}</span>
                    </div>
                </div>

                <label style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; display: block; margin-bottom: 1rem;">
                    <i class="fas fa-th" style="margin-right: 0.5rem;"></i>
                    QUESTIONS
                </label>
                <div class="question-navigator" id="questionList">
                    @for($i = 1; $i <= $totalQuestions; $i++)
                        <button type="button" class="question-nav-btn {{ $i === 1 ? 'active' : '' }}" data-question="{{ $i - 1 }}">
                            {{ $i }}
                        </button>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div>
            <div class="card">
                <form method="POST" action="{{ route('attempts.submit', $attempt) }}" id="quizForm">
                    @csrf
                    @method('PUT')

                    @foreach($questions as $index => $question)
                        <div class="question-container {{ $index === 0 ? 'active' : '' }}" id="question-{{ $question->id }}">
                            <div class="question-header">
                                <div>
                                    <div class="question-number">Question {{ $index + 1 }} of {{ $totalQuestions }}</div>
                                </div>
                                <div class="question-marks">
                                    <i class="fas fa-star" style="color: var(--warning); margin-right: 0.5rem;"></i>
                                    {{ $question->marks }} Marks
                                </div>
                            </div>

                            <div class="question-text">
                                {!! $question->question_text !!}
                            </div>

                            @if($question->image_url)
                                <img src="{{ $question->image_url }}" alt="Question image" class="question-image">
                            @endif

                            @if($question->type === 'boolean')
                                <div style="margin-bottom: 2rem;">
                                    <div class="option-group">
                                        <label class="option-label">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="true" required>
                                            <span class="option-text">
                                                <i class="fas fa-check-circle" style="color: var(--success); margin-right: 0.5rem;"></i>
                                                True
                                            </span>
                                        </label>
                                    </div>
                                    <div class="option-group">
                                        <label class="option-label">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="false" required>
                                            <span class="option-text">
                                                <i class="fas fa-times-circle" style="color: var(--danger); margin-right: 0.5rem;"></i>
                                                False
                                            </span>
                                        </label>
                                    </div>
                                </div>

                            @elseif($question->type === 'single_choice')
                                <div style="margin-bottom: 2rem;">
                                    @foreach($question->options as $option)
                                        <div class="option-group">
                                            <label class="option-label">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required>
                                                <span class="option-text">{{ $option->label }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif($question->type === 'multiple_choice')
                                <div style="margin-bottom: 2rem;">
                                    @foreach($question->options as $option)
                                        <div class="option-group">
                                            <label class="option-label">
                                                <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}">
                                                <span class="option-text">{{ $option->label }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif($question->type === 'number')
                                <div style="margin-bottom: 2rem;">
                                    <input type="number" name="answers[{{ $question->id }}]" placeholder="Enter your numeric answer" required style="padding: 1rem; font-size: 1rem;">
                                </div>

                            @elseif($question->type === 'text')
                                <div style="margin-bottom: 2rem;">
                                    <textarea name="answers[{{ $question->id }}]" placeholder="Enter your answer..." required style="padding: 1rem; font-size: 1rem;"></textarea>
                                </div>
                            @endif

                            <input type="hidden" id="timeSpent" name="time_spent">

                            <div class="navigation-buttons">
                                <button type="button" id="prevBtn" class="btn btn-secondary nav-btn" style="{{ $index === 0 ? 'display: none;' : '' }}">
                                    <i class="fas fa-arrow-left"></i>
                                    Previous
                                </button>
                                <button type="button" id="nextBtn" class="btn btn-primary nav-btn" style="{{ $index === $totalQuestions - 1 ? 'display: none;' : '' }}">
                                    Next
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                                <button type="submit" id="submitBtn" class="btn btn-success nav-btn" style="{{ $index === $totalQuestions - 1 ? '' : 'display: none;' }}">
                                    <i class="fas fa-check"></i>
                                    Submit Quiz
                                </button>
                            </div>
                        </div>
                    @endforeach
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentQ = 0;
        const totalQ = {{ $totalQuestions }};
        const startTime = Date.now();

        function showQuestion(n) {
            const questions = document.querySelectorAll('.question-container');
            questions.forEach(q => q.classList.remove('active'));
            
            if (n >= 0 && n < totalQ) {
                questions[n].classList.add('active');
                currentQ = n;
                
                document.getElementById('currentQuestion').textContent = n + 1;
                
                // Update navigation buttons
                document.getElementById('prevBtn').style.display = n === 0 ? 'none' : 'block';
                document.getElementById('nextBtn').style.display = n === totalQ - 1 ? 'none' : 'block';
                document.getElementById('submitBtn').style.display = n === totalQ - 1 ? 'block' : 'none';
                
                // Update question navigator
                document.querySelectorAll('.question-nav-btn').forEach((btn, i) => {
                    btn.classList.toggle('active', i === n);
                });

                // Scroll to top
                document.querySelector('.quiz-container').scrollIntoView({ behavior: 'smooth' });
            }
        }

        document.getElementById('nextBtn')?.addEventListener('click', () => {
            if (currentQ < totalQ - 1) showQuestion(currentQ + 1);
        });

        document.getElementById('prevBtn')?.addEventListener('click', () => {
            if (currentQ > 0) showQuestion(currentQ - 1);
        });

        document.querySelectorAll('.question-nav-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                showQuestion(parseInt(btn.dataset.question));
            });
        });

        // Timer
        setInterval(() => {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = elapsed % 60;
            document.getElementById('timer').textContent = 
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
        }, 1000);

        // Store time spent before submit
        document.getElementById('quizForm').addEventListener('submit', () => {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            document.getElementById('timeSpent').value = elapsed;
        });

        showQuestion(0);
    </script>
@endsection
