<?php

namespace App\Exports\Sheets;

use App\Exports\FormulaValueBinder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ComparisonSheet extends FormulaValueBinder implements FromCollection, WithHeadings, WithTitle, WithCustomValueBinder, ShouldAutoSize
{
    public function __construct(private array $ranking, private array $values) {}

    public function collection()
    {
        $rows = [];
        foreach ($this->ranking as $index => $code) {
            $rowNumber = $index + 2;
            $rows[] = [
                $code,
                $this->values[$code],
                null,
                '=ABS(B'.$rowNumber.'-C'.$rowNumber.')',
                '=IF(ABS(B'.$rowNumber.'-C'.$rowNumber.')<=0.0005,"Sesuai","Tidak Sesuai")',
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return ['Alternatif', 'f(K_i) Sistem', 'f(K_i) Excel (manual)', 'Selisih', 'Sesuai/Tidak Sesuai'];
    }

    public function title(): string
    {
        return 'Perbandingan';
    }
}
