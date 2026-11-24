<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hasil Kuesioner" description="Referensi jumlah responden, total skor, dan rata-rata. Bobot dihitung dari data.xlsx.">
            <a href="{{ route('admin.questionnaire.edit') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Ubah Data</a>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600">Kode</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600">Kriteria</th>
                            @foreach (['SS' => 'SS (5)', 'S' => 'S (4)', 'C' => 'C (3)', 'TS' => 'TS (2)', 'STS' => 'STS (1)'] as $key => $label)
                                <th class="px-3 py-3 text-center font-semibold text-gray-600">{{ $label }}</th>
                            @endforeach
                            <th class="px-3 py-3 text-right font-semibold text-gray-600">Total (T)</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-600">Rata-rata (R)</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-600">Bobot (referensi)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($criteria as $criterion)
                            @php
                                $q = $criterion->questionnaire;
                                $w = $weights[$criterion->code];
                            @endphp
                            <tr>
                                <td class="px-3 py-3 font-medium">{{ $criterion->code }}</td>
                                <td class="px-3 py-3">{{ $criterion->name }}</td>
                                <td class="px-3 py-3 text-center">{{ $q->n_ss ?? 0 }}</td>
                                <td class="px-3 py-3 text-center">{{ $q->n_s ?? 0 }}</td>
                                <td class="px-3 py-3 text-center">{{ $q->n_c ?? 0 }}</td>
                                <td class="px-3 py-3 text-center">{{ $q->n_ts ?? 0 }}</td>
                                <td class="px-3 py-3 text-center">{{ $q->n_sts ?? 0 }}</td>
                                <td class="px-3 py-3 text-right">{{ $w['total'] }}</td>
                                <td class="px-3 py-3 text-right">{{ number_format($w['average'], 4) }}</td>
                                <td class="px-3 py-3 text-right font-semibold text-indigo-700">{{ number_format($w['weight'], 6) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 text-sm font-semibold">
                        <tr>
                            <td colspan="7" class="px-3 py-3 text-right">Jumlah</td>
                            <td class="px-3 py-3 text-right">{{ array_sum(array_column($weights, 'total')) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format(array_sum(array_column($weights, 'average')), 4) }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format(array_sum(array_column($weights, 'weight')), 6) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-3">
                w<sub>j</sub> = R<sub>j</sub> / &Sigma;R = T<sub>j</sub> / &Sigma;T adalah nilai referensi. Bobot yang dipakai perhitungan berasal dari sheet <code>MARCOS</code> pada <code>data.xlsx</code>.
            </p>
        </div>
    </div>
</x-app-layout>
