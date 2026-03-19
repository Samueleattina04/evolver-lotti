<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LottiController;

Route::middleware('rete.aziendale')->group(function () {
    Route::get('/', [LottiController::class, 'index'])->name('lotti.index');
    Route::get('/cerca', [LottiController::class, 'cerca'])->name('lotti.cerca');
});
