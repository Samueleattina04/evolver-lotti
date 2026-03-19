<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Esolver') — Ricerca Lotti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #0f1117;
            --bg2:     #181c27;
            --bg3:     #1e2235;
            --border:  #2a2f45;
            --accent:  #3b82f6;
            --accent2: #60a5fa;
            --green:   #22c55e;
            --amber:   #f59e0b;
            --red:     #ef4444;
            --text:    #e2e8f0;
            --text2:   #94a3b8;
            --text3:   #475569;
            --mono:    'IBM Plex Mono', monospace;
            --sans:    'IBM Plex Sans', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            min-height: 100vh;
        }

        /* ── HEADER ── */
        header {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-badge {
            background: var(--accent);
            color: #fff;
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 3px;
            letter-spacing: 0.05em;
        }

        .logo-title {
            font-size: 13px;
            font-weight: 500;
            color: var(--text2);
            letter-spacing: 0.02em;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .header-meta {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text3);
        }

        .status-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            margin-right: 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.35; }
        }

        /* ── HAMBURGER ── */
        .hamburger {
            background: none;
            border: 1px solid var(--border);
            border-radius: 6px;
            width: 36px;
            height: 36px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            padding: 6px;
            transition: background 0.15s, border-color 0.15s;
        }

        .hamburger:hover {
            background: var(--bg3);
            border-color: var(--accent);
        }

        .hamburger span {
            display: block;
            width: 18px;
            height: 2px;
            background: var(--text2);
            border-radius: 2px;
            transition: background 0.15s;
        }

        .hamburger:hover span { background: var(--text); }

        /* ── SIDEBAR OVERLAY ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 200;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.visible { display: block; }

        /* ── SIDEBAR PANEL ── */
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: 280px;
            height: 100vh;
            background: var(--bg2);
            border-left: 1px solid var(--border);
            z-index: 300;
            transform: translateX(100%);
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar.open { transform: translateX(0); }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            height: 56px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .sidebar-header-title {
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .sidebar-close {
            background: none;
            border: none;
            color: var(--text3);
            font-size: 18px;
            cursor: pointer;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.15s, color 0.15s;
        }

        .sidebar-close:hover {
            background: var(--bg3);
            color: var(--text);
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-section-label {
            font-family: var(--mono);
            font-size: 10px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.75rem 1.25rem 0.35rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 1.25rem;
            text-decoration: none;
            color: var(--text2);
            font-size: 13.5px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: background 0.12s, color 0.12s, border-color 0.12s;
        }

        .nav-link:hover {
            background: var(--bg3);
            color: var(--text);
        }

        .nav-link.active {
            color: var(--accent2);
            background: rgba(59,130,246,0.08);
            border-left-color: var(--accent);
        }

        .nav-link svg {
            flex-shrink: 0;
            opacity: 0.7;
        }

        .nav-link.active svg { opacity: 1; }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border);
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text3);
            flex-shrink: 0;
        }

        /* ── MAIN ── */
        main {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2.25rem 1.75rem;
        }

        /* ── BREADCRUMB / PAGE TITLE ── */
        .page-header {
            margin-bottom: 1.75rem;
        }

        .page-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
        }

        .page-subtitle {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text3);
            margin-top: 4px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text3);
            text-decoration: none;
            margin-bottom: 1.25rem;
            transition: color 0.15s;
        }

        .back-link:hover { color: var(--accent2); }

        /* ── FORMS ── */
        .search-label {
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 500;
            color: var(--text3);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 10px;
            display: block;
        }

        .search-box {
            display: flex;
            gap: 8px;
            align-items: stretch;
        }

        .search-input-wrap {
            flex: 1;
            position: relative;
        }

        .search-input-wrap .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text3);
            pointer-events: none;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 12px 14px 12px 44px;
            font-family: var(--mono);
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        input[type="date"] { padding-left: 14px; }

        input[type="text"]:focus,
        input[type="date"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }

        input[type="text"]::placeholder { color: var(--text3); }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 6px;
            padding: 0 20px;
            font-family: var(--sans);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.15s, transform 0.1s, opacity 0.15s;
            height: 44px;
        }

        .btn:active { transform: scale(0.98); }

        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: #2563eb; }

        .btn-ghost {
            background: transparent;
            color: var(--text3);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            background: var(--bg3);
            color: var(--text);
            border-color: var(--text3);
        }

        .btn-success {
            background: rgba(34,197,94,0.15);
            color: var(--green);
            border: 1px solid rgba(34,197,94,0.3);
        }

        .btn-success:hover { background: rgba(34,197,94,0.25); }

        .btn-amber {
            background: rgba(245,158,11,0.12);
            color: var(--amber);
            border: 1px solid rgba(245,158,11,0.25);
        }

        .btn-amber:hover { background: rgba(245,158,11,0.22); }

        /* ── FILTER PANEL ── */
        .filter-panel {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 0.75rem;
            display: none;
        }

        .filter-panel.open { display: block; }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .filter-group label {
            display: block;
            font-family: var(--mono);
            font-size: 10px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .filter-group input[type="text"],
        .filter-group input[type="date"] {
            padding: 8px 12px;
            font-size: 12px;
        }

        .filter-check {
            display: flex;
            align-items: flex-end;
            padding-bottom: 2px;
        }

        .filter-check label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text2);
            text-transform: none;
            letter-spacing: 0;
            font-weight: 400;
            margin-bottom: 0;
        }

        input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 0.875rem;
            padding-top: 0.875rem;
            border-top: 1px solid var(--border);
        }

        /* ── RESULTS HEADER ── */
        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .results-count {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text2);
        }

        .results-count strong {
            color: var(--accent2);
            font-size: 15px;
        }

        .results-query {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text3);
        }

        .results-query span { color: var(--amber); }

        .results-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── TABLE ── */
        .table-wrap {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead {
            background: var(--bg3);
        }

        th {
            padding: 10px 14px;
            text-align: left;
            font-family: var(--mono);
            font-size: 10px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        th.sortable a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--text3);
            text-decoration: none;
            transition: color 0.12s;
        }

        th.sortable a:hover { color: var(--text2); }
        th.sortable.active a { color: var(--accent2); }

        .sort-icon { font-size: 11px; }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg3); }

        td {
            padding: 11px 14px;
            color: var(--text);
            vertical-align: middle;
        }

        .td-mono {
            font-family: var(--mono);
            font-size: 12px;
        }

        .td-dim { color: var(--text2); }

        /* ── BADGES ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: var(--mono);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .badge-lotto {
            background: rgba(59,130,246,0.14);
            color: var(--accent2);
            border: 1px solid rgba(59,130,246,0.25);
        }

        .badge-articolo {
            background: rgba(245,158,11,0.12);
            color: var(--amber);
            border: 1px solid rgba(245,158,11,0.25);
        }

        .badge-green {
            background: rgba(34,197,94,0.12);
            color: var(--green);
            border: 1px solid rgba(34,197,94,0.25);
        }

        .badge-gray {
            background: rgba(71,85,105,0.3);
            color: var(--text3);
            border: 1px solid var(--border);
        }

        .lotto-code {
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 600;
            color: var(--accent2);
            background: rgba(59,130,246,0.08);
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid rgba(59,130,246,0.2);
            text-decoration: none;
            transition: background 0.12s;
        }

        a.lotto-code:hover { background: rgba(59,130,246,0.18); }

        .no-lotto {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text3);
        }

        .qty {
            font-family: var(--mono);
            font-weight: 600;
            font-size: 12px;
        }

        .qty-positive { color: var(--green); }
        .qty-zero     { color: var(--text3); }
        .qty-negative { color: var(--red); }

        /* ── ARTICLE LINK ── */
        .art-link {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.12s;
        }

        .art-link:hover { color: var(--accent2); text-decoration: underline; }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
        }

        .empty-icon {
            font-size: 42px;
            margin-bottom: 1.25rem;
            opacity: 0.25;
        }

        .empty-title {
            font-size: 17px;
            font-weight: 500;
            color: var(--text2);
            margin-bottom: 8px;
        }

        .empty-sub {
            font-size: 13px;
            color: var(--text3);
            font-family: var(--mono);
        }

        /* ── ALERT ── */
        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 6px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 13px;
            margin-bottom: 1.5rem;
            font-family: var(--mono);
        }

        /* ── DETAIL SECTIONS ── */
        .section-title {
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        .detail-section { margin-bottom: 2rem; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 16px;
        }

        .stat-label {
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        .stat-value {
            font-family: var(--mono);
            font-size: 20px;
            font-weight: 600;
            color: var(--text);
        }

        .stat-value.accent { color: var(--accent2); }
        .stat-value.green  { color: var(--green); }
        .stat-value.amber  { color: var(--amber); }

        /* ── PAGINATION ── */
        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            justify-content: center;
            padding: 1.25rem 0 0;
            flex-wrap: wrap;
        }

        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 8px;
            border-radius: 5px;
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 500;
            color: var(--text2);
            background: var(--bg2);
            border: 1px solid var(--border);
            text-decoration: none;
            transition: background 0.12s, color 0.12s, border-color 0.12s;
        }

        .page-btn:hover:not(.disabled):not(.active) {
            background: var(--bg3);
            color: var(--text);
            border-color: var(--text3);
        }

        .page-btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .page-btn.disabled {
            opacity: 0.3;
            cursor: default;
        }

        .page-info {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text3);
            margin-left: 8px;
        }

        /* ── SEARCH HINT ── */
        .search-hint {
            margin-top: 6px;
            font-size: 11px;
            color: var(--text3);
            font-family: var(--mono);
        }

        .filter-indicator {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--amber);
            border-radius: 50%;
            margin-left: 4px;
            vertical-align: middle;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            main { padding: 1.25rem 1rem; }
            header { padding: 0 1rem; }
            .logo-title { display: none; }
            .header-meta { display: none; }
            .search-box { flex-direction: column; }
            .btn { height: 42px; }
            .filter-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
    @yield('head')
