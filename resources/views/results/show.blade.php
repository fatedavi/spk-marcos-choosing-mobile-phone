<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hasil Perhitungan MARCOS" description="Perhitungan #{{ $calculation->id }} · {{ $calculation->created_at->format('d/m/Y H:i') }} · mode {{ $calculation->price_mode }}">
            <a href="{{ route('results.export', $calculation) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">Ekspor Excel</a>
        </x-page-header>
    </x-slot>

    @php
        $codes = $result['codes'];
        $labels = $result['labels'];
        $matrix = $result['matrix'];
        $rawMatrix = $result['raw'];
        $scoreMatrix = $result['scores'];
        $weights = $result['weights'];
        $types = $result['types'];
        $alts = $output['alternatives'];
        $filters = $result['filters'] ?? [];
        $units = $result['criteria_units'] ?? [];
        $fmt = fn ($v) => number_format((float) $v, 6);
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.alerts')

            @if ($filters)
                <div class="bg-white shadow rounded-lg p-5">
                    <h3 class="font-semibold text-gray-800">Kriteria yang Diminta</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ count($result['matched_codes'] ?? []) }} HP memenuhi kriteria. Peringkat menggunakan bobot kuesioner admin.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($filters as $code => $bounds)
                            @php
                                $name = $result['criteria_names'][$code] ?? $code;
                                $unit = $units[$code] ?? '';
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
                            @endphp
                            <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">
                                {{ $code }} {{ $name }}: {{ implode(' · ', $parts) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($calculation->price_mode === 'proposal_compat')
                <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    Kriteria Harga tidak membedakan alternatif pada mode ini.
                </div>
            @endif

            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white shadow rounded-lg p-6">
                <div class="text-sm opacity-80">Rekomendasi terbaik</div>
                <div class="text-2xl font-bold mt-1">
                    {{ $recommendation['code'] }} — {{ $recommendation['name'] }}
                </div>
                <div class="mt-2 text-sm">Nilai akhir f(K<sub>i</sub>) = <strong>{{ $fmt($recommendation['f']) }}</strong></div>
            </div>

            <details class="bg-white shadow rounded-lg" open>
                <summary class="px-5 py-4 cursor-pointer font-semibold text-gray-800">1. Bobot Kriteria (dinormalisasi)</summary>
                <div class="px-5 pb-5 overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left">Kode</th><th class="px-3 py-2 text-left">Kriteria</th>
                            <th class="px-3 py-2 text-left">Jenis</th><th class="px-3 py-2 text-right">Bobot</th>
                            <th class="px-3 py-2 text-right">Persentase</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($codes as $j => $code)
                                <tr>
                                    <td class="px-3 py-2">{{ $code }}</td>
                                    <td class="px-3 py-2">{{ $result['criteria_names'][$code] ?? $code }}</td>
                                    <td class="px-3 py-2">{{ $types[$j] }}</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($weights[$code]) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($weights[$code] * 100, 4) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <details class="bg-white shadow rounded-lg">
                <summary class="px-5 py-4 cursor-pointer font-semibold text-gray-800">2. Matriks Keputusan (nilai mentah + skor 1-5)</summary>
                <div class="px-5 pb-5 overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left">Alternatif</th>
                            @foreach ($codes as $code)<th class="px-3 py-2 text-center">{{ $code }}</th>@endforeach
                        </tr></thead>
                        <tbody>
                            @foreach ($matrix as $code => $row)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $code }} — {{ $labels[$code] }}</td>
                                    @foreach ($row as $j => $value)
                                        <td class="px-3 py-2 text-center">
                                            <div>{{ number_format((float) $rawMatrix[$code][$j], 0, ',', '.') }}</div>
                                            <div class="text-indigo-600 font-semibold">skor {{ $scoreMatrix[$code][$j] }}</div>
                                            <div class="text-gray-400">x = {{ $fmt($value) }}</div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <details class="bg-white shadow rounded-lg">
                <summary class="px-5 py-4 cursor-pointer font-semibold text-gray-800">3. AI / AAI</summary>
                <div class="px-5 pb-5 overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left">Baris</th>
                            @foreach ($codes as $code)<th class="px-3 py-2 text-center">{{ $code }}</th>@endforeach
                        </tr></thead>
                        <tbody>
                            <tr><td class="px-3 py-2 font-semibold">AAI</td>
                                @foreach ($output['aai'] as $v)<td class="px-3 py-2 text-center">{{ $fmt($v) }}</td>@endforeach</tr>
                            <tr><td class="px-3 py-2 font-semibold">AI</td>
                                @foreach ($output['ai'] as $v)<td class="px-3 py-2 text-center">{{ $fmt($v) }}</td>@endforeach</tr>
                        </tbody>
                    </table>
                    <p class="text-xs text-gray-500 mt-2">S<sub>AAI</sub> = {{ $fmt($output['S_aai']) }} · S<sub>AI</sub> = {{ $fmt($output['S_ai']) }}</p>
                </div>
            </details>

            @foreach ([['4. Normalisasi', 'norm', 'n'], ['5. Matriks Berbobot', 'weighted', 'v']] as [$stepTitle, $key, $symbol])
                <details class="bg-white shadow rounded-lg">
                    <summary class="px-5 py-4 cursor-pointer font-semibold text-gray-800">{{ $stepTitle }}</summary>
                    <div class="px-5 pb-5 overflow-x-auto">
                        <table class="min-w-full text-xs divide-y divide-gray-200">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-3 py-2 text-left">Baris</th>
                                @foreach ($codes as $code)<th class="px-3 py-2 text-center">{{ $code }}</th>@endforeach
                                <th class="px-3 py-2 text-right">Jumlah (S)</th>
                            </tr></thead>
                            <tbody>
                                <tr class="bg-gray-50">
                                    <td class="px-3 py-2 font-semibold">AAI</td>
                                    @foreach ($output['ext']['AAI'][$key] as $v)<td class="px-3 py-2 text-center">{{ $fmt($v) }}</td>@endforeach
                                    <td class="px-3 py-2 text-right">{{ $fmt($output['ext']['AAI']['S']) }}</td>
                                </tr>
                                @foreach ($alts as $code => $row)
                                    <tr>
                                        <td class="px-3 py-2">{{ $code }}</td>
                                        @foreach ($row[$key] as $v)<td class="px-3 py-2 text-center">{{ $fmt($v) }}</td>@endforeach
                                        <td class="px-3 py-2 text-right">{{ $fmt($row['S']) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50">
                                    <td class="px-3 py-2 font-semibold">AI</td>
                                    @foreach ($output['ext']['AI'][$key] as $v)<td class="px-3 py-2 text-center">{{ $fmt($v) }}</td>@endforeach
                                    <td class="px-3 py-2 text-right">{{ $fmt($output['ext']['AI']['S']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="text-xs text-gray-500 mt-2">{{ $symbol }}<sub>ij</sub> ditampilkan dengan 6 desimal.</p>
                    </div>
                </details>
            @endforeach

            <details class="bg-white shadow rounded-lg" open>
                <summary class="px-5 py-4 cursor-pointer font-semibold text-gray-800">6. Nilai S, K-, K+, f(K-), f(K+), f(K<sub>i</sub>), dan Ranking</summary>
                <div class="px-5 pb-5 overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left">Peringkat</th>
                            <th class="px-3 py-2 text-left">Alternatif</th>
                            <th class="px-3 py-2 text-right">S<sub>i</sub></th>
                            <th class="px-3 py-2 text-right">K-</th>
                            <th class="px-3 py-2 text-right">K+</th>
                            <th class="px-3 py-2 text-right">f(K-)</th>
                            <th class="px-3 py-2 text-right">f(K+)</th>
                            <th class="px-3 py-2 text-right">f(K<sub>i</sub>)</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($output['ranking'] as $code)
                                @php $row = $alts[$code]; @endphp
                                <tr class="{{ $code === $recommendation['code'] ? 'bg-green-50' : '' }}">
                                    <td class="px-3 py-2 font-semibold">{{ $row['rank'] }}</td>
                                    <td class="px-3 py-2">{{ $code }} — {{ $labels[$code] }}</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($row['S']) }}</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($row['K_minus']) }}</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($row['K_plus']) }}</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($row['f_K_minus']) }}</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($row['f_K_plus']) }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-indigo-700">{{ $fmt($row['f']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="text-xs text-gray-500 mt-3">
                        f(K-) dan f(K+) sama untuk semua alternatif — sifat matematis MARCOS, bukan kesalahan.
                        Peringkat seri menggunakan pembulatan 6 desimal (1, 2, 2, 4).
                    </p>
                </div>
            </details>
        </div>
    </div>
</x-app-layout>
