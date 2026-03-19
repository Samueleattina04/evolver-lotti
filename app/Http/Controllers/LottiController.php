<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LottiController extends Controller
{
    private const PER_PAGE = 50;

    private const SORT_COLS = [
        'cod_articolo', 'variante', 'lotto_completo', 'lotto_data',
        'cod_magazzino', 'area_magazzino', 'giacenza_um1', 'giacenza_um2',
    ];

    // ─── Ricerca principale ───────────────────────────────────────────────────

    public function index()
    {
        return view('lotti.index', [
            'risultati'    => new LengthAwarePaginator(collect(), 0, self::PER_PAGE),
            'query'        => '',
            'cercato'      => false,
            'sort'         => 'cod_articolo',
            'dir'          => 'asc',
            'mag'          => '',
            'soloGiacenza' => false,
            'dataDa'       => '',
            'dataA'        => '',
        ]);
    }

    public function cerca(Request $request)
    {
        $request->validate(['q' => 'required|string|min:1|max:100']);

        $q            = trim((string) $request->input('q'));
        $mag          = trim((string) $request->input('mag', ''));
        $soloGiacenza = $request->boolean('solo_giacenza');
        $dataDa       = (string) $request->input('data_da', '');
        $dataA        = (string) $request->input('data_a', '');
        $sort         = (string) $request->input('sort', 'cod_articolo');
        $dir          = strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $page         = max(1, (int) $request->input('page', 1));

        if (! in_array($sort, self::SORT_COLS, true)) {
            $sort = 'cod_articolo';
        }

        ['union' => $union, 'params' => $params] = $this->buildRicercaQuery(
            $q, $mag, $soloGiacenza, $dataDa, $dataA
        );

        $total  = DB::selectOne("SELECT COUNT(*) AS total FROM ($union) AS t", $params)->total ?? 0;
        $offset = ($page - 1) * self::PER_PAGE;
        $limit  = self::PER_PAGE;

        $rows = DB::select(
            "SELECT * FROM ($union) AS t ORDER BY $sort $dir OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY",
            $params
        );

        $paginati = new LengthAwarePaginator(
            collect($rows),
            $total,
            self::PER_PAGE,
            $page,
            ['path' => route('lotti.cerca'), 'query' => $request->except('page')]
        );

        return view('lotti.index', [
            'risultati'    => $paginati,
            'query'        => $q,
            'cercato'      => true,
            'sort'         => $sort,
            'dir'          => $dir,
            'mag'          => $mag,
            'soloGiacenza' => $soloGiacenza,
            'dataDa'       => $dataDa,
            'dataA'        => $dataA,
        ]);
    }

    public function esportaCsv(Request $request)
    {
        $request->validate(['q' => 'required|string|min:1|max:100']);

        $q            = trim((string) $request->input('q'));
        $mag          = trim((string) $request->input('mag', ''));
        $soloGiacenza = $request->boolean('solo_giacenza');
        $dataDa       = (string) $request->input('data_da', '');
        $dataA        = (string) $request->input('data_a', '');

        ['union' => $union, 'params' => $params] = $this->buildRicercaQuery(
            $q, $mag, $soloGiacenza, $dataDa, $dataA
        );

        $rows     = DB::select("SELECT * FROM ($union) AS t ORDER BY cod_articolo ASC, lotto_data DESC", $params);
        $filename = 'lotti_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $fp = fopen('php://output', 'w');
            fwrite($fp, "\xEF\xBB\xBF"); // UTF-8 BOM per Excel
            fputcsv($fp, [
                'Tipo', 'Cod. Articolo', 'Variante', 'Lotto', 'Data Lotto',
                'Magazzino', 'Area', 'Giacenza UM1', 'Giacenza UM2', 'Ult. Aggiornamento',
            ], ';');
            foreach ($rows as $row) {
                fputcsv($fp, [
                    $row->tipo,
                    $row->cod_articolo ?? '',
                    $row->variante ?? '',
                    $row->lotto_completo ?? '',
                    $row->lotto_data ? Carbon::parse($row->lotto_data)->format('d/m/Y') : '',
                    $row->cod_magazzino ?? '',
                    $row->area_magazzino ?? '',
                    number_format((float) ($row->giacenza_um1 ?? 0), 3, ',', '.'),
                    number_format((float) ($row->giacenza_um2 ?? 0), 3, ',', '.'),
                    $row->ultimo_aggiornamento ? Carbon::parse($row->ultimo_aggiornamento)->format('d/m/Y H:i') : '',
                ], ';');
            }
            fclose($fp);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Articoli ────────────────────────────────────────────────────────────

    public function articoli(Request $request)
    {
        $q    = trim($request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $like = $q !== '' ? "%$q%" : '%';

        $baseSql = "
            SELECT
                a.CodArt                        AS cod_articolo,
                a.VarianteArt                   AS variante,
                SUM(a.QtaGiacUmMag)             AS giacenza_um1,
                SUM(a.QtaGiacUm2Mag)            AS giacenza_um2,
                (
                    SELECT COUNT(*)
                    FROM MagProgrLotto l
                    WHERE l.CodArt = a.CodArt AND l.VarianteArt = a.VarianteArt
                )                               AS num_lotti,
                MAX(a.FirmaUltVarData)          AS ultimo_aggiornamento
            FROM MagProgrArticoli a
            WHERE (a.CodArt LIKE ? OR a.VarianteArt LIKE ?)
            GROUP BY a.CodArt, a.VarianteArt
        ";
        $params = [$like, $like];

        $total = DB::selectOne(
            "SELECT COUNT(*) AS total FROM (SELECT a.CodArt, a.VarianteArt FROM MagProgrArticoli a WHERE (a.CodArt LIKE ? OR a.VarianteArt LIKE ?) GROUP BY a.CodArt, a.VarianteArt) AS t",
            $params
        )->total ?? 0;

        $offset = ($page - 1) * self::PER_PAGE;
        $limit  = self::PER_PAGE;
        $rows   = DB::select(
            "$baseSql ORDER BY cod_articolo, variante OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY",
            $params
        );

        return view('articoli.index', [
            'articoli' => new LengthAwarePaginator(
                collect($rows), $total, self::PER_PAGE, $page,
                ['path' => route('articoli.index'), 'query' => $request->except('page')]
            ),
            'query'    => $q,
        ]);
    }

    public function dettaglioArticolo(Request $request)
    {
        $request->validate([
            'cod' => 'required|string|max:50',
            'var' => 'required|string|max:20',
        ]);

        $cod = $request->input('cod');
        $var = $request->input('var');

        $giacenze = collect(DB::select("
            SELECT
                CodMag          AS cod_magazzino,
                CodAreaMag      AS area_magazzino,
                QtaGiacUmMag    AS giacenza_um1,
                QtaGiacUm2Mag   AS giacenza_um2,
                FirmaUltVarData AS ultimo_aggiornamento
            FROM MagProgrArticoli
            WHERE CodArt = ? AND VarianteArt = ?
            ORDER BY CodMag, CodAreaMag
        ", [$cod, $var]));

        $lotti = collect(DB::select("
            SELECT
                RifLottoAlfab   AS lotto_alfab,
                RifLottoData    AS lotto_data,
                RifLottoNum     AS lotto_num,
                CONCAT(
                    ISNULL(RifLottoAlfab, ''),
                    ISNULL(CONVERT(VARCHAR, RifLottoData, 103), ''),
                    ISNULL(CAST(RifLottoNum AS VARCHAR), '')
                )               AS lotto_completo,
                CodMag          AS cod_magazzino,
                CodAreaMag      AS area_magazzino,
                QtaGiacenzaUmMag    AS giacenza_um1,
                QtaGiacenzaUm2Mag   AS giacenza_um2,
                FirmaUltVarData     AS ultimo_aggiornamento
            FROM MagProgrLotto
            WHERE CodArt = ? AND VarianteArt = ?
            ORDER BY RifLottoData DESC, RifLottoAlfab, CodMag
        ", [$cod, $var]));

        return view('articoli.dettaglio', [
            'cod'      => $cod,
            'var'      => $var,
            'giacenze' => $giacenze,
            'lotti'    => $lotti,
        ]);
    }

    // ─── Lotti ───────────────────────────────────────────────────────────────

    public function esploraLotti(Request $request)
    {
        $q    = trim($request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $like = $q !== '' ? "%$q%" : '%';

        $baseSql = "
            SELECT
                RifLottoAlfab   AS lotto_alfab,
                RifLottoData    AS lotto_data,
                RifLottoNum     AS lotto_num,
                CONCAT(
                    ISNULL(RifLottoAlfab, ''),
                    ISNULL(CONVERT(VARCHAR, RifLottoData, 103), ''),
                    ISNULL(CAST(RifLottoNum AS VARCHAR), '')
                )               AS lotto_completo,
                COUNT(*)                AS num_pedane,
                COUNT(DISTINCT CodArt)  AS num_articoli,
                SUM(QtaGiacenzaUmMag)   AS giacenza_totale,
                MAX(FirmaUltVarData)    AS ultimo_aggiornamento
            FROM MagProgrLotto
            WHERE (
                RifLottoAlfab LIKE ?
                OR CAST(RifLottoNum AS VARCHAR) LIKE ?
                OR CodArt LIKE ?
            )
            GROUP BY RifLottoAlfab, RifLottoData, RifLottoNum
        ";
        $params = [$like, $like, $like];

        $total = DB::selectOne(
            "SELECT COUNT(*) AS total FROM (SELECT RifLottoAlfab, RifLottoData, RifLottoNum FROM MagProgrLotto WHERE (RifLottoAlfab LIKE ? OR CAST(RifLottoNum AS VARCHAR) LIKE ? OR CodArt LIKE ?) GROUP BY RifLottoAlfab, RifLottoData, RifLottoNum) AS t",
            $params
        )->total ?? 0;

        $offset = ($page - 1) * self::PER_PAGE;
        $limit  = self::PER_PAGE;
        $rows   = DB::select(
            "$baseSql ORDER BY lotto_data DESC, lotto_alfab OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY",
            $params
        );

        return view('lotti.esplora', [
            'lotti' => new LengthAwarePaginator(
                collect($rows), $total, self::PER_PAGE, $page,
                ['path' => route('lotti.esplora'), 'query' => $request->except('page')]
            ),
            'query' => $q,
        ]);
    }

    public function dettaglioLotto(Request $request)
    {
        $request->validate([
            'alfab' => 'nullable|string|max:50',
            'data'  => 'nullable|string|max:30',
            'num'   => 'nullable|integer',
        ]);

        $alfab = $request->input('alfab', '');
        $data  = $request->input('data', '');
        $num   = (int) $request->input('num', 0);

        $pedane = collect(DB::select("
            SELECT
                CodArt          AS cod_articolo,
                VarianteArt     AS variante,
                CodMag          AS cod_magazzino,
                CodAreaMag      AS area_magazzino,
                RifLottoAlfab   AS lotto_alfab,
                RifLottoData    AS lotto_data,
                RifLottoNum     AS lotto_num,
                QtaGiacenzaUmMag    AS giacenza_um1,
                QtaGiacenzaUm2Mag   AS giacenza_um2,
                FirmaUltVarData     AS ultimo_aggiornamento
            FROM MagProgrLotto
            WHERE
                ISNULL(RifLottoAlfab, '') = ?
                AND (? = '' OR CAST(RifLottoData AS DATE) = CAST(? AS DATE))
                AND ISNULL(RifLottoNum, 0) = ?
            ORDER BY CodArt, VarianteArt, CodMag, CodAreaMag
        ", [$alfab, $data, $data, $num]));

        $lottoCompleto = $alfab
            . ($data ? Carbon::parse($data)->format('d/m/Y') : '')
            . ($num ? (string) $num : '');

        return view('lotti.dettaglio', [
            'pedane'         => $pedane,
            'lotto_completo' => $lottoCompleto,
            'lotto_alfab'    => $alfab,
            'lotto_data'     => $data,
            'lotto_num'      => $num,
        ]);
    }

    // ─── Senza lotto ─────────────────────────────────────────────────────────

    public function senzaLotto(Request $request)
    {
        $q            = trim($request->input('q', ''));
        $mag          = trim($request->input('mag', ''));
        $soloGiacenza = $request->boolean('solo_giacenza');
        $page         = max(1, (int) $request->input('page', 1));
        $like         = $q !== '' ? "%$q%" : '%';

        $where  = "WHERE NOT EXISTS (
                SELECT 1 FROM MagProgrLotto l
                WHERE l.CodArt = a.CodArt AND l.VarianteArt = a.VarianteArt AND l.CodMag = a.CodMag
            )
            AND (a.CodArt LIKE ? OR a.VarianteArt LIKE ?)";
        $params = [$like, $like];

        if ($mag !== '') {
            $where    .= " AND a.CodMag = ?";
            $params[]  = $mag;
        }
        if ($soloGiacenza) {
            $where .= " AND a.QtaGiacUmMag > 0";
        }

        $baseSql = "
            SELECT
                a.CodArt        AS cod_articolo,
                a.VarianteArt   AS variante,
                a.CodMag        AS cod_magazzino,
                a.CodAreaMag    AS area_magazzino,
                a.QtaGiacUmMag  AS giacenza_um1,
                a.QtaGiacUm2Mag AS giacenza_um2,
                a.FirmaUltVarData AS ultimo_aggiornamento
            FROM MagProgrArticoli a
            $where
        ";

        $total  = DB::selectOne("SELECT COUNT(*) AS total FROM ($baseSql) AS t", $params)->total ?? 0;
        $offset = ($page - 1) * self::PER_PAGE;
        $limit  = self::PER_PAGE;
        $rows   = DB::select(
            "$baseSql ORDER BY a.CodArt, a.VarianteArt, a.CodMag OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY",
            $params
        );

        return view('lotti.senza_lotto', [
            'articoli'     => new LengthAwarePaginator(
                collect($rows), $total, self::PER_PAGE, $page,
                ['path' => route('lotti.senza'), 'query' => $request->except('page')]
            ),
            'query'        => $q,
            'mag'          => $mag,
            'soloGiacenza' => $soloGiacenza,
        ]);
    }

    // ─── Query builder privato ────────────────────────────────────────────────

    private function buildRicercaQuery(
        string $q,
        string $mag,
        bool   $soloGiacenza,
        string $dataDa,
        string $dataA
    ): array {
        $like = "%$q%";

        $lottoPart = "
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
            WHERE (
                l.RifLottoAlfab     LIKE ?
                OR l.CodArt         LIKE ?
                OR l.VarianteArt    LIKE ?
                OR CAST(l.RifLottoNum AS VARCHAR) LIKE ?
            )
        ";
        $lottoParams = [$like, $like, $like, $like];

        if ($mag !== '') {
            $lottoPart   .= " AND l.CodMag = ?";
            $lottoParams[] = $mag;
        }
        if ($soloGiacenza) {
            $lottoPart .= " AND l.QtaGiacenzaUmMag > 0";
        }
        if ($dataDa !== '') {
            $lottoPart   .= " AND CAST(l.RifLottoData AS DATE) >= CAST(? AS DATE)";
            $lottoParams[] = $dataDa;
        }
        if ($dataA !== '') {
            $lottoPart   .= " AND CAST(l.RifLottoData AS DATE) <= CAST(? AS DATE)";
            $lottoParams[] = $dataA;
        }

        $articoloPart = "
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
            AND (a.CodArt LIKE ? OR a.VarianteArt LIKE ?)
        ";
        $articoloParams = [$like, $like];

        if ($mag !== '') {
            $articoloPart    .= " AND a.CodMag = ?";
            $articoloParams[] = $mag;
        }
        if ($soloGiacenza) {
            $articoloPart .= " AND a.QtaGiacUmMag > 0";
        }

        return [
            'union'  => "$lottoPart UNION ALL $articoloPart",
            'params' => array_merge($lottoParams, $articoloParams),
        ];
    }
}
