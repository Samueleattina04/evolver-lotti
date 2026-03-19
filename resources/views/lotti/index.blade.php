@extends('layouts.app')

@section('title', 'Ricerca')
@section('page-title', 'Ricerca Lotti & Articoli')

@section('content')

@if ($errors->any())
    <div class="alert-error">⚠ {{ $errors->first() }}</div>
@endif

{{-- FORM RICERCA --}}
<div style="margin-bottom: 2rem;">
    <span class="search-label">// ricerca lotti / articoli</span>
    <form action="{{ route('lotti.cerca') }}" method="GET" autocomplete="off" id="search-form">
        <div class="search-box">
            <div class="search-input-wrap">
                <svg class="search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    name="q"
                    id="q"
                    value="{{ $query }}"
                    placeholder="Inserisci codice lotto, codice articolo o variante..."
                    autofocus
                >
            </div>
            <button type="submit" class="btn btn-primary">Cerca</button>
            @if ($cercato)
                <a href="{{ route('lotti.index') }}" class="btn btn-ghost" title="Nuova ricerca">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Reset
                </a>
            @endif
            <button type="button" class="btn btn-ghost" id="toggle-filtri" title="Filtri avanzati">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/>
                    <line x1="11" y1="18" x2="13" y2="18"/>
                </svg>
                Filtri
                @if($mag || $soloGiacenza || $dataDa || $dataA)
                    <span class="filter-indicator"></span>
                @endif
            </button>
        </div>

        {{-- PANNELLO FILTRI --}}
        <div class="filter-panel {{ ($mag || $soloGiacenza || $dataDa || $dataA) ? 'open' : '' }}" id="filter-panel">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Magazzino</label>
                    <input type="text" name="mag" value="{{ $mag }}" placeholder="es. MAIN">
                </div>
                <div class="filter-group">
                    <label>Data lotto da</label>
                    <input type="date" name="data_da" value="{{ $dataDa }}">
                </div>
                <div class="filter-group">
                    <label>Data lotto a</label>
                    <input type="date" name="data_a" value="{{ $dataA }}">
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
                @if($mag || $soloGiacenza || $dataDa || $dataA)
                    <a href="{{ route('lotti.cerca', ['q' => $query]) }}" class="btn btn-ghost" style="height:36px;font-size:12px;padding:0 14px;">
                        Rimuovi filtri
                    </a>
                @endif
            </div>
        </div>

        {{-- Parametri sort nascosti --}}
        @if($cercato)
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
        @endif
    </form>
    <p class="search-hint">// ricerca su: codice lotto · codice articolo · variante</p>
</div>

