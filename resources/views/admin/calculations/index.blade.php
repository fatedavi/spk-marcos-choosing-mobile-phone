<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Riwayat Perhitungan" description="Semua hasil perhitungan MARCOS yang tersimpan." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Pengguna</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Mode Harga</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Rekomendasi</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($calculations as $calculation)
                            @php
                                $result = $calculation->result;
                                $top = $result['output']['ranking'][0] ?? null;
                                $topName = $top ? ($result['labels'][$top] ?? $top) : '-';
                                $f = $top ? $result['output']['alternatives'][$top]['f'] ?? null : null;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">#{{ $calculation->id }}</td>
                                <td class="px-4 py-3">{{ $calculation->user->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $calculation->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $calculation->price_mode }}</td>
                                <td class="px-4 py-3">{{ $topName }} <span class="text-gray-400">({{ $f ? number_format($f, 6) : '-' }})</span></td>
                                <td class="px-4 py-3 text-right space-x-3">
                                    <a href="{{ route('results.show', $calculation) }}" class="text-indigo-600 hover:underline">Lihat</a>
                                    <a href="{{ route('results.export', $calculation) }}" class="text-emerald-600 hover:underline">Excel</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada perhitungan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
