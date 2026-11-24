<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SpkBobotImport implements ToCollection
{
    private const METADATA = [
        'C1' => ['name' => 'Harga', 'unit' => 'Rp', 'direction' => 'desc'],
        'C2' => ['name' => 'Skor AnTuTu', 'unit' => 'skor', 'direction' => 'asc'],
        'C3' => ['name' => 'RAM', 'unit' => 'GB', 'direction' => 'asc'],
        'C4' => ['name' => 'Refresh Rate', 'unit' => 'Hz', 'direction' => 'asc'],
        'C5' => ['name' => 'Kapasitas Baterai', 'unit' => 'mAh', 'direction' => 'asc'],
        'C6' => ['name' => 'Fast Charging', 'unit' => 'W', 'direction' => 'asc'],
        'C7' => ['name' => 'ROM', 'unit' => 'GB', 'direction' => 'asc'],
        'C8' => ['name' => 'Kualitas Kamera', 'unit' => 'MP', 'direction' => 'asc'],
    ];

    private array $criteria = [];

    private array $weights = [];

    public function collection(Collection $rows): void
    {
        foreach (array_keys(self::METADATA) as $index => $code) {
            $row = $rows->get($index + 1);

            if ($row === null) {
                continue;
            }

            $type = strtolower(trim((string) ($row[1] ?? 'benefit'))) === 'cost' ? 'cost' : 'benefit';

            $this->criteria[$code] = [
                'code' => $code,
                'name' => self::METADATA[$code]['name'],
                'unit' => self::METADATA[$code]['unit'],
                'type' => $type,
                'direction' => $type === 'cost' ? 'desc' : 'asc',
                'sort_order' => $index + 1,
            ];

            $this->weights[$code] = (float) ($row[2] ?? 0);
        }
    }

    public function criteria(): array
    {
        return $this->criteria;
    }

    public function weights(): array
    {
        return $this->weights;
    }
}
