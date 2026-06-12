<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\StoreClienteRequest;
use App\Http\Requests\Clientes\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Venta;
use App\Support\Excel\ManualXlsxBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClientesController extends Controller
{
    public function index(Request $request): View
    {
        [$clientesQuery, $search, $estado, $ciudad, $segmento] = $this->clientesQuery($request);

        $clientes = $clientesQuery->paginate(7)->withQueryString();

        $stats = [
            ['label' => 'Clientes totales', 'value' => Cliente::count(), 'trend' => '18% vs. mes anterior', 'tone' => 'blue', 'icon' => 'users'],
            ['label' => 'Clientes activos', 'value' => Cliente::where('estado', 'activo')->count(), 'trend' => '12% vs. mes anterior', 'tone' => 'green', 'icon' => 'user-check'],
            ['label' => 'Clientes inactivos', 'value' => Cliente::where('estado', 'inactivo')->count(), 'trend' => '6% vs. mes anterior', 'tone' => 'purple', 'icon' => 'user-x'],
            ['label' => 'Clientes frecuentes', 'value' => Cliente::where('segmento', 'frecuente')->count(), 'trend' => '9% vs. mes anterior', 'tone' => 'orange', 'icon' => 'star'],
            ['label' => 'Total compras', 'value' => '$' . number_format((float) Venta::sum('total'), 0, ',', '.'), 'trend' => '22% vs. mes anterior', 'tone' => 'teal', 'icon' => 'money'],
        ];

        $ciudades = Cliente::query()
            ->whereNotNull('ciudad')
            ->where('ciudad', '!=', '')
            ->distinct()
            ->orderBy('ciudad')
            ->pluck('ciudad')
            ->values();

        $segmentos = collect(['todos', 'frecuente', 'activo', 'nuevo', 'inactivo']);

        return view('clientes.index', compact('clientes', 'stats', 'search', 'estado', 'ciudad', 'segmento', 'ciudades', 'segmentos'));
    }

    public function show(Cliente $cliente): View
    {
        $cliente->loadCount('vehiculos')
            ->loadSum('ventas as compras_total', 'total')
            ->loadMax('ventas as ultima_compra', 'fecha_venta')
            ->load(['vehiculos', 'ventas' => fn ($query) => $query->latest('fecha_venta')->limit(5), 'pagos' => fn ($query) => $query->latest()->limit(5), 'movimientosParqueadero' => fn ($query) => $query->latest()->limit(5)]);

        return view('clientes.show', compact('cliente'));
    }

    public function exportarExcel(Request $request)
    {
        [$clientesQuery] = $this->clientesQuery($request);

        $selectedIds = collect(explode(',', (string) $request->query('ids', '')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if ($selectedIds !== []) {
            $clientesQuery->whereIn('id', $selectedIds);
        }

        $clientes = $clientesQuery
            ->withCount(['vehiculos', 'ventas', 'pagos', 'movimientosParqueadero'])
            ->withSum('ventas as compras_total', 'total')
            ->withSum('pagos as pagos_total', 'valor')
            ->withSum('movimientosParqueadero as parqueadero_total', 'total')
            ->withMax('ventas as ultima_compra', 'fecha_venta')
            ->withMax('pagos as ultimo_pago', 'pagado_at')
            ->withMax('movimientosParqueadero as ultimo_movimiento', 'entrada_at')
            ->get();
        $fileName = 'clientes-vehipark.xlsx';

        return response()->streamDownload(function () use ($clientes) {
            echo $this->buildClientesWorkbookXlsx($clientes);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function create(): View
    {
        return view('clientes.create', [
            'cliente' => new Cliente(),
        ]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('clientes', 'public');
        }

        $cliente = Cliente::create($data);

        return redirect()
            ->route('clientes.index')
            ->with('status', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente): View
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            if ($cliente->foto) {
                Storage::disk('public')->delete($cliente->foto);
            }

            $data['foto'] = $request->file('foto')->store('clientes', 'public');
        }

        $cliente->update($data);

        return redirect()
            ->route('clientes.index')
            ->with('status', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        if ($cliente->foto) {
            Storage::disk('public')->delete($cliente->foto);
        }

        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('status', 'Cliente eliminado correctamente.');
    }

    /**
     * @return array{0:\Illuminate\Database\Eloquent\Builder,1:string,2:string,3:string,4:string}
     */
    private function clientesQuery(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $estado = $request->query('estado', 'todos');
        $ciudad = $request->query('ciudad', 'todas');
        $segmento = $request->query('segmento', 'todos');

        $clientesQuery = Cliente::query()
            ->withCount('vehiculos')
            ->withSum('ventas as compras_total', 'total')
            ->withMax('ventas as ultima_compra', 'fecha_venta')
            ->latest('id');

        if ($search !== '') {
            $clientesQuery->where(function ($query) use ($search) {
                $query->where('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('documento', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($estado !== 'todos') {
            $clientesQuery->where('estado', $estado);
        }

        if ($ciudad !== 'todas') {
            $clientesQuery->where('ciudad', $ciudad);
        }

        if ($segmento !== 'todos') {
            $clientesQuery->where('segmento', $segmento);
        }

        return [$clientesQuery, $search, $estado, $ciudad, $segmento];
    }

    private function buildClientesWorkbookXlsx($clientes): string
    {
        $rows = $clientes->map(function (Cliente $cliente): array {
            $fullName = trim($cliente->nombres . ' ' . ($cliente->apellidos ?? ''));
            $lastPurchase = $cliente->ultima_compra ? Carbon::parse($cliente->ultima_compra)->format('d/m/Y') : 'Sin ventas';
            $lastPayment = $cliente->ultimo_pago ? Carbon::parse($cliente->ultimo_pago)->format('d/m/Y h:i A') : 'Sin pagos';
            $lastParking = $cliente->ultimo_movimiento ? Carbon::parse($cliente->ultimo_movimiento)->format('d/m/Y h:i A') : 'Sin movimientos';

            return [
                $fullName,
                (string) $cliente->tipo_documento,
                (string) $cliente->documento,
                (string) ($cliente->telefono ?? ''),
                (string) ($cliente->email ?? ''),
                (string) ($cliente->ciudad ?? ''),
                (string) ($cliente->direccion ?? ''),
                ucfirst((string) ($cliente->segmento ?? '')),
                ucfirst((string) ($cliente->estado ?? '')),
                (string) (int) ($cliente->vehiculos_count ?? 0),
                (int) ($cliente->ventas_count ?? 0) . ' / $' . number_format((float) ($cliente->compras_total ?? 0), 0, ',', '.'),
                (int) ($cliente->pagos_count ?? 0) . ' / $' . number_format((float) ($cliente->pagos_total ?? 0), 0, ',', '.'),
                (int) ($cliente->movimientos_parqueadero_count ?? 0) . ' / $' . number_format((float) ($cliente->parqueadero_total ?? 0), 0, ',', '.'),
                optional($cliente->created_at)->format('d/m/Y'),
                'Venta: ' . $lastPurchase . ' | Pago: ' . $lastPayment . ' | Parqueadero: ' . $lastParking,
            ];
        })->values()->all();

        return ManualXlsxBuilder::build([
            '[Content_Types].xml' => $this->clientesContentTypesXml(),
            '_rels/.rels' => $this->clientesRelsXml(),
            'docProps/app.xml' => $this->clientesAppXml(),
            'docProps/core.xml' => $this->clientesCoreXml(),
            'xl/workbook.xml' => $this->clientesWorkbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->clientesWorkbookRelsXml(),
            'xl/styles.xml' => $this->clientesStylesXml(),
            'xl/worksheets/sheet1.xml' => $this->clientesSheetXml($rows),
        ]);
    }

    private function clientesSheetXml(array $rows): string
    {
        $title = ManualXlsxBuilder::escape('Reporte de clientes - VehiPark');
        $subtitle = ManualXlsxBuilder::escape('Exportado el ' . now()->format('d/m/Y H:i') . ' · Registros: ' . count($rows));

        $headerLabels = [
            'Nombre completo',
            'Tipo de documento',
            'Documento',
            'Teléfono',
            'Correo electrónico',
            'Ciudad',
            'Dirección',
            'Segmento',
            'Estado',
            'Vehículos',
            'Ventas',
            'Pagos',
            'Parqueadero',
            'Fecha de registro',
            'Últimos movimientos',
        ];

        $sheetRows = [];
        $sheetRows[] = $this->xlsxRow(1, [$title], 1);
        $sheetRows[] = $this->xlsxRow(2, [$subtitle], 2);
        $sheetRows[] = $this->xlsxRow(3, $headerLabels, 3);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 4;
            $sheetRows[] = $this->xlsxRow($rowNumber, $row);
        }

        $lastRow = count($rows) + 3;
        $autoFilter = $lastRow >= 3 ? '<autoFilter ref="A3:O' . $lastRow . '"/>' : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="3" topLeftCell="A4" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="18"/>'
            . '<cols>'
            . '<col min="1" max="1" width="24" customWidth="1"/>'
            . '<col min="2" max="2" width="14" customWidth="1"/>'
            . '<col min="3" max="3" width="14" customWidth="1"/>'
            . '<col min="4" max="4" width="16" customWidth="1"/>'
            . '<col min="5" max="5" width="28" customWidth="1"/>'
            . '<col min="6" max="6" width="16" customWidth="1"/>'
            . '<col min="7" max="7" width="24" customWidth="1"/>'
            . '<col min="8" max="8" width="14" customWidth="1"/>'
            . '<col min="9" max="9" width="14" customWidth="1"/>'
            . '<col min="10" max="10" width="11" customWidth="1"/>'
            . '<col min="11" max="11" width="14" customWidth="1"/>'
            . '<col min="12" max="12" width="14" customWidth="1"/>'
            . '<col min="13" max="13" width="14" customWidth="1"/>'
            . '<col min="14" max="14" width="14" customWidth="1"/>'
            . '<col min="15" max="15" width="42" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . $autoFilter
            . '<mergeCells count="2"><mergeCell ref="A1:O1"/><mergeCell ref="A2:O2"/></mergeCells>'
            . '<pageMargins left="0.3" right="0.3" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>'
            . '</worksheet>';
    }

    private function clientesWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Clientes" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function clientesWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function clientesRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function clientesContentTypesXml(): string
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

    private function clientesAppXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>VehiPark</Application>'
            . '</Properties>';
    }

    private function clientesCoreXml(): string
    {
        $createdAt = now()->toAtomString();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Reporte de clientes - VehiPark</dc:title>'
            . '<dc:creator>VehiPark</dc:creator>'
            . '<cp:lastModifiedBy>VehiPark</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function clientesStylesXml(): string
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
            . '<borders count="1">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '</borders>'
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
        return ManualXlsxBuilder::row($rowNumber, $values, $styleId);
    }

}