</head>
<body>

<header>
    <a href="{{ route('lotti.index') }}" class="logo">
        <span class="logo-badge">ESOLVER</span>
        <span class="logo-title">@yield('page-title', 'Ricerca Lotti & Articoli')</span>
    </a>
    <div class="header-right">
        <div class="header-meta">
            <span class="status-dot"></span>DB LIVE · {{ config('database.connections.sqlsrv.host', '192.168.3.210') }}
        </div>
        <button class="hamburger" id="menu-btn" aria-label="Apri menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="sidebar-header-title">Navigazione</span>
        <button class="sidebar-close" id="sidebar-close" aria-label="Chiudi menu">✕</button>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Ricerca</div>
        <a href="{{ route('lotti.index') }}"
           class="nav-link {{ request()->routeIs('lotti.index') || request()->routeIs('lotti.cerca') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            Ricerca Lotti / Articoli
        </a>

        <div class="nav-section-label" style="margin-top:.5rem">Catalogo</div>
        <a href="{{ route('articoli.index') }}"
           class="nav-link {{ request()->routeIs('articoli.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            Tutti gli Articoli
        </a>
        <a href="{{ route('lotti.esplora') }}"
           class="nav-link {{ request()->routeIs('lotti.esplora') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            Esplora Lotti
        </a>

        <div class="nav-section-label" style="margin-top:.5rem">Filtri</div>
        <a href="{{ route('lotti.senza') }}"
           class="nav-link {{ request()->routeIs('lotti.senza') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Articoli Senza Lotto
        </a>
    </nav>
    <div class="sidebar-footer">
        {{ config('database.connections.sqlsrv.host', 'SERVER2019\SISTEMI') }} · {{ config('database.connections.sqlsrv.database', 'ESOLVER') }}
    </div>
</div>

<main>
    @yield('content')
</main>

<script>
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const closeBtn = document.getElementById('sidebar-close');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('visible');
        document.body.style.overflow = '';
    }

    menuBtn.addEventListener('click', openSidebar);
    overlay.addEventListener('click', closeSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
</script>

@yield('scripts')
</body>
</html>
