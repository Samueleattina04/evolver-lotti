@extends('layouts.app')

@section('title', 'Articolo ' . $cod)
@section('page-title', 'Dettaglio Articolo')

@section('content')

<a href="{{ url()->previous(route('articoli.index')) }}" class="back-link">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    Torna agli articoli
</a>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <div class="page-title" style="font-family:var(--mono)">{{ $cod }}</div>
        <span class="badge badge-gray" style="font-size:13px;padding:4px 10px">var. {{ $var }}</span>
    </div>
    <div class="page-subtitle">// dettaglio giacenze e lotti associati</div>
</div>

{{-- STAT SOMMARIO --}}
@php
    $totUm1 = $giacenze->sum(fn($r) => floatval($r->giacenza_um1 ?? 0));
    $totUm2 = $giacenze->sum(fn($r) => floatval($r->giacenza_um2 ?? 0));
@endphp
<div class="stat-grid" style="margin-bottom:2rem">
    <div class="stat-card">
        <div class="stat-label">Giacenza totale UM1</div>
        <div class="stat-value {{ $totUm1 > 0 ? 'green' : ($totUm1 < 0 ? '' : '') }}">
            {{ number_format($totUm1, 3, ',', '.') }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Giacenza totale UM2</div>
        <div class="stat-value">{{ number_format($totUm2, 3, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Magazzini</div>
        <div class="stat-value accent">{{ $giacenze->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Lotti registrati</div>
        <div class="stat-value amber">{{ $lotti->count() }}</div>
    </div>
</div>

{{-- GIACENZE PER MAGAZZINO --}}
<div class="detail-section">
    <div class="section-title">Giacenze per magazzino</div>
    @if ($giacenze->isEmpty())
        <p style="font-family:var(--mono);font-size:12px;color:var(--text3)">Nessuna giacenza trovata.</p>
    @else
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
                    @foreach ($giacenze as $g)
                    <tr>
                        <td class="td-mono" style="font-weight:600">{{ $g->cod_magazzino ?? '—' }}</td>
                        <td class="td-mono td-dim">{{ $g->area_magazzino ?? '—' }}</td>
                        <td>
                            @php $q1 = floatval($g->giacenza_um1 ?? 0); @endphp
                            <span class="qty {{ $q1 > 0 ? 'qty-positive' : ($q1 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                {{ number_format($q1, 3, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @php $q2 = floatval($g->giacenza_um2 ?? 0); @endphp
                            <span class="qty {{ $q2 > 0 ? 'qty-positive' : ($q2 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                {{ number_format($q2, 3, ',', '.') }}
                            </span>
                        </td>
                        <td class="td-mono" style="color:var(--text3);font-size:11px">
                            {{ $g->ultimo_aggiornamento ? \Carbon\Carbon::parse($g->ultimo_aggiornamento)->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- LOTTI ASSOCIATI --}}
<div class="detail-section">
    <div class="section-title">Lotti associati ({{ $lotti->count() }})</div>
    @if ($lotti->isEmpty())
        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:6px;padding:14px 16px;display:flex;align-items:center;gap:10px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span style="font-family:var(--mono);font-size:12px;color:var(--amber)">
                Nessun lotto associato a questo articolo.
            </span>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Lotto</th>
                        <th>Data Lotto</th>
                        <th>Magazzino</th>
                        <th>Area</th>
                        <th>Giacenza UM1</th>
                        <th>Giacenza UM2</th>
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
                        <td class="td-mono">{{ $l->cod_magazzino ?? '—' }}</td>
                        <td class="td-mono td-dim">{{ $l->area_magazzino ?? '—' }}</td>
                        <td>
                            @php $q1 = floatval($l->giacenza_um1 ?? 0); @endphp
                            <span class="qty {{ $q1 > 0 ? 'qty-positive' : ($q1 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                {{ number_format($q1, 3, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @php $q2 = floatval($l->giacenza_um2 ?? 0); @endphp
                            <span class="qty {{ $q2 > 0 ? 'qty-positive' : ($q2 < 0 ? 'qty-negative' : 'qty-zero') }}">
                                {{ number_format($q2, 3, ',', '.') }}
                            </span>
                        </td>
                        <td class="td-mono" style="color:var(--text3);font-size:11px">
                            {{ $l->ultimo_aggiornamento ? \Carbon\Carbon::parse($l->ultimo_aggiornamento)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('lotti.dettaglio', ['alfab' => $l->lotto_alfab, 'data' => $l->lotto_data ? \Carbon\Carbon::parse($l->lotto_data)->format('Y-m-d') : '', 'num' => $l->lotto_num ?? 0]) }}"
                               class="btn btn-ghost" style="height:28px;font-size:11px;padding:0 10px;">
                                Pedane →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
