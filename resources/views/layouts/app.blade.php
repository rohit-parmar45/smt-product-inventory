<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Product & Inventory Management System — Track products, manage stock, and streamline your inventory workflow.">
    <title>@yield('title', 'Inventory System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body text-white min-h-screen font-inter antialiased">
    {{-- ── Navbar ────────────────────────────────────────── --}}
    <nav class="navbar" id="mainNav">
        <div class="navbar-inner">
            <a href="/dashboard" class="navbar-brand">
                <span class="brand-icon"></span>
                <span class="brand-text">Product Inventory System</span>
            </a>

            <div class="navbar-user" id="navUser" style="display:none;">
                <span class="user-badge" id="navRole"></span>
                <span class="user-name" id="navName"></span>
                <button class="btn btn-ghost btn-sm" id="logoutBtn" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- ── Main Content ─────────────────────────────────── --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- ── Toast Container ──────────────────────────────── --}}
    <div id="toastContainer" class="toast-container"></div>

    @yield('scripts')
</body>
</html>
