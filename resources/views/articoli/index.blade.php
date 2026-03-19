@extends('layouts.app')

@section('title', 'Articoli')
@section('page-title', 'Tutti gli Articoli')

@section('content')

<div class="page-header">
    <div class="page-title">Catalogo Articoli</div>
    <div class="page-subtitle">// tutti gli articoli con giacenza e numero di lotti associati</div>
</div>

{{-- RICERCA --}}
<div style="margin-bottom:1.75rem">
    <form action="{{ route('articoli.index') }}" method="GET" autocomplete="off">
        <div class="search-box">
            <div class="search-input-wrap">
                <svg class="search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="q" value="{{ $query }}" placeholder="Cerca codice articolo o variante..." autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Cerca</button>
            @if ($query)
                <a href="{{ route('articoli.index') }}" class="btn btn-ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- RISULTATI --}}
@if ($articoli->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <div class="empty-title">Nessun articolo trovato</div>
        <div class="empty-sub">{{ $query ? 'Nessun articolo corrisponde a "'.$query.'"' : 'Nessun articolo presente nel database.' }}</div>
    </div>
@else
    <div class="results-header">
        <div class="results-count">
            <strong>{{ $articoli->total() }}</strong> articoli
            @if ($query)<span class="results-query" style="margin-left:8px">per <span>"{{ $query }}"</span></span>@endif
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Cod. Articolo</th>
                    <th>Variante</th>
                    <th>Lotti</th>
                    <th>Giacenza UM1</th>
                    <th>Giacenza UM2</th>
                    <th>Ult. Aggiorn.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($articoli as $row)
                <tr>
                    <td>
                        <a href="{{ route('articoli.dettaglio', ['cod' => $row->cod_articolo, 'var' => $row->variante]) }}"
                           class="art-link" style="font-weight:600">{{ $row->cod_articolo }}</a>
                    </td>
                    <td class="td-mono td-dim">{{ $row->variante }}</td>
                    <td>
                        @if ($row->num_lotti > 0)
                            <span class="badge badge-lotto">{{ $row->num_lotti }} lotti</span>
                        @else
                            <span class="badge badge-gray">nessun lotto</span>
                        @endif
                    </td>
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
                    <td style="text-align:right">
                        <a href="{{ route('articoli.dettaglio', ['cod' => $row->cod_articolo, 'var' => $row->variante]) }}"
                           class="btn btn-ghost" style="height:30px;font-size:11px;padding:0 12px;">
                            Dettaglio →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $articoli])
@endif

@endsection
