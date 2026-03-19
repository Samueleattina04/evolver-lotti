<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LottiController extends Controller
{
    public function index()
    {
        return view('lotti.index', [
            'risultati' => collect(),
            'query'     => '',
            'cercato'   => false,
        ]);
    }

    public function cerca(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $q = trim($request->input('q'));

        /*
         * LOGICA:
         * - Gli articoli CON lotto vengono da MagProgrLotto (join con MagProgrArticoli per la giacenza)
         * - Gli articoli SENZA lotto vengono da MagProgrArticoli (solo quelli NON presenti in MagProgrLotto)
         * - La ricerca filtra per codice lotto, codice articolo o variante
         */
        $risultati = DB::select("
            -- Articoli CON lotto
            SELECT
                'lotto'                         AS tipo,
                l.CodArt                        AS cod_articolo,
                l.VarianteArt                   AS variante,
                l.CodMag                        AS cod_magazzino,
                l.CodAreaMag                    AS area_magazzino,
                l.RifLottoAlfab                 AS lotto_alfab,
                l.RifLottoData                  AS lotto_data,
                l.RifLottoNum                   AS lotto_num,
                CONCAT(
                    ISNULL(l.RifLottoAlfab, ''),
                    ISNULL(CONVERT(VARCHAR, l.RifLottoData, 103), ''),
                    ISNULL(CAST(l.RifLottoNum AS VARCHAR), '')
                )                               AS lotto_completo,
                l.QtaGiacenzaUmMag              AS giacenza_um1,
                l.QtaGiacenzaUm2Mag             AS giacenza_um2,
                l.FirmaUltVarData               AS ultimo_aggiornamento
            FROM MagProgrLotto l
            WHERE
                l.RifLottoAlfab     LIKE ?
                OR l.CodArt         LIKE ?
                OR l.VarianteArt    LIKE ?
                OR CAST(l.RifLottoNum AS VARCHAR) LIKE ?

            UNION ALL

            -- Articoli SENZA lotto (non presenti in MagProgrLotto)
            SELECT
                'articolo'                      AS tipo,
                a.CodArt                        AS cod_articolo,
                a.VarianteArt                   AS variante,
                a.CodMag                        AS cod_magazzino,
                a.CodAreaMag                    AS area_magazzino,
                NULL                            AS lotto_alfab,
                NULL                            AS lotto_data,
                NULL                            AS lotto_num,
                NULL                            AS lotto_completo,
                a.QtaGiacUmMag                  AS giacenza_um1,
                a.QtaGiacUm2Mag                 AS giacenza_um2,
                a.FirmaUltVarData               AS ultimo_aggiornamento
            FROM MagProgrArticoli a
            WHERE NOT EXISTS (
                SELECT 1 FROM MagProgrLotto l
                WHERE l.CodArt      = a.CodArt
                  AND l.VarianteArt = a.VarianteArt
                  AND l.CodMag      = a.CodMag
            )
            AND (
                a.CodArt        LIKE ?
                OR a.VarianteArt LIKE ?
            )

            ORDER BY cod_articolo, lotto_data DESC, lotto_alfab
        ", [
            "%$q%", "%$q%", "%$q%", "%$q%",  // parametri per la parte CON lotto
            "%$q%", "%$q%",                    // parametri per la parte SENZA lotto
        ]);

        return view('lotti.index', [
            'risultati' => collect($risultati),
            'query'     => $q,
            'cercato'   => true,
        ]);
    }
}
