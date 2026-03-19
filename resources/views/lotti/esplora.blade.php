@extends('layouts.app')

@section('title', 'Esplora Lotti')
@section('page-title', 'Esplora Lotti')

@section('content')

<div class="page-header">
    <div class="page-title">Esplora Lotti</div>
    <div class="page-subtitle">// tutti i lotti con numero di pedane e articoli associati</div>
</div>

{{-- RICERCA --}}
<div style="margin-bottom:1.75rem">
    <form action="{{ route('lotti.esplora') }}" method="GET" autocomplete="off">
        <div class="search-box">
            <div class="search-input-wrap">
                <svg class="search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="q" value="{{ $query }}"
                       placeholder="Cerca per codice lotto, numero o articolo..." autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Cerca</button>
            @if ($query)
                <a href="{{ route('lotti.esplora') }}" class="btn btn-ghost">
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
@if ($lotti->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">🏷️</div>
        <div class="empty-title">Nessun lotto trovato</div>
        <div class="empty-sub">{{ $query ? 'Nessun lotto corrisponde a "'.$query.'"' : 'Nessun lotto presente nel database.' }}</div>
    </div>
@else
    <div class="results-header">
        <div class="results-count">
            <strong>{{ $lotti->total() }}</strong> lotti
            @if ($query)<span class="results-query" style="margin-left:8px">per <span>"{{ $query }}"</span></span>@endif
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Codice Lotto</th>
                    <th>Data Lotto</th>
                    <th>Pedane</th>
                    <th>Articoli</th>
                    <th>Giacenza Totale</th>
                    <th>Ult. Aggiorn.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lotti as $l)
                <tr>
                    <td>
                        <a href="{{ route('lotti.dettaglio', ['alfab' => $l->lotto_alfab, 'data' => $l->lotto_data ? \Carbon\Carbon::parse($l->lotto_data)->format('Y-m-d') : '', 'num' => $l->lotto_num ?? 0]) }}"
                           class="lotto-code">{{ $l->lotto_completo ?: '—' }}</a>
                    </td>
                    <td class="td-mono td-dim">
                        {{ $l->lotto_data ? \Carbon\Carbon::parse($l->lotto_data)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        <span class="badge badge-lotto">{{ $l->num_pedane }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $l->num_articoli > 1 ? 'badge-amber' : 'badge-gray' }}">
                            {{ $l->num_articoli }} art.
                        </span>
                    </td>
                    <td>
                        @php $tot = floatval($l->giacenza_totale ?? 0); @endphp
                        <span class="qty {{ $tot > 0 ? 'qty-positive' : ($tot < 0 ? 'qty-negative' : 'qty-zero') }}">
                            {{ number_format($tot, 3, ',', '.') }}
                        </span>
                    </td>
                    <td class="td-mono" style="color:var(--text3);font-size:11px">
                        {{ $l->ultimo_aggiornamento ? \Carbon\Carbon::parse($l->ultimo_aggiornamento)->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('lotti.dettaglio', ['alfab' => $l->lotto_alfab, 'data' => $l->lotto_data ? \Carbon\Carbon::parse($l->lotto_data)->format('Y-m-d') : '', 'num' => $l->lotto_num ?? 0]) }}"
                           class="btn btn-ghost" style="height:30px;font-size:11px;padding:0 12px;">
                            Dettaglio →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $lotti])
@endif

@endsection
