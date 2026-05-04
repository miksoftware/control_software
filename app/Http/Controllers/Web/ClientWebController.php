<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::query();

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->input('client_type'));
        }

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        $clients = $query->withCount(['mikposLicenses', 'mikposFeatures', 'customProjects'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $clientTypes = ClientType::cases();

        return view('clients.index', compact('clients', 'clientTypes'));
    }

    public function create(): View
    {
        $clientTypes = ClientType::cases();
        return view('clients.create', compact('clientTypes'));
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->validated());

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Client $client): View
    {
        $client->load([
            'mikposLicenses' => fn ($q) => $q->orderByDesc('created_at'),
            'mikposFeatures' => fn ($q) => $q->orderByDesc('created_at'),
            'customProjects' => fn ($q) => $q->orderByDesc('created_at'),
            'payments'       => fn ($q) => $q->orderByDesc('paid_at'),
        ]);

        $clientTypes = ClientType::cases();

        return view('clients.show', compact('client', 'clientTypes'));
    }

    public function statement(Client $client): View
    {
        $client->load([
            'mikposFeatures' => fn ($q) => $q->orderByDesc('created_at'),
            'customProjects' => fn ($q) => $q->orderByDesc('created_at'),
            'payments'       => fn ($q) => $q->orderByDesc('paid_at'),
        ]);

        return view('clients.statement', compact('client'));
    }

    public function edit(Client $client): View
    {
        $clientTypes = ClientType::cases();
        return view('clients.edit', compact('client', 'clientTypes'));
    }

    public function report(Client $client, ClientReportService $reportService): View
    {
        $reportData = $reportService->generateReport($client);

        return view('clients.report', $reportData);
    }

    public function exportExcel(Client $client, ClientReportService $reportService): StreamedResponse
    {
        $reportData = $reportService->generateReport($client);
        $clientModel = $reportData['client'];
        $fs = $reportData['financial_summary'];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('MikSoftware')
            ->setTitle('Reporte - ' . $client->name);

        // Colors
        $primary = '1B0B3B';
        $accent = 'FF7152';
        $emerald = '059669';
        $headerBg = 'F3F4F6';
        $white = 'FFFFFF';
        $darkText = '111827';
        $grayText = '6B7280';

        $currencyFormat = '"$"#,##0.00';

        // ══════════════════════════════════════════════
        // HOJA 1: RESUMEN
        // ══════════════════════════════════════════════
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);

        // Title row
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'REPORTE COMPLETO DEL CLIENTE');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $primary]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(45);

        // Subtitle
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'MikSoftware — ' . $reportData['generated_at']);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => $white], 'italic' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $primary]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(25);

        // Client info section
        $row = 4;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'INFORMACIÓN DEL CLIENTE');
        $this->applySectionHeader($sheet, "A{$row}:D{$row}", $accent);
        $row++;

        $clientInfo = [
            ['Nombre', $clientModel->name],
            ['Email', $clientModel->email],
            ['Teléfono', $clientModel->phone ?? 'No registrado'],
            ['Tipo', $clientModel->client_type->label()],
            ['Cliente desde', $clientModel->created_at->format('d/m/Y')],
        ];
        foreach ($clientInfo as $info) {
            $sheet->setCellValue("A{$row}", $info[0]);
            $sheet->setCellValue("B{$row}", $info[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($grayText));
            $sheet->getStyle("B{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($darkText));
            $row++;
        }

        // Financial summary
        $row++;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'RESUMEN FINANCIERO');
        $this->applySectionHeader($sheet, "A{$row}:D{$row}", $primary);
        $row++;

        $financials = [
            ['Deuda Total Proyectos', (float) $fs['total_projects_debt']],
            ['Deuda Total Mejoras', (float) $fs['total_features_debt']],
            ['Deuda Acumulada', (float) $fs['total_debt']],
            ['Total Pagado', (float) $fs['total_paid']],
            ['Saldo Pendiente', (float) $fs['outstanding_balance']],
        ];
        foreach ($financials as $fin) {
            $sheet->setCellValue("A{$row}", $fin[0]);
            $sheet->setCellValue("B{$row}", $fin[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            if ($fin[0] === 'Total Pagado') {
                $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($emerald));
            } elseif ($fin[0] === 'Saldo Pendiente') {
                $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($accent));
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            }
            $row++;
        }
        $sheet->setCellValue("A{$row}", 'Progreso de Pago');
        $sheet->setCellValue("B{$row}", (float) $fs['payment_progress'] / 100);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($accent));

        // ══════════════════════════════════════════════
        // HOJA 2: PROYECTOS
        // ══════════════════════════════════════════════
        $projSheet = $spreadsheet->createSheet();
        $projSheet->setTitle('Proyectos');

        $projSheet->getColumnDimension('A')->setWidth(30);
        $projSheet->getColumnDimension('B')->setWidth(15);
        $projSheet->getColumnDimension('C')->setWidth(18);
        $projSheet->getColumnDimension('D')->setWidth(18);
        $projSheet->getColumnDimension('E')->setWidth(18);
        $projSheet->getColumnDimension('F')->setWidth(15);
        $projSheet->getColumnDimension('G')->setWidth(15);

        // Title
        $projSheet->mergeCells('A1:G1');
        $projSheet->setCellValue('A1', 'PROYECTOS A MEDIDA — ' . $clientModel->name);
        $projSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $primary]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $projSheet->getRowDimension(1)->setRowHeight(40);

        // Headers
        $headers = ['Proyecto', 'Estado', 'Valor Contrato', 'Abonado', 'Pendiente', 'Fecha Inicio', 'Fecha Fin'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . '3';
            $projSheet->setCellValue($cell, $header);
        }
        $projSheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $darkText]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $accent]]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 4;
        $totalContract = 0;
        $totalPaid = 0;
        foreach ($reportData['projects'] as $project) {
            $paid = (float) $project->payments->sum('amount');
            $pending = max(0, (float) $project->contract_value - $paid);
            $totalContract += (float) $project->contract_value;
            $totalPaid += $paid;

            $projSheet->setCellValue("A{$row}", $project->name);
            $projSheet->setCellValue("B{$row}", $project->status->label());
            $projSheet->setCellValue("C{$row}", (float) $project->contract_value);
            $projSheet->setCellValue("D{$row}", $paid);
            $projSheet->setCellValue("E{$row}", $pending);
            $projSheet->setCellValue("F{$row}", $project->start_date?->format('d/m/Y') ?? 'N/A');
            $projSheet->setCellValue("G{$row}", $project->actual_end_date?->format('d/m/Y') ?? ($project->estimated_end_date?->format('d/m/Y') ?? 'N/A'));

            $projSheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $projSheet->getStyle("D{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($emerald));
            if ($pending > 0) {
                $projSheet->getStyle("E{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($accent));
            }

            // Zebra striping
            if ($row % 2 === 0) {
                $projSheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            }

            $projSheet->getStyle("A{$row}:G{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');
            $row++;
        }

        // Totals row
        $projSheet->setCellValue("A{$row}", 'TOTALES');
        $projSheet->setCellValue("C{$row}", $totalContract);
        $projSheet->setCellValue("D{$row}", $totalPaid);
        $projSheet->setCellValue("E{$row}", max(0, $totalContract - $totalPaid));
        $projSheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $primary]]],
        ]);
        $projSheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);

        // ══════════════════════════════════════════════
        // HOJA 3: HISTORIAL DE PAGOS
        // ══════════════════════════════════════════════
        $paySheet = $spreadsheet->createSheet();
        $paySheet->setTitle('Historial de Pagos');

        $paySheet->getColumnDimension('A')->setWidth(15);
        $paySheet->getColumnDimension('B')->setWidth(15);
        $paySheet->getColumnDimension('C')->setWidth(30);
        $paySheet->getColumnDimension('D')->setWidth(25);
        $paySheet->getColumnDimension('E')->setWidth(15);
        $paySheet->getColumnDimension('F')->setWidth(30);
        $paySheet->getColumnDimension('G')->setWidth(18);

        // Title
        $paySheet->mergeCells('A1:G1');
        $paySheet->setCellValue('A1', 'HISTORIAL DE PAGOS — ' . $clientModel->name);
        $paySheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $emerald]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $paySheet->getRowDimension(1)->setRowHeight(40);

        // Headers
        $payHeaders = ['Fecha', 'Categoría', 'Proyecto', 'Referencia', 'Método', 'Notas', 'Monto'];
        foreach ($payHeaders as $col => $header) {
            $cell = chr(65 + $col) . '3';
            $paySheet->setCellValue($cell, $header);
        }
        $paySheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $emerald]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 4;
        $allPayments = $clientModel->payments->sortByDesc('paid_at');
        foreach ($allPayments as $payment) {
            $categoryLabel = match ($payment->category) {
                'projects' => 'Proyectos',
                'features' => 'Mejoras',
                default => 'Global',
            };

            $paySheet->setCellValue("A{$row}", $payment->paid_at->format('d/m/Y'));
            $paySheet->setCellValue("B{$row}", $categoryLabel);
            $paySheet->setCellValue("C{$row}", $payment->customProject?->name ?? '-');
            $paySheet->setCellValue("D{$row}", $payment->reference ?? 'Abono a cuenta');
            $paySheet->setCellValue("E{$row}", $payment->payment_method->label());
            $paySheet->setCellValue("F{$row}", $payment->notes ?? '');
            $paySheet->setCellValue("G{$row}", (float) $payment->amount);

            $paySheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $paySheet->getStyle("G{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($emerald));

            if ($row % 2 === 0) {
                $paySheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ECFDF5');
            }

            $paySheet->getStyle("A{$row}:G{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');
            $row++;
        }

        // Total row
        $paySheet->setCellValue("F{$row}", 'TOTAL PAGADO');
        $paySheet->setCellValue("G{$row}", (float) $allPayments->sum('amount'));
        $paySheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $emerald]],
        ]);
        $paySheet->getStyle("F{$row}:G{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($white));
        $paySheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);

        // Subtotals by category
        $row += 2;
        $paySheet->setCellValue("E{$row}", 'SUBTOTALES POR CATEGORÍA');
        $paySheet->getStyle("E{$row}")->getFont()->setBold(true)->setSize(11);
        $row++;
        foreach (['global' => 'Global', 'projects' => 'Proyectos', 'features' => 'Mejoras'] as $key => $label) {
            $paySheet->setCellValue("E{$row}", $label);
            $paySheet->setCellValue("F{$row}", $reportData['payments_by_category'][$key]['payments']->count() . ' pagos');
            $paySheet->setCellValue("G{$row}", (float) $reportData['payments_by_category'][$key]['subtotal']);
            $paySheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $paySheet->getStyle("E{$row}")->getFont()->setBold(true);
            $row++;
        }

        // ══════════════════════════════════════════════
        // HOJA 4: DETALLE POR PROYECTO
        // ══════════════════════════════════════════════
        $detailSheet = $spreadsheet->createSheet();
        $detailSheet->setTitle('Pagos por Proyecto');

        $detailSheet->getColumnDimension('A')->setWidth(18);
        $detailSheet->getColumnDimension('B')->setWidth(30);
        $detailSheet->getColumnDimension('C')->setWidth(18);
        $detailSheet->getColumnDimension('D')->setWidth(18);

        $detailSheet->mergeCells('A1:D1');
        $detailSheet->setCellValue('A1', 'DETALLE DE PAGOS POR PROYECTO — ' . $clientModel->name);
        $detailSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $white]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accent]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $detailSheet->getRowDimension(1)->setRowHeight(40);

        $row = 3;
        foreach ($reportData['projects'] as $project) {
            // Project header
            $detailSheet->mergeCells("A{$row}:D{$row}");
            $projPaid = (float) $project->payments->sum('amount');
            $projPending = max(0, (float) $project->contract_value - $projPaid);
            $detailSheet->setCellValue("A{$row}", $project->name . '  |  Contrato: $' . number_format((float) $project->contract_value, 2) . '  |  Abonado: $' . number_format($projPaid, 2) . '  |  Pendiente: $' . number_format($projPending, 2));
            $detailSheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $primary]],
            ]);
            $detailSheet->getRowDimension($row)->setRowHeight(28);
            $row++;

            if ($project->payments->isEmpty()) {
                $detailSheet->mergeCells("A{$row}:D{$row}");
                $detailSheet->setCellValue("A{$row}", 'Sin pagos registrados');
                $detailSheet->getStyle("A{$row}")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($grayText));
                $row += 2;
                continue;
            }

            // Sub-headers
            $detailSheet->setCellValue("A{$row}", 'Fecha');
            $detailSheet->setCellValue("B{$row}", 'Referencia');
            $detailSheet->setCellValue("C{$row}", 'Método');
            $detailSheet->setCellValue("D{$row}", 'Monto');
            $detailSheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $grayText]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
            ]);
            $row++;

            foreach ($project->payments->sortByDesc('paid_at') as $payment) {
                $detailSheet->setCellValue("A{$row}", $payment->paid_at->format('d/m/Y'));
                $detailSheet->setCellValue("B{$row}", $payment->reference ?? 'Pago de proyecto');
                $detailSheet->setCellValue("C{$row}", $payment->payment_method->label());
                $detailSheet->setCellValue("D{$row}", (float) $payment->amount);
                $detailSheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $detailSheet->getStyle("D{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($emerald));
                $row++;
            }

            // Subtotal
            $detailSheet->setCellValue("C{$row}", 'Subtotal');
            $detailSheet->setCellValue("D{$row}", $projPaid);
            $detailSheet->getStyle("C{$row}:D{$row}")->getFont()->setBold(true);
            $detailSheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $detailSheet->getStyle("A{$row}:D{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($accent);
            $row += 2;
        }

        // Set active sheet to first
        $spreadsheet->setActiveSheetIndex(0);

        // Generate file
        $filename = 'Reporte_' . str_replace(' ', '_', $client->name) . '_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function applySectionHeader($sheet, string $range, string $color): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->mikposLicenses()->where('status', 'active')->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente con licencias activas.');
        }

        if ($client->customProjects()->active()->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente con proyectos activos.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
