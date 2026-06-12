<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\VentaRequest;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Vehiculo;
use App\Models\Venta;
use App\Services\Ventas\VentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class VentasController extends Controller
{
    public function __construct(private VentaService $ventas)
    {
    }

    public function index(Request $request): View
    {
        [$ventasQuery, $search, $estado, $desde, $hasta] = $this->ventasQuery($request);

        $ventas = $ventasQuery->paginate(8)->withQueryString();
        $stats = $this->stats();
        $dashboard = $this->dashboardData();
        $clientes = Cliente::query()->orderBy('nombres')->get();
        $vehiculos = $this->vehiculosParaVentaQuery()->get();

        return view('ventas.index', compact('ventas', 'stats', 'dashboard', 'search', 'estado', 'desde', 'hasta', 'clientes', 'vehiculos'));
    }

    public function create(): View
    {
        return view('ventas.create', [
            'venta' => new Venta(['fecha_venta' => now()->toDateString(), 'descuento' => 0, 'impuestos' => 0]),
            'clientes' => Cliente::query()->orderBy('nombres')->get(),
            'vehiculos' => $this->vehiculosParaVentaQuery()->get(),
            'action' => route('ventas.store'),
            'method' => 'POST',
        ]);
    }

    public function store(VentaRequest $request): RedirectResponse
    {
        try {
            $venta = $this->ventas->crear($request->validated(), $request->user()->id);
        } catch (\RuntimeException $exception) {
            return back()->withInput()->withErrors(['vehiculo_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('ventas.show', $venta)
            ->with('status', 'Venta registrada correctamente.');
    }

    public function show(Venta $venta): View
    {
        $venta->load(['cliente', 'vehiculo', 'vendedor', 'pagos.recibidoPor']);

        return view('ventas.show', compact('venta'));
    }

    public function edit(Venta $venta): View
    {
        $venta->load(['cliente', 'vehiculo', 'vendedor', 'pagos']);

        return view('ventas.edit', [
            'venta' => $venta,
            'clientes' => Cliente::query()->orderBy('nombres')->get(),
            'vehiculos' => $this->vehiculosParaVentaQuery()->orWhereKey($venta->vehiculo_id)->get(),
            'action' => route('ventas.update', $venta),
            'method' => 'PUT',
        ]);
    }

    public function update(VentaRequest $request, Venta $venta): RedirectResponse
    {
        try {
            $venta = $this->ventas->actualizar($venta, $request->validated());
        } catch (\RuntimeException $exception) {
            return back()->withInput()->withErrors(['vehiculo_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('ventas.show', $venta)
            ->with('status', 'Venta actualizada correctamente.');
    }

    public function exportar(Request $request)
    {
        [$ventasQuery] = $this->ventasQuery($request);
        $selectedIds = collect(explode(',', (string) $request->query('ids', '')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if ($selectedIds !== []) {
            $ventasQuery->whereIn('id', $selectedIds);
        }

        $ventas = $ventasQuery
            ->with(['cliente', 'vehiculo', 'vendedor'])
            ->withSum('pagos', 'valor')
            ->get();

        return response()->streamDownload(function () use ($ventas) {
            echo $this->buildVentasWorkbookXlsx($ventas);
        }, 'ventas-vehipark.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{0:\Illuminate\Database\Eloquent\Builder,1:string,2:string,3:string,4:string}
     */
    private function ventasQuery(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $estado = (string) $request->query('estado', 'todos');
        $desde = (string) $request->query('desde', '');
        $hasta = (string) $request->query('hasta', '');

        $query = Venta::query()
            ->with(['cliente', 'vehiculo', 'vendedor'])
            ->withSum('pagos', 'valor')
            ->latest('fecha_venta')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('cliente', function ($cliente) use ($search) {
                    $cliente->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('documento', 'like', "%{$search}%");
                })->orWhereHas('vehiculo', function ($vehiculo) use ($search) {
                    $vehiculo->where('marca', 'like', "%{$search}%")
                        ->orWhere('modelo', 'like', "%{$search}%")
                        ->orWhere('placa', 'like', "%{$search}%");
                });
            });
        }

        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }

        if ($desde !== '') {
            $query->whereDate('fecha_venta', '>=', $desde);
        }

        if ($hasta !== '') {
            $query->whereDate('fecha_venta', '<=', $hasta);
        }

        return [$query, $search, $estado, $desde, $hasta];
    }

    private function stats(): array
    {
        $total = (float) Venta::sum('total');
        $pagado = (float) Pago::query()->whereNotNull('venta_id')->sum('valor');
        $monthTotal = (float) Venta::query()
            ->whereBetween('fecha_venta', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        return [
            ['label' => 'Ventas del mes', 'value' => $this->money($monthTotal), 'trend' => Venta::whereBetween('fecha_venta', [now()->startOfMonth(), now()->endOfMonth()])->count() . ' cierres activos', 'tone' => 'blue', 'icon' => 'chart'],
            ['label' => 'Pagado', 'value' => $this->money($pagado), 'trend' => 'Recaudo registrado', 'tone' => 'green', 'icon' => 'money'],
            ['label' => 'Pendiente', 'value' => $this->money(max(0, $total - $pagado)), 'trend' => Venta::whereIn('estado', ['pendiente', 'abono'])->count() . ' ventas por cobrar', 'tone' => 'orange', 'icon' => 'wallet'],
            ['label' => 'Total facturado', 'value' => $this->money($total), 'trend' => Venta::count() . ' operaciones históricas', 'tone' => 'purple', 'icon' => 'tag'],
        ];
    }

    private function vehiculosParaVentaQuery()
    {
        return Vehiculo::query()
            ->orderByRaw("CASE WHEN estado IN ('vendido', 'inactivo') THEN 1 ELSE 0 END")
            ->orderBy('marca')
            ->orderBy('modelo')
            ->orderBy('placa');
    }

    private function dashboardData(): array
    {
        $today = today();
        $salesToday = (float) Venta::query()->whereDate('fecha_venta', $today)->sum('total');
        $paidToday = (float) Pago::query()->whereNotNull('venta_id')->whereDate('pagado_at', $today)->sum('valor');
        $pendingSales = Venta::query()->whereIn('estado', ['pendiente', 'abono'])->count();

        $upcomingCollections = Venta::query()
            ->with(['cliente', 'vehiculo'])
            ->withSum('pagos', 'valor')
            ->whereIn('estado', ['pendiente', 'abono'])
            ->oldest('fecha_venta')
            ->limit(4)
            ->get()
            ->map(function (Venta $venta) {
                $pagado = (float) ($venta->pagos_sum_valor ?? 0);

                return [
                    'id' => $venta->id,
                    'cliente' => trim($venta->cliente->nombres . ' ' . ($venta->cliente->apellidos ?? '')),
                    'vehiculo' => trim($venta->vehiculo->marca . ' ' . $venta->vehiculo->modelo),
                    'saldo' => $this->money(max(0, (float) $venta->total - $pagado)),
                    'fecha' => optional($venta->fecha_venta)->format('d/m/Y'),
                    'estado' => ucfirst($venta->estado),
                ];
            });

        $recentActivity = collect()
            ->merge(Venta::query()->with(['cliente', 'vehiculo'])->latest('created_at')->limit(4)->get()->map(function (Venta $venta) {
                return [
                    'type' => 'venta',
                    'title' => 'Venta #' . $venta->id . ' registrada',
                    'meta' => trim($venta->cliente->nombres . ' ' . ($venta->cliente->apellidos ?? '')) . ' · ' . trim($venta->vehiculo->marca . ' ' . $venta->vehiculo->modelo),
                    'amount' => $this->money((float) $venta->total),
                    'time' => $venta->created_at?->diffForHumans() ?? 'Sin fecha',
                    'sort_at' => $venta->created_at ?? now()->subYears(10),
                ];
            }))
            ->merge(Pago::query()->with(['cliente', 'venta'])->whereNotNull('venta_id')->latest('pagado_at')->limit(4)->get()->map(function (Pago $pago) {
                return [
                    'type' => 'pago',
                    'title' => 'Abono recibido',
                    'meta' => trim(($pago->cliente->nombres ?? '') . ' ' . ($pago->cliente->apellidos ?? '')) . ' · Venta #' . $pago->venta_id,
                    'amount' => $this->money((float) $pago->valor),
                    'time' => $pago->pagado_at?->diffForHumans() ?? 'Sin fecha',
                    'sort_at' => $pago->pagado_at ?? now()->subYears(10),
                ];
            }))
            ->sortByDesc(fn (array $activity) => Carbon::parse($activity['sort_at']))
            ->take(6)
            ->map(function (array $activity) {
                unset($activity['sort_at']);

                return $activity;
            })
            ->values();

        return [
            'today' => [
                ['label' => 'Vendido hoy', 'value' => $this->money($salesToday), 'hint' => Venta::query()->whereDate('fecha_venta', $today)->count() . ' ventas'],
                ['label' => 'Recaudado hoy', 'value' => $this->money($paidToday), 'hint' => Pago::query()->whereNotNull('venta_id')->whereDate('pagado_at', $today)->count() . ' pagos'],
                ['label' => 'Cartera activa', 'value' => $pendingSales, 'hint' => 'pendientes o con abono'],
            ],
            'upcomingCollections' => $upcomingCollections,
            'recentActivity' => $recentActivity,
        ];
    }

    private function buildVentasWorkbookXlsx($ventas): string
    {
        $rows = $ventas->map(function (Venta $venta): array {
            $pagado = (float) ($venta->pagos_sum_valor ?? 0);
            $saldo = max(0, (float) $venta->total - $pagado);

            return [
                '#' . $venta->id,
                trim($venta->cliente->nombres . ' ' . ($venta->cliente->apellidos ?? '')),
                trim($venta->vehiculo->marca . ' ' . $venta->vehiculo->modelo . ' ' . ($venta->vehiculo->placa ?? '')),
                (string) ($venta->vehiculo->placa ?? ''),
                optional($venta->fecha_venta)->format('d/m/Y'),
                '$' . number_format((float) $venta->precio_base, 0, ',', '.'),
                '$' . number_format((float) $venta->descuento, 0, ',', '.'),
                '$' . number_format((float) $venta->impuestos, 0, ',', '.'),
                '$' . number_format((float) $venta->total, 0, ',', '.'),
                '$' . number_format($pagado, 0, ',', '.'),
                '$' . number_format($saldo, 0, ',', '.'),
                ucfirst((string) $venta->estado),
                $venta->vendedor?->name ?? 'Sin asignar',
                trim($venta->cliente->documento ?? ''),
            ];
        })->values()->all();

        return $this->buildZipArchive([
            '[Content_Types].xml' => $this->ventasContentTypesXml(),
            '_rels/.rels' => $this->ventasRelsXml(),
            'docProps/app.xml' => $this->ventasAppXml(),
            'docProps/core.xml' => $this->ventasCoreXml(),
            'xl/workbook.xml' => $this->ventasWorkbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->ventasWorkbookRelsXml(),
            'xl/styles.xml' => $this->ventasStylesXml(),
            'xl/worksheets/sheet1.xml' => $this->ventasSheetXml($rows),
        ]);
    }

    private function ventasSheetXml(array $rows): string
    {
        $title = $this->xmlEscape('Reporte de ventas - VehiPark');
        $subtitle = $this->xmlEscape('Exportado el ' . now()->format('d/m/Y H:i') . ' · Registros: ' . count($rows));

        $headerLabels = [
            'Venta',
            'Cliente',
            'Vehículo',
            'Placa',
            'Fecha',
            'Precio base',
            'Descuento',
            'Impuestos',
            'Total',
            'Pagado',
            'Saldo',
            'Estado',
            'Vendedor',
            'Documento cliente',
        ];

        $sheetRows = [];
        $sheetRows[] = $this->xlsxRow(1, [$title], 1);
        $sheetRows[] = $this->xlsxRow(2, [$subtitle], 2);
        $sheetRows[] = $this->xlsxRow(3, $headerLabels, 3);

        foreach ($rows as $index => $row) {
            $sheetRows[] = $this->xlsxRow($index + 4, $row);
        }

        $lastRow = count($rows) + 3;
        $autoFilter = $lastRow >= 3 ? '<autoFilter ref="A3:N' . $lastRow . '"/>' : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="3" topLeftCell="A4" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="18"/>'
            . '<cols>'
            . '<col min="1" max="1" width="12" customWidth="1"/>'
            . '<col min="2" max="2" width="28" customWidth="1"/>'
            . '<col min="3" max="3" width="28" customWidth="1"/>'
            . '<col min="4" max="4" width="14" customWidth="1"/>'
            . '<col min="5" max="5" width="14" customWidth="1"/>'
            . '<col min="6" max="6" width="14" customWidth="1"/>'
            . '<col min="7" max="7" width="14" customWidth="1"/>'
            . '<col min="8" max="8" width="14" customWidth="1"/>'
            . '<col min="9" max="9" width="14" customWidth="1"/>'
            . '<col min="10" max="10" width="14" customWidth="1"/>'
            . '<col min="11" max="11" width="14" customWidth="1"/>'
            . '<col min="12" max="12" width="14" customWidth="1"/>'
            . '<col min="13" max="13" width="22" customWidth="1"/>'
            . '<col min="14" max="14" width="18" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . $autoFilter
            . '<mergeCells count="2"><mergeCell ref="A1:N1"/><mergeCell ref="A2:N2"/></mergeCells>'
            . '<pageMargins left="0.3" right="0.3" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>'
            . '</worksheet>';
    }

    private function ventasWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Ventas" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function ventasWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function ventasRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function ventasContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    private function ventasAppXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>VehiPark</Application>'
            . '</Properties>';
    }

    private function ventasCoreXml(): string
    {
        $createdAt = now()->toAtomString();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Reporte de ventas - VehiPark</dc:title>'
            . '<dc:creator>VehiPark</dc:creator>'
            . '<cp:lastModifiedBy>VehiPark</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function ventasStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="4">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><i/><sz val="10"/><color rgb="FF0F766E"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="4">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF0F766E"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1F2937"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '<dxfs count="0"/>'
            . '<tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/>'
            . '</styleSheet>';
    }

    private function xlsxRow(int $rowNumber, array $values, ?int $styleId = null): string
    {
        $xml = '<row r="' . $rowNumber . '" spans="1:14">';

        foreach ($values as $index => $value) {
            $column = $this->xlsxColumn($index + 1);
            $styleAttribute = $styleId === null ? '' : ' s="' . $styleId . '"';
            $xml .= '<c r="' . $column . $rowNumber . '" t="inlineStr"' . $styleAttribute . '><is><t xml:space="preserve">' . $this->xmlEscape((string) $value) . '</t></is></c>';
        }

        return $xml . '</row>';
    }

    private function xlsxColumn(int $index): string
    {
        $column = '';

        while ($index > 0) {
            $index--;
            $column = chr(65 + ($index % 26)) . $column;
            $index = intdiv($index, 26);
        }

        return $column;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function buildZipArchive(array $files): string
    {
        $data = '';
        $centralDirectory = '';
        $offset = 0;

        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $content = (string) $content;
            $nameLength = strlen($name);
            $contentLength = strlen($content);
            $crc = crc32($content);
            if ($crc < 0) {
                $crc += 4294967296;
            }

            $localHeader =
                $this->zipPack32(0x04034b50) .
                $this->zipPack16(20) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack32($crc) .
                $this->zipPack32($contentLength) .
                $this->zipPack32($contentLength) .
                $this->zipPack16($nameLength) .
                $this->zipPack16(0) .
                $name .
                $content;

            $data .= $localHeader;

            $centralDirectory .=
                $this->zipPack32(0x02014b50) .
                $this->zipPack16(20) .
                $this->zipPack16(20) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack32($crc) .
                $this->zipPack32($contentLength) .
                $this->zipPack32($contentLength) .
                $this->zipPack16($nameLength) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack16(0) .
                $this->zipPack32(0) .
                $this->zipPack32($offset) .
                $name;

            $offset += strlen($localHeader);
        }

        $centralDirectoryOffset = strlen($data);
        $data .= $centralDirectory;
        $data .=
            $this->zipPack32(0x06054b50) .
            $this->zipPack16(0) .
            $this->zipPack16(0) .
            $this->zipPack16(count($files)) .
            $this->zipPack16(count($files)) .
            $this->zipPack32(strlen($centralDirectory)) .
            $this->zipPack32($centralDirectoryOffset) .
            $this->zipPack16(0);

        return $data;
    }

    private function zipPack16(int $value): string
    {
        return pack('v', $value & 0xffff);
    }

    private function zipPack32(int $value): string
    {
        return pack('V', $value & 0xffffffff);
    }

    private function money(float $value): string
    {
        return '$' . number_format($value, 0, ',', '.');
    }
}
