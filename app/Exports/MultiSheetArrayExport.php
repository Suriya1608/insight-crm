<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetArrayExport implements WithMultipleSheets
{
    public function __construct(private array $sheets) {}

    public function sheets(): array
    {
        return array_map(
            fn($s) => new ArrayExport($s['rows'], $s['headings'], $s['title']),
            $this->sheets
        );
    }
}
