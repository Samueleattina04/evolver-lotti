@extends('layouts.app')

@section('title', 'Lotto ' . $lotto_completo)
@section('page-title', 'Dettaglio Lotto')

@section('content')

<a href="{{ url()->previous(route('lotti.esplora')) }}" class="back-link">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    Torna ai lotti
</a>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <div class="page-title">Lotto</div>
        <span class="lotto-code" style="font-size:16px;padding:5px 12px">{{ $lotto_completo ?: '—' }}</span>
    </div>
    @if ($lotto_data)
        <div class="page-subtitle">// data: {{ \Carbon\Carbon::parse($lotto_data)->format('d/m/Y') }}</div>
    @endif
</div>

{{-- STAT SOMMARIO --}}
@php
    $totUm1       = $pedane->sum(fn($r) => floatval($r->giacenza_um1 ?? 0));
    $totUm2       = $pedane->sum(fn($r) => floatval($r->giacenza_um2 ?? 0));
    $numArticoli  = $pedane->pluck('cod_articolo')->unique()->count();
    $numMagazzini = $pedane->pluck('cod_magazzino')->unique()->count();
@endphp
<div class="stat-grid" style="margin-bottom:2rem">
    <div class="stat-card">
        <div class="stat-label">Pedane / Righe</div>
        <div class="stat-value accent">{{ $pedane->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Articoli distinti</div>
        <div class="stat-value amber">{{ $numArticoli }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Magazzini</div>
        <div class="stat-value">{{ $numMagazzini }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Giacenza totale UM1</div>
        <div class="stat-value {{ $totUm1 > 0 ? 'green' : '' }}">
            {{ number_format($totUm1, 3, ',', '.') }}
        </div>
    </div>
</div>

{{-- PEDANE --}}
<div class="detail-section">
    <div class="section-title">Pedane / Righe magazzino ({{ $pedane->count() }})</div>

    @if ($pedane->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">◎</div>
            <div class="empty-title">Nessuna pedana trovata</div>
            <div class="empty-sub">Il lotto "{{ $lotto_completo }}" non ha righe in MagProgrLotto.</div>
        </div>
    @else
        {{-- Raggruppa per articolo --}}
        @php $byArticolo = $pedane->groupBy('cod_articolo'); @endphp

        @foreach ($byArticolo as $codArt => $righe)
        <div style="margin-bottom:1.25rem">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.6rem">
                <a href="{{ route('articoli.dettaglio', ['cod' => $codArt, 'var' => $righe->first()->variante]) }}"
                   class="art-link" style="font-size:14px;font-weight:600">{{ $codArt }}</a>
                <span class="badge badge-gray">var. {{ $righe->first()->variante }}</span>
                <span class="badge badge-lotto">{{ $righe->count() }} {{ $righe->count() === 1 ? 'pedana' : 'pedane' }}</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Magazzino</th>
                            <th>Area</th>
                            <th>Giacenza UM1</th>
                            <th>Giacenza UM2</th>
                            <th>Ult. Aggiorn.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($righe as $r)
                        <tr>
                            <td class="td-mono" style="font-weight:600">{{ $r->cod_magazzino ?? '—' }}</td>
                            <td class="td-mono td-dim">{{ $r->area_magazzino ?? '—' }}</td>
                            <td>
                                @php $q1 = floatval($r->giacenza_um1 ?? 0); @endphp
                                <span class="qty {{ $q1 > 0 ? 'qty-positive' : ($q1 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                    {{ number_format($q1, 3, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                @php $q2 = floatval($r->giacenza_um2 ?? 0); @endphp
                                <span class="qty {{ $q2 > 0 ? 'qty-positive' : ($q2 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                    {{ number_format($q2, 3, ',', '.') }}
                                </span>
                            </td>
                            <td class="td-mono" style="color:var(--text3);font-size:11px">
                                {{ $r->ultimo_aggiornamento ? \Carbon\Carbon::parse($r->ultimo_aggiornamento)->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        {{-- Totale --}}
        <div style="margin-top:1.25rem;padding:12px 16px;background:var(--bg2);border:1px solid var(--border);border-radius:6px;display:flex;gap:2rem;font-family:var(--mono);font-size:12px;color:var(--text3)">
            <span>Totale UM1:
                <strong class="{{ $totUm1 > 0 ? 'qty-positive' : ($totUm1 < 0 ? 'qty-negative' : 'qty-zero') }}" style="margin-left:6px">
                    {{ number_format($totUm1, 3, ',', '.') }}
                </strong>
            </span>
            <span>Totale UM2:
                <strong class="{{ $totUm2 > 0 ? 'qty-positive' : ($totUm2 < 0 ? 'qty-negative' : 'qty-zero') }}" style="margin-left:6px">
                    {{ number_format($totUm2, 3, ',', '.') }}
                </strong>
            </span>
        </div>
    @endif
</div>

@endsection
