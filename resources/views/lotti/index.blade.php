<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ricerca Lotti — Esolver</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0f1117;
            --bg2:       #181c27;
            --bg3:       #1e2235;
            --border:    #2a2f45;
            --accent:    #3b82f6;
            --accent2:   #60a5fa;
            --green:     #22c55e;
            --amber:     #f59e0b;
            --red:       #ef4444;
            --text:      #e2e8f0;
            --text2:     #94a3b8;
            --text3:     #475569;
            --mono:      'IBM Plex Mono', monospace;
            --sans:      'IBM Plex Sans', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            min-height: 100vh;
            padding: 0;
        }

        /* ── HEADER ── */
        header {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
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
            font-size: 14px;
            font-weight: 500;
            color: var(--text2);
            letter-spacing: 0.03em;
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
            50% { opacity: 0.4; }
        }

        /* ── MAIN ── */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        /* ── SEARCH AREA ── */
        .search-section {
            margin-bottom: 2.5rem;
        }

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
            gap: 10px;
            align-items: stretch;
        }

        .search-input-wrap {
            flex: 1;
            position: relative;
        }

        .search-input-wrap svg {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text3);
            pointer-events: none;
        }

        input[type="text"] {
            width: 100%;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px 16px 14px 46px;
            font-family: var(--mono);
            font-size: 15px;
            color: var(--text);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        input[type="text"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        input[type="text"]::placeholder {
            color: var(--text3);
        }

        button[type="submit"] {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0 28px;
            font-family: var(--sans);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            white-space: nowrap;
        }

        button[type="submit"]:hover { background: #2563eb; }
        button[type="submit"]:active { transform: scale(0.98); }

        .search-hint {
            margin-top: 8px;
            font-size: 12px;
            color: var(--text3);
            font-family: var(--mono);
        }

        /* ── RISULTATI HEADER ── */
        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .results-count {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text2);
        }

        .results-count strong {
            color: var(--accent2);
            font-size: 14px;
        }

        .results-query {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text3);
        }

        .results-query span {
            color: var(--amber);
        }

        /* ── TABELLA ── */
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
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            padding: 11px 14px;
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

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg3); }

        td {
            padding: 12px 14px;
            color: var(--text);
            vertical-align: middle;
        }

        .td-mono {
            font-family: var(--mono);
            font-size: 12px;
        }

        /* ── BADGES ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 4px;
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 500;
        }

        .badge-lotto {
            background: rgba(59, 130, 246, 0.15);
            color: var(--accent2);
            border: 1px solid rgba(59, 130, 246, 0.25);
        }

        .badge-articolo {
            background: rgba(245, 158, 11, 0.12);
            color: var(--amber);
            border: 1px solid rgba(245, 158, 11, 0.25);
        }

        .lotto-code {
            font-family: var(--mono);
            font-size: 13px;
            font-weight: 600;
            color: var(--accent2);
            background: rgba(59, 130, 246, 0.08);
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .no-lotto {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text3);
        }

        .qty {
            font-family: var(--mono);
            font-weight: 600;
        }

        .qty-positive { color: var(--green); }
        .qty-zero { color: var(--text3); }
        .qty-negative { color: var(--red); }

        /* ── EMPTY / STATO INIZIALE ── */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 500;
            color: var(--text2);
            margin-bottom: 8px;
        }

        .empty-sub {
            font-size: 13px;
            color: var(--text3);
            font-family: var(--mono);
        }

        /* ── ERRORE VALIDAZIONE ── */
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 13px;
            margin-bottom: 1.5rem;
            font-family: var(--mono);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            main { padding: 1.5rem 1rem; }
            header { padding: 0 1rem; }
            .search-box { flex-direction: column; }
            button[type="submit"] { padding: 14px; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <span class="logo-badge">ESOLVER</span>
        <span class="logo-title">Ricerca Lotti &amp; Articoli</span>
    </div>
    <div class="header-meta">
        <span class="status-dot"></span>DB LIVE · 192.168.3.210
    </div>
</header>

<main>

    {{-- Errori validazione --}}
    @if ($errors->any())
        <div class="alert-error">
            ⚠ {{ $errors->first() }}
        </div>
    @endif

    {{-- FORM DI RICERCA --}}
    <div class="search-section">
        <span class="search-label">// ricerca lotti / articoli</span>
        <form action="{{ route('lotti.cerca') }}" method="GET" autocomplete="off">
            <div class="search-box">
                <div class="search-input-wrap">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Inserisci codice lotto, codice articolo o variante..."
                        autofocus
                    >
                </div>
                <button type="submit">Cerca</button>
            </div>
            <p class="search-hint">// ricerca su: codice lotto · codice articolo · variante articolo</p>
        </form>
    </div>

    {{-- RISULTATI --}}
    @if ($cercato)
        <div class="results-header">
            <div class="results-count">
                <strong>{{ $risultati->count() }}</strong> risultati trovati
            </div>
            @if ($query)
                <div class="results-query">query: <span>"{{ $query }}"</span></div>
            @endif
        </div>

        @if ($risultati->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">◎</div>
                <div class="empty-title">Nessun risultato</div>
                <div class="empty-sub">Nessun lotto o articolo trovato per "{{ $query }}"</div>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Cod. Articolo</th>
                            <th>Variante</th>
                            <th>Lotto</th>
                            <th>Data Lotto</th>
                            <th>Magazzino</th>
                            <th>Area Mag.</th>
                            <th>Giacenza UM1</th>
                            <th>Giacenza UM2</th>
                            <th>Ult. Aggiorn.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($risultati as $row)
                        <tr>
                            <td>
                                @if ($row->tipo === 'lotto')
                                    <span class="badge badge-lotto">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="2" width="20" height="20" rx="3"/></svg>
                                        LOTTO
                                    </span>
                                @else
                                    <span class="badge badge-articolo">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
                                        ARTICOLO
                                    </span>
                                @endif
                            </td>
                            <td class="td-mono">{{ $row->cod_articolo ?? '—' }}</td>
                            <td class="td-mono">{{ $row->variante ?? '—' }}</td>
                            <td>
                                @if ($row->lotto_completo)
                                    <span class="lotto-code">{{ $row->lotto_completo }}</span>
                                @else
                                    <span class="no-lotto">— nessun lotto —</span>
                                @endif
                            </td>
                            <td class="td-mono">
                                {{ $row->lotto_data ? \Carbon\Carbon::parse($row->lotto_data)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="td-mono">{{ $row->cod_magazzino ?? '—' }}</td>
                            <td class="td-mono">{{ $row->area_magazzino ?? '—' }}</td>
                            <td>
                                @php $q1 = floatval($row->giacenza_um1 ?? 0); @endphp
                                <span class="qty {{ $q1 > 0 ? 'qty-positive' : ($q1 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                    {{ number_format($q1, 3, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                @php $q2 = floatval($row->giacenza_um2 ?? 0); @endphp
                                <span class="qty {{ $q2 > 0 ? 'qty-positive' : ($q2 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                    {{ number_format($q2, 3, ',', '.') }}
                                </span>
                            </td>
                            <td class="td-mono" style="color: var(--text3); font-size: 11px;">
                                {{ $row->ultimo_aggiornamento ? \Carbon\Carbon::parse($row->ultimo_aggiornamento)->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @else
        {{-- Stato iniziale --}}
        <div class="empty-state">
            <div class="empty-icon">⬡</div>
            <div class="empty-title">Ricerca lotti Esolver</div>
            <div class="empty-sub">Inserisci un codice lotto o articolo per iniziare la ricerca</div>
        </div>
    @endif

</main>

</body>
</html>
