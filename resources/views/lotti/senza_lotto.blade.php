@extends('layouts.app')

@section('title', 'Articoli Senza Lotto')
@section('page-title', 'Articoli Senza Lotto')

@section('content')

<div class="page-header">
    <div class="page-title">Articoli Senza Lotto</div>
    <div class="page-subtitle">// articoli presenti in MagProgrArticoli senza corrispondenza in MagProgrLotto</div>
</div>

{{-- FORM FILTRI --}}
<div style="margin-bottom:1.75rem">
    <form action="{{ route('lotti.senza') }}" method="GET" autocomplete="off">
        <div class="search-box">
            <div class="search-input-wrap">
                <svg class="search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="q" value="{{ $query }}"
                       placeholder="Cerca codice articolo o variante..." autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Cerca</button>
            <button type="button" class="btn btn-ghost" id="toggle-filtri" title="Filtri">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/>
                    <line x1="11" y1="18" x2="13" y2="18"/>
                </svg>
                Filtri@if($mag || $soloGiacenza)<span class="filter-indicator"></span>@endif
            </button>
            @if ($query || $mag || $soloGiacenza)
                <a href="{{ route('lotti.senza') }}" class="btn btn-ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Reset
                </a>
            @endif
        </div>

        <div class="filter-panel {{ ($mag || $soloGiacenza) ? 'open' : '' }}" id="filter-panel">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Magazzino</label>
                    <input type="text" name="mag" value="{{ $mag }}" placeholder="es. MAIN">
                </div>
                <div class="filter-group filter-check">
                    <label>
                        <input type="checkbox" name="solo_giacenza" value="1" {{ $soloGiacenza ? 'checked' : '' }}>
                        Solo giacenze &gt; 0
                    </label>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary" style="height:36px;font-size:12px;padding:0 16px;">Applica</button>
            </div>
        </div>
    </form>
</div>

{{-- RISULTATI --}}
@if ($articoli->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">✓</div>
        <div class="empty-title">Nessun articolo senza lotto</div>
        <div class="empty-sub">
            {{ $query || $mag || $soloGiacenza
                ? 'Nessun articolo corrisponde ai criteri di ricerca.'
                : 'Tutti gli articoli hanno almeno un lotto associato.' }}
        </div>
    </div>
@else
    <div class="results-header">
        <div class="results-count">
            <strong>{{ $articoli->total() }}</strong> articoli senza lotto
            @if ($query)<span class="results-query" style="margin-left:8px">per <span>"{{ $query }}"</span></span>@endif
        </div>
        @if ($mag || $soloGiacenza)
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                @if ($mag)
                    <span class="badge badge-amber">mag: {{ $mag }}</span>
                @endif
                @if ($soloGiacenza)
                    <span class="badge badge-green">giacenza &gt; 0</span>
                @endif
            </div>
        @endif
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Cod. Articolo</th>
                    <th>Variante</th>
                    <th>Magazzino</th>
                    <th>Area</th>
                    <th>Giacenza UM1</th>
                    <th>Giacenza UM2</th>
                    <th>Ult. Aggiorn.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($articoli as $a)
                <tr>
                    <td>
                        <a href="{{ route('articoli.dettaglio', ['cod' => $a->cod_articolo, 'var' => $a->variante]) }}"
                           class="art-link" style="font-weight:600">{{ $a->cod_articolo }}</a>
                    </td>
                    <td class="td-mono td-dim">{{ $a->variante }}</td>
                    <td class="td-mono">{{ $a->cod_magazzino ?? '—' }}</td>
                    <td class="td-mono td-dim">{{ $a->area_magazzino ?? '—' }}</td>
                    <td>
                        @php $q1 = floatval($a->giacenza_um1 ?? 0); @endphp
                        <span class="qty {{ $q1 > 0 ? 'qty-positive' : ($q1 < 0 ? 'qty-negative' : 'qty-zero') }}">
                            {{ number_format($q1, 3, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        @php $q2 = floatval($a->giacenza_um2 ?? 0); @endphp
                        <span class="qty {{ $q2 > 0 ? 'qty-positive' : ($q2 < 0 ? 'qty-negative' : 'qty-zero') }}">
                            {{ number_format($q2, 3, ',', '.') }}
                        </span>
                    </td>
                    <td class="td-mono" style="color:var(--text3);font-size:11px">
                        {{ $a->ultimo_aggiornamento ? \Carbon\Carbon::parse($a->ultimo_aggiornamento)->format('d/m/Y H:i') : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $articoli])
@endif

@endsection

@section('scripts')
<script>
    const toggleBtn   = document.getElementById('toggle-filtri');
    const filterPanel = document.getElementById('filter-panel');
    if (toggleBtn && filterPanel) {
        toggleBtn.addEventListener('click', () => filterPanel.classList.toggle('open'));
    }
</script>
@endsection
