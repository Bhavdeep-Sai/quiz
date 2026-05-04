<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'QuizMaster')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #f5f7fb;
            --surface: rgba(255, 255, 255, 0.92);
            --surface-strong: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --brand: #0f766e;
            --brand-2: #0891b2;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #2563eb;
            --shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            --shadow-sm: 0 8px 24px rgba(15, 23, 42, 0.06);
            --radius: 22px;
            --radius-sm: 14px;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            font-family: 'Manrope', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(8, 145, 178, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 22%),
                var(--bg);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        a { color: inherit; }

        header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .shell {
            width: min(1160px, calc(100% - 2rem));
            margin: 0 auto;
        }

        .topbar {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand {
            display: inline-flex; align-items: center; gap: .75rem;
            font-weight: 800; font-size: 1.15rem; text-decoration: none; color: #fff;
            letter-spacing: -0.02em;
        }

        .brand i { color: #2dd4bf; font-size: 1.25rem; }

        .nav {
            display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; justify-content: flex-end;
            list-style: none; padding: 0; margin: 0;
        }

        .nav a {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .65rem .95rem; border-radius: 999px;
            color: #cbd5e1; text-decoration: none; font-weight: 600; font-size: .94rem;
            transition: .2s ease;
        }

        .nav a:hover, .nav a.active { background: rgba(45, 212, 191, 0.10); color: #fff; }

        main {
            width: min(1160px, calc(100% - 2rem));
            margin: 1.25rem auto 2rem;
        }

        .card, .surface {
            background: var(--surface);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(10px);
        }

        .card { padding: 1.25rem; }
        .card + .card { margin-top: 1rem; }

        .card-header {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            margin-bottom: 1rem; padding-bottom: .9rem; border-bottom: 1px solid var(--line);
        }

        .card-title {
            display: inline-flex; align-items: center; gap: .7rem;
            margin: 0; font-size: 1.1rem; font-weight: 800; letter-spacing: -0.02em;
        }

        .card-title i { color: var(--brand); }

        .grid { display: grid; gap: 1rem; }
        .grid-2 { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .grid-3 { grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); }
        .grid-4 { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            padding: .72rem 1rem; border: 1px solid transparent; border-radius: 999px;
            font: inherit; font-weight: 700; text-decoration: none; cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, var(--brand), var(--brand-2)); color: #fff; box-shadow: var(--shadow-sm); }
        .btn-secondary { background: #fff; color: var(--text); border-color: var(--line); }
        .btn-success { background: var(--success); color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-small { padding: .55rem .85rem; font-size: .9rem; }
        .btn-block { width: 100%; }

        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .45rem; font-size: .92rem; font-weight: 700; color: var(--text); }
        input[type="text"], input[type="email"], input[type="number"], input[type="password"], input[type="url"], textarea, select {
            width: 100%; padding: .85rem .95rem; border-radius: 14px; border: 1px solid var(--line);
            background: #fff; color: var(--text); font: inherit; outline: none; transition: .18s ease;
        }
        input:focus, textarea:focus, select:focus { border-color: rgba(8, 145, 178, 0.5); box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.12); }
        textarea { min-height: 110px; resize: vertical; }

        .alert {
            display: flex; gap: .75rem; align-items: flex-start; padding: .95rem 1rem; border-radius: 16px; margin-bottom: 1rem; border: 1px solid transparent;
        }
        .alert-success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
        .alert-danger { background: #fef2f2; color: #7f1d1d; border-color: #fecaca; }
        .alert-info { background: #eff6ff; color: #1e3a8a; border-color: #bfdbfe; }
        .alert-warning { background: #fffbeb; color: #78350f; border-color: #fde68a; }

        .badge { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .7rem; border-radius: 999px; font-size: .82rem; font-weight: 800; }
        .badge-primary { background: rgba(8,145,178,.12); color: var(--brand-2); }
        .badge-success { background: rgba(5,150,105,.12); color: var(--success); }
        .badge-danger { background: rgba(220,38,38,.12); color: var(--danger); }
        .badge-info { background: rgba(37,99,235,.12); color: var(--info); }
        .badge-warning { background: rgba(217,119,6,.12); color: var(--warning); }

        .stat-box {
            padding: 1rem 1.1rem; border-radius: 18px; background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,250,252,.96));
            border: 1px solid var(--line); box-shadow: var(--shadow-sm);
        }
        .stat-label { font-size: .78rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; }
        .stat-value { margin-top: .35rem; font-size: 1.8rem; font-weight: 800; letter-spacing: -0.03em; }
        .stat-icon { color: var(--brand-2); margin-right: .4rem; }

        .quiz-item {
            display: block; text-decoration: none; color: inherit; padding: 1rem; border-radius: 18px; background: var(--surface-strong);
            border: 1px solid var(--line); box-shadow: var(--shadow-sm); transition: .18s ease;
        }
        .quiz-item:hover { transform: translateY(-2px); box-shadow: var(--shadow); border-color: rgba(8,145,178,.25); }
        .quiz-item-title { font-size: 1rem; font-weight: 800; margin-bottom: .4rem; display: flex; align-items: center; gap: .55rem; }
        .quiz-item-meta { display: flex; flex-wrap: wrap; gap: .85rem; color: var(--muted); font-size: .85rem; margin-top: .9rem; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .8rem .75rem; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        thead th { font-size: .8rem; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
        tbody tr:hover { background: rgba(248,250,252,.9); }

        footer {
            width: 100%; padding: 1.1rem 0 1.4rem; color: var(--muted); text-align: center; font-size: .9rem;
        }

        .score-card {
            background: linear-gradient(135deg, rgba(15,118,110,.96), rgba(8,145,178,.96));
            color: #fff; padding: 1.5rem; border-radius: 24px; box-shadow: var(--shadow);
        }

        .score-label { font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; opacity: .86; }
        .score-value { font-size: 2.1rem; font-weight: 800; letter-spacing: -0.03em; }
        .score-percentage { font-size: 1.25rem; font-weight: 700; opacity: .95; }

        @media (max-width: 768px) {
            .shell, main { width: min(100% - 1rem, 1160px); }
            .topbar { min-height: 64px; flex-direction: column; align-items: flex-start; padding: .75rem 0; }
            .nav { justify-content: flex-start; }
            .card { padding: 1rem; border-radius: 18px; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <header>
        <div class="shell topbar">
            <a href="/" class="brand"><i class="fas fa-graduation-cap"></i><span>QuizMaster</span></a>
            <nav aria-label="Primary">
                <ul class="nav">
                    <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}"><i class="fas fa-house"></i>Home</a></li>
                    <li><a href="/quizzes" class="{{ request()->is('quizzes*') ? 'active' : '' }}"><i class="fas fa-book"></i>Quizzes</a></li>
                    <li><a href="/admin/quizzes" class="{{ request()->is('admin/quizzes*') ? 'active' : '' }}"><i class="fas fa-screwdriver-wrench"></i>Manage</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-triangle-exclamation"></i>
                <div>
                    <strong>Please fix the following errors</strong>
                    <ul style="margin: .45rem 0 0 1rem; padding-left: 1rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-circle-check"></i><div>{{ session('success') }}</div></div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i><div>{{ session('error') }}</div></div>
        @endif

        @yield('content')
    </main>

    <footer>
        <div class="shell">© 2026 QuizMaster</div>
    </footer>
</body>
</html>