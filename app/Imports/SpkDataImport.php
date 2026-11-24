<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\ToCollection;

class SpkDataImport implements ToCollection
{
    private array $alternatives = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($index < 2) {
                continue;
            }

            $name = trim((string) ($row[0] ?? ''));
            $brand = trim((string) ($row[1] ?? ''));
            $kelas = trim((string) ($row[2] ?? ''));

            if ($name === '') {
                continue;
            }

            $values = [];

            foreach (range(3, 10) as $column) {
                if (! isset($row[$column]) || trim((string) $row[$column]) === '') {
                    throw new InvalidArgumentException('Nilai kriteria tidak lengkap untuk '.$name);
                }

                $values[] = $this->normalizeValue($row[$column], $column);
            }

            $this->alternatives[] = [
                'code' => 'A'.(count($this->alternatives) + 1),
                'name' => $name,
                'brand' => $brand !== '' ? $brand : null,
                'kelas' => $kelas !== '' ? $kelas : null,
                'values' => $values,
            ];
        }
    }

    public function alternatives(): array
    {
        return $this->alternatives;
    }

    private function normalizeValue(mixed $value, int $column): float
    {
        $text = trim((string) $value);
        $digits = preg_replace('/\D+/', '', $text);

        if ($digits === null || $digits === '') {
            return 0.0;
        }

        $number = (float) $digits;

        if ($column === 9 && stripos($text, 'tb') !== false) {
            return $number > 0 ? $number * 1024 : 1024.0;
        }

        return $number;
    }
}
