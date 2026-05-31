<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;

class InvoicePdfController extends Controller
{
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'order',
            'customer',
            'currency',
            'items.product',
            'items.productVariant',
        ]);

        $tempDir = storage_path('app/mpdf');

        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'tempDir' => $tempDir,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->SetTitle('Invoice ' . $invoice->invoice_number);
        $mpdf->SetDirectionality('rtl');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        $html = view('admin.invoices.pdf', [
            'invoice' => $invoice,
        ])->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice-' . $invoice->invoice_number . '.pdf"',
        ]);
    }
}