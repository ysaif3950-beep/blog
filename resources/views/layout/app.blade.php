<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Blog App') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            /* Color System - Improved Contrast */
            --primary-600: #4f46e5;
            --primary-500: #6366f1;
            --primary-400: #818cf8;
            --primary-50: #eef2ff;
            
            --gray-900: #111827;
            --gray-800: #1f2937;
            --gray-700: #374151;
            --gray-600: #4b5563;
            --gray-500: #6b7280;
            --gray-400: #9ca3af;
            --gray-300: #d1d5db;
            --gray-200: #e5e7eb;
            --gray-100: #f3f4f6;
            --gray-50: #f9fafb;
            
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            
            /* Spacing System (8px grid) */
            --space-1: 0.25rem;  /* 4px */
            --space-2: 0.5rem;   /* 8px */
            --space-3: 0.75rem;  /* 12px */
            --space-4: 1rem;     /* 16px */
            --space-5: 1.5rem;   /* 24px */
            --space-6: 2rem;     /* 32px */
            --space-8: 3rem;     /* 48px */
            --space-10: 4rem;    /* 64px */
            
            /* Typography Scale */
            --text-xs: 0.75rem;   /* 12px */
            --text-sm: 0.875rem;  /* 14px */
            --text-base: 1rem;    /* 16px */
            --text-lg: 1.125rem;  /* 18px */
            --text-xl: 1.25rem;   /* 20px */
            --text-2xl: 1.5rem;   /* 24px */
            --text-3xl: 1.875rem; /* 30px */
            --text-4xl: 2.25rem;  /* 36px */
            
            /* Line Heights */
            --leading-tight: 1.25;
            --leading-normal: 1.5;
            --leading-relaxed: 1.75;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-full: 9999px;
        }

        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: var(--leading-normal);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Typography Utilities */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            line-height: var(--leading-tight);
            color: var(--gray-900);
        }

        /* Navigation */
        .navbar-main {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            padding: var(--space-4) 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: var(--text-xl);
            color: var(--primary-600) !important;
            letter-spacing: -0.025em;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .navbar-brand i {
            font-size: var(--text-2xl);
        }

        .nav-link {
            font-weight: 500;
            font-size: var(--text-sm);
            color: var(--gray-600);
            padding: var(--space-2) var(--space-4) !important;
            border-radius: var(--radius);
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-600);
            background-color: var(--primary-50);
        }

        .nav-link.active {
            color: var(--primary-600) !important;
            background-color: var(--primary-50);
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: var(--primary-600);
            border-radius: var(--radius-full);
        }

        /* Search */
        .search-box {
            position: relative;
            width: 280px;
        }

        .search-box input {
            width: 100%;
            padding: var(--space-2) var(--space-4);
            padding-left: var(--space-10);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px var(--primary-50);
        }

        .search-box i {
            position: absolute;
            left: var(--space-4);
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        /* Buttons */
        .btn {
            font-weight: 500;
            font-size: var(--text-sm);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius);
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
        }

        .btn-primary {
            background-color: var(--primary-600);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background-color: var(--primary-500);
            box-shadow: var(--shadow);
            transform: translateY(-1px);
            color: white;
        }

        .btn-secondary {
            background-color: white;
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .btn-secondary:hover {
            background-color: var(--gray-50);
            border-color: var(--gray-400);
        }

        .btn-sm {
            padding: var(--space-1) var(--space-3);
            font-size: var(--text-xs);
        }

        .btn-lg {
            padding: var(--space-3) var(--space-6);
            font-size: var(--text-base);
        }

        /* Avatar */
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--primary-600), var(--primary-400));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: var(--text-sm);
        }

        /* Dropdown */
        .dropdown-menu {
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-lg);
            border-radius: var(--radius-md);
            padding: var(--space-2);
            margin-top: var(--space-2);
        }

        .dropdown-item {
            padding: var(--space-2) var(--space-3);
            border-radius: var(--radius-sm);
            font-size: var(--text-sm);
            color: var(--gray-700);
            transition: all 0.15s ease;
        }

        .dropdown-item:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }

        .dropdown-item i {
            width: 20px;
            font-size: var(--text-base);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: var(--space-8) 0;
        }

        /* Cards */
        .card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--gray-300);
        }

        /* Forms */
        .form-label {
            font-weight: 500;
            font-size: var(--text-sm);
            color: var(--gray-700);
            margin-bottom: var(--space-2);
        }

        .form-control, .form-select {
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: var(--text-sm);
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px var(--primary-50);
            outline: none;
        }

        .form-text {
            font-size: var(--text-xs);
            color: var(--gray-500);
            margin-top: var(--space-1);
        }

        /* Utility Classes */
        .text-muted { color: var(--gray-600); }
        .text-primary { color: var(--primary-600); }
        .bg-primary-light { background-color: var(--primary-50); }

    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-main sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{url('/')}}">
                <i class="bi bi-layers"></i>
                <span>Blog App</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{url('/')}}">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('posts*') ? 'active' : '' }}" href="{{ url('posts')}}">
                            <i class="bi bi-file-text me-1"></i> Posts
                        </a>
                    </li>
                    @can('viewAny', \App\Models\User::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="{{ url('users')}}">
                            <i class="bi bi-people me-1"></i> Users
                        </a>
                    </li>
                    @endcan
                    @can('viewAny', \App\Models\Tag::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('tags*') ? 'active' : '' }}" href="{{ url('tags')}}">
                            <i class="bi bi-tags me-1"></i> Tags
                        </a>
                    </li>
                    @endcan
                    @endauth
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <form class="d-none d-lg-block" role="search" method="GET" action="{{ url('posts/search') }}">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="search" name="search" placeholder="Search posts..." aria-label="Search" required>
                        </div>
                    </form>

                    @guest
                    <div class="d-flex gap-2">
                        @if (Route::has('login'))
                        <a class="btn btn-secondary" href="{{ route('login') }}">Login</a>
                        @endif
                        @if (Route::has('register'))
                        <a class="btn btn-primary" href="{{ route('register') }}">Get Started</a>
                        @endif
                    </div>
                    @else
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle d-flex align-items-center gap-2 p-1 pe-3" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right text-danger"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>