{{-- RISULTATI --}}
@if ($cercato)
    <div class="results-header">
        <div>
            <div class="results-count">
                <strong>{{ $risultati->total() }}</strong> risultati
                @if ($query)<span class="results-query" style="margin-left:8px">per <span>"{{ $query }}"</span></span>@endif
            </div>
        </div>
        <div class="results-actions">
            @if ($risultati->total() > 0)
                <a href="{{ route('lotti.export', array_merge(['q' => $query], array_filter(['mag' => $mag, 'solo_giacenza' => $soloGiacenza ? 1 : null, 'data_da' => $dataDa, 'data_a' => $dataA]))) }}"
                   class="btn btn-success" style="height:34px;font-size:12px;padding:0 14px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    CSV
                </a>
            @endif
        </div>
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
                        @php
                            function sortUrl($col, $currentSort, $currentDir, $request) {
                                $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
                                return route('lotti.cerca', array_merge($request->except(['sort','dir','page']), ['sort' => $col, 'dir' => $newDir]));
                            }
                        @endphp
                        <th class="sortable {{ $sort === 'cod_articolo' ? 'active' : '' }}">
                            <a href="{{ sortUrl('cod_articolo', $sort, $dir, request()) }}">
                                Cod. Articolo
                                @if($sort === 'cod_articolo')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th class="sortable {{ $sort === 'variante' ? 'active' : '' }}">
                            <a href="{{ sortUrl('variante', $sort, $dir, request()) }}">
                                Variante
                                @if($sort === 'variante')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th class="sortable {{ $sort === 'lotto_completo' ? 'active' : '' }}">
                            <a href="{{ sortUrl('lotto_completo', $sort, $dir, request()) }}">
                                Lotto
                                @if($sort === 'lotto_completo')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th class="sortable {{ $sort === 'lotto_data' ? 'active' : '' }}">
                            <a href="{{ sortUrl('lotto_data', $sort, $dir, request()) }}">
                                Data Lotto
                                @if($sort === 'lotto_data')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th class="sortable {{ $sort === 'cod_magazzino' ? 'active' : '' }}">
                            <a href="{{ sortUrl('cod_magazzino', $sort, $dir, request()) }}">
                                Magazzino
                                @if($sort === 'cod_magazzino')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th class="sortable {{ $sort === 'area_magazzino' ? 'active' : '' }}">
                            <a href="{{ sortUrl('area_magazzino', $sort, $dir, request()) }}">
                                Area
                                @if($sort === 'area_magazzino')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th class="sortable {{ $sort === 'giacenza_um1' ? 'active' : '' }}">
                            <a href="{{ sortUrl('giacenza_um1', $sort, $dir, request()) }}">
                                Giacenza UM1
                                @if($sort === 'giacenza_um1')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th class="sortable {{ $sort === 'giacenza_um2' ? 'active' : '' }}">
                            <a href="{{ sortUrl('giacenza_um2', $sort, $dir, request()) }}">
                                Giacenza UM2
                                @if($sort === 'giacenza_um2')<span class="sort-icon">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                        <th>Ult. Aggiorn.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($risultati as $row)
                    <tr>
                        <td>
                            @if ($row->tipo === 'lotto')
                                <span class="badge badge-lotto">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="2" width="20" height="20" rx="3"/></svg>
                                    LOTTO
                                </span>
                            @else
                                <span class="badge badge-articolo">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
                                    ART
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('articoli.dettaglio', ['cod' => $row->cod_articolo, 'var' => $row->variante]) }}"
                               class="art-link">{{ $row->cod_articolo ?? '—' }}</a>
                        </td>
                        <td class="td-mono td-dim">{{ $row->variante ?? '—' }}</td>
                        <td>
                            @if ($row->lotto_completo)
                                <a href="{{ route('lotti.dettaglio', ['alfab' => $row->lotto_alfab, 'data' => $row->lotto_data ? \Carbon\Carbon::parse($row->lotto_data)->format('Y-m-d') : '', 'num' => $row->lotto_num ?? 0]) }}"
                                   class="lotto-code">{{ $row->lotto_completo }}</a>
                            @else
                                <span class="no-lotto">— nessun lotto —</span>
                            @endif
                        </td>
                        <td class="td-mono td-dim">
                            {{ $row->lotto_data ? \Carbon\Carbon::parse($row->lotto_data)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="td-mono">{{ $row->cod_magazzino ?? '—' }}</td>
                        <td class="td-mono td-dim">{{ $row->area_magazzino ?? '—' }}</td>
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
                        <td class="td-mono" style="color:var(--text3);font-size:11px">
                            {{ $row->ultimo_aggiornamento ? \Carbon\Carbon::parse($row->ultimo_aggiornamento)->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $risultati])
    @endif

@else
    <div class="empty-state">
        <div class="empty-icon">⬡</div>
        <div class="empty-title">Ricerca lotti Esolver</div>
        <div class="empty-sub">Inserisci un codice lotto o articolo per iniziare la ricerca</div>
    </div>
@endif

@endsection

@section('scripts')
<script>
    const toggleBtn  = document.getElementById('toggle-filtri');
    const filterPanel = document.getElementById('filter-panel');
    if (toggleBtn && filterPanel) {
        toggleBtn.addEventListener('click', () => {
            filterPanel.classList.toggle('open');
        });
    }
</script>
@endsection
