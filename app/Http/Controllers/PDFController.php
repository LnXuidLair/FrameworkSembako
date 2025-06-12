<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// menggunakan kelas pdf
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    //contoh pembuatan pdf
    public function contohpdf()
    {
        // contoh datanya
        $data = [
            'invoice_number' => 'INV-2025-001',
            'customer_name' => 'John Doe',
            'items' => [
                ['name' => 'Produk A', 'qty' => 2, 'price' => 50000],
                ['name' => 'Produk B', 'qty' => 1, 'price' => 75000],
            ],
            'total' => 175000,
            'date' => now()->format('d M Y'),
        ];

        $pdf = Pdf::loadView('pdf.contoh', $data);
        return $pdf->download('contoh.pdf');
    }
}
