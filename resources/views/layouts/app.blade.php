<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'QuizMaster - Professional Quiz System')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Professional color palette - Teal & Slate theme */
            --primary-dark: #0f172a;
            --primary-light: #1e293b;
            --primary-medium: #334155;
            --accent-primary: #0ea5e9;
            --accent-secondary: #06b6d4;
            --accent-tertiary: #14b8a6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-light: #94a3b8;
            --border-color: #e2e8f0;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            color: var(--text-primary);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header Navigation */
        header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            padding: 1rem 0;
            box-shadow: var(--shadow-lg);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-secondary);
            text-decoration: none;
            transition: var(--transition);
        }

        .header-brand:hover {
            color: var(--accent-tertiary);
            transform: translateX(2px);
        }

        .header-brand i {
            font-size: 1.75rem;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-nav {
            display: flex;
            gap: 0.5rem;
            list-style: none;
            align-items: center;
        }

        .header-nav a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-nav a:hover,
        .header-nav a.active {
            color: var(--accent-secondary);
            background: rgba(6, 182, 212, 0.1);
        }

        /* Main Content */
        main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            flex: 1;
        }

        /* Cards */
        .card {
            background: var(--bg-primary);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-md);
            padding: 2rem;
            border: 1px solid var(--border-color);
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-title i {
            color: var(--accent-primary);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
            border-color: var(--text-secondary);
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-block {
            width: 100%;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: var(--transition);
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 2rem;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        /* Stat Box */
        .stat-box {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border-left: 4px solid var(--accent-primary);
            transition: var(--transition);
        }

        .stat-box:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-primary);
            margin: 0.5rem 0;
        }

        .stat-icon {
            font-size: 1.5rem;
            color: var(--accent-primary);
            opacity: 0.8;
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: #fef2f2;
            color: #7f1d1d;
            border-left: 4px solid var(--danger);
        }

        .alert-info {
            background: #eff6ff;
            color: #0c2d6b;
            border-left: 4px solid var(--info);
        }

        .alert-warning {
            background: #fffbeb;
            color: #78350f;
            border-left: 4px solid var(--warning);
        }

        .alert i {
            font-size: 1.25rem;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        thead {
            background: var(--bg-tertiary);
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        tbody tr:hover {
            background: var(--bg-secondary);
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-success {
            background: #ecfdf5;
            color: #065f46;
        }

        .badge-danger {
            background: #fef2f2;
            color: #7f1d1d;
        }

        .badge-info {
            background: #eff6ff;
            color: #0c2d6b;
        }

        .badge-warning {
            background: #fffbeb;
            color: #78350f;
        }

        .badge-primary {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(6, 182, 212, 0.1));
            color: var(--accent-primary);
        }

        /* Footer */
        footer {
            background: var(--primary-dark);
            color: #cbd5e1;
            text-align: center;
            padding: 2rem;
            margin-top: auto;
            border-top: 1px solid var(--border-color);
        }

        footer p {
            margin: 0;
        }

        /* Score Display */
        .score-card {
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            padding: 3rem;
            border-radius: 1rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl);
        }

        .score-label {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .score-value {
            font-size: 3rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .score-percentage {
            font-size: 2rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        /* Quiz Item */
        .quiz-item {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .quiz-item:hover {
            border-color: var(--accent-primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .quiz-item-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .quiz-item-meta {
            display: flex;
            gap: 1.5rem;
            margin: 1rem 0;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .quiz-item-meta i {
            color: var(--accent-primary);
            width: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-nav {
                gap: 0.25rem;
            }

            .header-nav a {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }

            .card {
                padding: 1.5rem;
            }

            main {
                padding: 1rem;
            }

            .grid-2,
            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .score-card {
                padding: 2rem;
            }

            .score-value {
                font-size: 2.5rem;
            }

            .quiz-item-meta {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .header-brand {
                font-size: 1.125rem;
            }

            .header-brand i {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="/" class="header-brand">
                <i class="fas fa-graduation-cap"></i>
                QuizMaster
            </a>
            <nav>
                <ul class="header-nav">
                    <li><a href="/"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="/quizzes"><i class="fas fa-book"></i> Quizzes</a></li>
                    <li><a href="/admin/quizzes"><i class="fas fa-cog"></i> Manage</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <p><i class="fas fa-copyright"></i> 2026 QuizMaster. Professional Quiz Management System.</p>
    </footer>
</body>
</html>
