<?php

// =====================================================================
// ISTRUZIONI: aggiungi queste righe al tuo app/Http/Kernel.php
// =====================================================================
//
// Nel array $middlewareAliases (o $routeMiddleware nelle versioni
// precedenti di Laravel) aggiungi:
//
//   'rete.aziendale' => \App\Http\Middleware\SoloReteAziendale::class,
//
// Esempio di come appare il file Kernel.php dopo la modifica:
//
//   protected $middlewareAliases = [
//       'auth'           => \App\Http\Middleware\Authenticate::class,
//       'cache.headers'  => \Illuminate\Http\Middleware\SetCacheHeaders::class,
//       // ... altri middleware ...
//       'rete.aziendale' => \App\Http\Middleware\SoloReteAziendale::class,  // <-- aggiungi questa riga
//   ];
//
// =====================================================================
