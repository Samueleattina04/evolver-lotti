<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LottiController;

Route::middleware('rete.aziendale')->group(function () {

    // Ricerca principale
    Route::get('/',       [LottiController::class, 'index'])->name('lotti.index');
    Route::get('/cerca',  [LottiController::class, 'cerca'])->name('lotti.cerca');
    Route::get('/export', [LottiController::class, 'esportaCsv'])->name('lotti.export');

    // Articoli
    Route::get('/articoli',          [LottiController::class, 'articoli'])->name('articoli.index');
    Route::get('/articoli/dettaglio', [LottiController::class, 'dettaglioArticolo'])->name('articoli.dettaglio');

    // Lotti
    Route::get('/lotti/esplora',   [LottiController::class, 'esploraLotti'])->name('lotti.esplora');
    Route::get('/lotti/dettaglio', [LottiController::class, 'dettaglioLotto'])->name('lotti.dettaglio');

    // Senza lotto
    Route::get('/senza-lotto', [LottiController::class, 'senzaLotto'])->name('lotti.senza');

});
