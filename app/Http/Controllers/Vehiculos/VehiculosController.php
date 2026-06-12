<?php

namespace App\Http\Controllers\Vehiculos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehiculos\StoreVehiculoRequest;
use App\Http\Requests\Vehiculos\UpdateVehiculoRequest;
use App\Models\Cliente;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VehiculosController extends Controller
{
    private const TIPOS = ['carro', 'moto', 'camioneta', 'camion', 'otro'];
    private const ESTADOS = ['disponible', 'vendido', 'reservado', 'mantenimiento', 'parqueado', 'inactivo'];
    private const UBICACIONES = [
        'inventario venta' => 'Inventario venta',
        'parqueadero' => 'Parqueadero',
        'taller' => 'Taller',
        'vendido' => 'Vendido',
        'reservado' => 'Reservado',
    ];

    public function index(Request $request): View
    {
        [$vehiculosQuery, $search, $tipo, $estado, $anio] = $this->vehiculosQuery($request);

        $vehiculos = $vehiculosQuery->paginate(7)->withQueryString();

        $stats = [
            ['label' => 'Vehículos totales', 'value' => Vehiculo::count(), 'trend' => '15% vs. mes anterior', 'tone' => 'blue', 'icon' => 'car'],
            ['label' => 'Disponibles para venta', 'value' => Vehiculo::whereNotIn('estado', ['vendido', 'inactivo'])->count(), 'trend' => '12% vs. mes anterior', 'tone' => 'green', 'icon' => 'tag'],
            ['label' => 'Vehículos vendidos', 'value' => Vehiculo::where('estado', 'vendido')->count(), 'trend' => '18% vs. mes anterior', 'tone' => 'purple', 'icon' => 'cart'],
            ['label' => 'En parqueadero', 'value' => Vehiculo::whereIn('ubicacion', ['parqueadero', '1', 1])->count(), 'trend' => '8% vs. mes anterior', 'tone' => 'orange', 'icon' => 'parking'],
            ['label' => 'Valor inventario', 'value' => '$' . number_format((float) Vehiculo::whereIn('estado', ['disponible', 'reservado', 'parqueado', 'mantenimiento'])->sum('precio_venta'), 0, ',', '.'), 'trend' => '22% vs. mes anterior', 'tone' => 'teal', 'icon' => 'money'],
        ];

        $vehiculoTipos = collect(array_merge(['todos' => 'todos'], array_combine(self::TIPOS, self::TIPOS)));

        return view('vehiculos.index', compact('vehiculos', 'stats', 'search', 'tipo', 'estado', 'anio', 'vehiculoTipos'));
    }

    public function create(): View
    {
        return view('vehiculos.create', [
            'vehiculo' => new Vehiculo(),
            'clientes' => Cliente::query()->orderBy('nombres')->get(),
            'tipos' => self::TIPOS,
            'estados' => self::ESTADOS,
            'ubicaciones' => self::UBICACIONES,
        ]);
    }

    public function store(StoreVehiculoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('vehiculos', 'public');
        }

        $vehiculo = Vehiculo::create($data);

        return redirect()
            ->route('vehiculos.show', $vehiculo)
            ->with('status', 'Vehiculo creado correctamente.');
    }

    public function show(Vehiculo $vehiculo): View
    {
        $vehiculo->load('cliente', 'venta');

        return view('vehiculos.show', compact('vehiculo'));
    }

    public function imagen(Vehiculo $vehiculo)
    {
        abort_unless($vehiculo->imagen && Storage::disk('public')->exists($vehiculo->imagen), 404);

        return response()->file(Storage::disk('public')->path($vehiculo->imagen), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function edit(Vehiculo $vehiculo): View
    {
        return view('vehiculos.edit', [
            'vehiculo' => $vehiculo,
            'clientes' => Cliente::query()->orderBy('nombres')->get(),
            'tipos' => self::TIPOS,
            'estados' => self::ESTADOS,
            'ubicaciones' => self::UBICACIONES,
        ]);
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            if ($vehiculo->imagen) {
                Storage::disk('public')->delete($vehiculo->imagen);
            }

            $data['imagen'] = $request->file('imagen')->store('vehiculos', 'public');
        }

        $vehiculo->update($data);

        return redirect()
            ->route('vehiculos.show', $vehiculo)
            ->with('status', 'Vehiculo actualizado correctamente.');
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        if ($vehiculo->imagen) {
            Storage::disk('public')->delete($vehiculo->imagen);
        }

        $vehiculo->delete();

        return redirect()
            ->route('vehiculos.index')
            ->with('status', 'Vehiculo eliminado correctamente.');
    }

    public function exportar(Request $request)
    {
        [$vehiculosQuery] = $this->vehiculosQuery($request);
        $selectedIds = collect(explode(',', (string) $request->query('ids', '')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if ($selectedIds !== []) {
            $vehiculosQuery->whereIn('id', $selectedIds);
        }

        $vehiculos = $vehiculosQuery
            ->with('cliente')
            ->get();

        $fileName = 'vehiculos-vehipark.xlsx';

        return response()->streamDownload(function () use ($vehiculos) {
            echo $this->buildVehiculosWorkbookXlsx($vehiculos);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{0:\Illuminate\Database\Eloquent\Builder,1:string,2:string,3:string,4:string}
     */
    private function vehiculosQuery(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $tipo = $request->query('tipo', 'todos');
        $estado = $request->query('estado', 'todos');
        $anio = $request->query('anio', 'todos');

        $query = Vehiculo::query()->with('cliente')->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('placa', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            });
        }

        if ($tipo !== 'todos') {
            $query->where('tipo', $tipo);
        }

        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }

        if ($anio !== 'todos') {
            if ($anio === 'anteriores') {
                $query->where('anio', '<=', 2020);
            } else {
                $query->where('anio', $anio);
            }
        }

        return [$query, $search, $tipo, $estado, $anio];
    }

    private function tipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'automovil', 'carro' => 'Carro',
            'motocicleta', 'moto' => 'Moto',
            'camioneta' => 'Camioneta',
            'camion' => 'Camión',
            default => 'Otro',
        };
    }

    private function buildVehiculosWorkbookXlsx($vehiculos): string
    {
        $rows = $vehiculos->map(function (Vehiculo $vehiculo): array {
            $vehicleName = trim($vehiculo->marca . ' ' . $vehiculo->modelo . ' ' . ($vehiculo->anio ?? ''));
            $clientName = $vehiculo->cliente ? trim($vehiculo->cliente->nombres . ' ' . ($vehiculo->cliente->apellidos ?? '')) : 'Sin cliente';

            return [
                $vehicleName,
                (string) ($vehiculo->placa ?? ''),
                $this->tipoLabel((string) ($vehiculo->tipo ?? 'otro')),
                (string) ($vehiculo->marca ?? ''),
                (string) ($vehiculo->modelo ?? ''),
                (string) ($vehiculo->anio ?? ''),
                (string) ($vehiculo->color ?? ''),
                (string) ($vehiculo->kilometraje ?? ''),
                '$' . number_format((float) ($vehiculo->precio_compra ?? 0), 0, ',', '.'),
                '$' . number_format((float) ($vehiculo->precio_venta ?? 0), 0, ',', '.'),
                ucfirst((string) ($vehiculo->estado ?? '')),
                $this->vehiculoUbicacionLabel((string) ($vehiculo->ubicacion ?? '')),
                $clientName,
                optional($vehiculo->created_at)->format('d/m/Y'),
            ];
        })->values()->all();

        return $this->buildZipArchive([
            '[Content_Types].xml' => $this->vehiculosContentTypesXml(),
            '_rels/.rels' => $this->vehiculosRelsXml(),
            'docProps/app.xml' => $this->vehiculosAppXml(),
            'docProps/core.xml' => $this->vehiculosCoreXml(),
            'xl/workbook.xml' => $this->vehiculosWorkbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->vehiculosWorkbookRelsXml(),
            'xl/styles.xml' => $this->vehiculosStylesXml(),
            'xl/worksheets/sheet1.xml' => $this->vehiculosSheetXml($rows),
        ]);
    }

    private function vehiculosSheetXml(array $rows): string
    {
        $title = $this->xmlEscape('Reporte de vehículos - VehiPark');
        $subtitle = $this->xmlEscape('Exportado el ' . now()->format('d/m/Y H:i') . ' · Registros: ' . count($rows));

        $headerLabels = [
            'Vehículo',
            'Placa',
            'Tipo',
            'Marca',
            'Modelo',
            'Año',
            'Color',
            'Kilometraje',
            'Precio compra',
            'Precio venta',
            'Estado',
            'Ubicación',
            'Cliente',
            'Fecha de registro',
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
            . '<col min="1" max="1" width="28" customWidth="1"/>'
            . '<col min="2" max="2" width="14" customWidth="1"/>'
            . '<col min="3" max="3" width="14" customWidth="1"/>'
            . '<col min="4" max="4" width="16" customWidth="1"/>'
            . '<col min="5" max="5" width="16" customWidth="1"/>'
            . '<col min="6" max="6" width="12" customWidth="1"/>'
            . '<col min="7" max="7" width="14" customWidth="1"/>'
            . '<col min="8" max="8" width="14" customWidth="1"/>'
            . '<col min="9" max="9" width="14" customWidth="1"/>'
            . '<col min="10" max="10" width="14" customWidth="1"/>'
            . '<col min="11" max="11" width="14" customWidth="1"/>'
            . '<col min="12" max="12" width="16" customWidth="1"/>'
            . '<col min="13" max="13" width="24" customWidth="1"/>'
            . '<col min="14" max="14" width="16" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . $autoFilter
            . '<mergeCells count="2"><mergeCell ref="A1:N1"/><mergeCell ref="A2:N2"/></mergeCells>'
            . '<pageMargins left="0.3" right="0.3" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>'
            . '</worksheet>';
    }

    private function vehiculosWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Vehículos" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function vehiculosWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function vehiculosRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function vehiculosContentTypesXml(): string
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

    private function vehiculosAppXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>VehiPark</Application>'
            . '</Properties>';
    }

    private function vehiculosCoreXml(): string
    {
        $createdAt = now()->toAtomString();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Reporte de vehículos - VehiPark</dc:title>'
            . '<dc:creator>VehiPark</dc:creator>'
            . '<cp:lastModifiedBy>VehiPark</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function vehiculosStylesXml(): string
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

    private function vehiculoUbicacionLabel(string $ubicacion): string
    {
        return match ($ubicacion) {
            'inventario venta', '0' => 'Inventario venta',
            'parqueadero', '1' => 'Parqueadero',
            'taller', '2' => 'Taller',
            'vendido', '3' => 'Vendido',
            'reservado', '4' => 'Reservado',
            default => ucfirst($ubicacion),
        };
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
}
