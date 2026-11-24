<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SpkKriteriaImport implements ToCollection
{
    private const MIN_VALUES = [
        'C1' => [
            5 => 0.0,
            4 => 3000000.0,
            3 => 5000000.0,
            2 => 7000000.0,
            1 => 10000000.0,
        ],
        'C2' => [
            5 => 1500000.0,
            4 => 1000000.0,
            3 => 700000.0,
            2 => 400000.0,
            1 => 0.0,
        ],
        'C3' => [
            5 => 16.0,
            4 => 12.0,
            3 => 8.0,
            2 => 6.0,
            1 => 0.0,
        ],
        'C4' => [
            5 => 165.0,
            4 => 144.0,
            3 => 120.0,
            2 => 90.0,
            1 => 0.0,
        ],
        'C5' => [
            5 => 7000.0,
            4 => 6000.0,
            3 => 5000.0,
            2 => 4500.0,
            1 => 0.0,
        ],
        'C6' => [
            5 => 120.0,
            4 => 67.0,
            3 => 45.0,
            2 => 25.0,
            1 => 0.0,
        ],
        'C7' => [
            5 => 1024.0,
            4 => 512.0,
            3 => 256.0,
            2 => 128.0,
            1 => 0.0,
        ],
        'C8' => [
            5 => 200.0,
            4 => 108.0,
            3 => 64.0,
            2 => 50.0,
            1 => 0.0,
        ],
    ];

    private ?string $currentCode = null;

    private array $criteria = [];

    private array $subCriteria = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $code = trim((string) ($row[0] ?? ''));

            if (preg_match('/^C\d+$/', $code)) {
                $this->currentCode = $code;
                $this->criteria[$code] = trim((string) ($row[1] ?? ''));
            }

            if ($this->currentCode === null) {
                continue;
            }

            $label = trim((string) ($row[2] ?? ''));
            $score = (int) ($row[3] ?? 0);

            if ($label === '' || ! isset(self::MIN_VALUES[$this->currentCode][$score])) {
                continue;
            }

            $this->subCriteria[$this->currentCode][] = [
                'score' => $score,
                'min_value' => self::MIN_VALUES[$this->currentCode][$score],
                'label' => $label,
            ];
        }
    }

    public function criteria(): array
    {
        return $this->criteria;
    }

    public function subCriteria(): array
    {
        return $this->subCriteria;
    }
}
