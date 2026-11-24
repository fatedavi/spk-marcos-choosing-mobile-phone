<?php

namespace App\Exports;

use App\Exports\Sheets\ComparisonSheet;
use App\Models\Calculation;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MarcosResultExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private Calculation $calculation) {}

    public function sheets(): array
    {
        $result = $this->calculation->result;
        $output = $result['output'];
        $codes = $result['codes'];
        $labels = $result['labels'];
        $weights = $result['weights'];
        $types = $result['types'];
        $matrix = $result['matrix'];
        $raw = $result['raw'];
        $scores = $result['scores'];
        $alternatives = $output['alternatives'];
        $ranking = $output['ranking'];
        $top = $ranking[0] ?? null;
        $format = fn ($value) => round((float) $value, 6);

        $summary = [
            ['ID Perhitungan', $this->calculation->id],
            ['Waktu', $this->calculation->created_at?->format('d/m/Y H:i')],
            ['Pengguna', $this->calculation->user->name ?? '-'],
            ['Mode Harga', $this->calculation->price_mode],
            ['Rekomendasi', $top ? $top.' — '.$labels[$top] : '-'],
            ['f(K_i) terbaik', $top ? $format($alternatives[$top]['f']) : '-'],
        ];
        if (! empty($result['filters'])) {
            $summary[] = ['HP yang memenuhi kriteria', count($result['matched_codes'] ?? [])];
            $summary[] = ['Sumber bobot', 'Kuesioner admin'];
            foreach ($result['filters'] as $code => $bounds) {
                $name = $result['criteria_names'][$code] ?? $code;
                $unit = $result['criteria_units'][$code] ?? '';
                $index = array_search($code, $codes, true);
                $type = $index === false ? 'benefit' : $types[$index];
                if (! is_array($bounds)) {
                    $bounds = [$type === 'cost' ? 'max' : 'min' => $bounds];
                }
                $formatBound = function ($value) use ($code, $unit) {
                    $display = number_format((float) $value, 0, ',', '.');

                    return $code === 'C1'
                        ? 'Rp '.$display
                        : $display.($unit ? ' '.$unit : '');
                };
                $parts = [];
                if (array_key_exists('min', $bounds)) {
                    $parts[] = 'min '.$formatBound($bounds['min']);
                }
                if (array_key_exists('max', $bounds)) {
                    $parts[] = 'maks '.$formatBound($bounds['max']);
                }
                $summary[] = ['Rentang '.$code.' '.$name, implode(' · ', $parts)];
            }
        }
        foreach ($codes as $code) {
            $summary[] = ['Bobot '.$code, $format($weights[$code])];
            $summary[] = ['Persentase '.$code, number_format($weights[$code] * 100, 4).'%'];
        }

        $matrixHeadings = ['Alternatif', 'Nama'];
        foreach ($codes as $code) {
            $matrixHeadings[] = $code.' nilai mentah';
            $matrixHeadings[] = $code.' skor';
            $matrixHeadings[] = $code.' x';
        }
        $matrixRows = [];
        foreach ($matrix as $code => $row) {
            $cells = [$code, $labels[$code] ?? $code];
            foreach ($row as $j => $value) {
                $cells[] = $raw[$code][$j];
                $cells[] = $scores[$code][$j];
                $cells[] = $format($value);
            }
            $matrixRows[] = $cells;
        }

        $aiAaiRows = [
            array_merge(['AAI', ...array_map($format, $output['aai'])], [$format($output['S_aai'])]),
            array_merge(['AI', ...array_map($format, $output['ai'])], [$format($output['S_ai'])]),
        ];

        $normalRows = [
            array_merge(['AAI', ...array_map($format, $output['ext']['AAI']['norm'])], [$format($output['ext']['AAI']['S'])]),
        ];
        $weightedRows = [
            array_merge(['AAI', ...array_map($format, $output['ext']['AAI']['weighted'])], [$format($output['ext']['AAI']['S'])]),
        ];
        foreach ($ranking as $code) {
            $normalRows[] = array_merge([$code, ...array_map($format, $alternatives[$code]['norm'])], [$format($alternatives[$code]['S'])]);
            $weightedRows[] = array_merge([$code, ...array_map($format, $alternatives[$code]['weighted'])], [$format($alternatives[$code]['S'])]);
        }
        $normalRows[] = array_merge(['AI', ...array_map($format, $output['ext']['AI']['norm'])], [$format($output['ext']['AI']['S'])]);
        $weightedRows[] = array_merge(['AI', ...array_map($format, $output['ext']['AI']['weighted'])], [$format($output['ext']['AI']['S'])]);

        $utilityRows = [];
        foreach ($ranking as $code) {
            $row = $alternatives[$code];
            $utilityRows[] = [
                $row['rank'], $code, $labels[$code] ?? $code,
                $format($row['S']), $format($row['K_minus']), $format($row['K_plus']),
                $format($row['f_K_minus']), $format($row['f_K_plus']), $format($row['f']),
            ];
        }

        $codeHeadings = ['Baris', ...$codes, 'S'];
        $criteriaRows = [];
        foreach ($codes as $j => $code) {
            $criteriaRows[] = [$code, $result['criteria_names'][$code] ?? $code, $types[$j]];
        }

        return [
            new ArraySheet('Ringkasan', ['Keterangan', 'Nilai'], $summary),
            new ArraySheet('Kriteria', ['Kode', 'Kriteria', 'Jenis'], $criteriaRows),
            new ArraySheet('Matriks', $matrixHeadings, $matrixRows),
            new ArraySheet('AI_AAI', $codeHeadings, $aiAaiRows),
            new ArraySheet('Normalisasi', $codeHeadings, $normalRows),
            new ArraySheet('Berbobot', $codeHeadings, $weightedRows),
            new ArraySheet('S_K_F', ['Peringkat', 'Alternatif', 'Nama', 'S', 'K-', 'K+', 'f(K-)', 'f(K+)', 'f(K_i)'], $utilityRows),
            new ComparisonSheet($ranking, array_map(fn ($code) => $format($alternatives[$code]['f']), array_combine($ranking, $ranking))),
        ];
    }
}
