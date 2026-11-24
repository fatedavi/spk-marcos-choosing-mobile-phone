<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Daftar Smartphone" description="Nilai mentah dan skor 1-5 untuk tiap kriteria." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')

            <form method="GET" action="{{ route('comparison.index') }}" class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center font-semibold text-gray-600">Bandingkan</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600">Kode</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600">Nama Smartphone</th>
                            @foreach ($criteria as $criterion)
                                <th class="px-3 py-3 text-center font-semibold text-gray-600">{{ $criterion->code }}<br><span class="font-normal text-gray-400">{{ $criterion->name }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($alternatives as $alternative)
                            <tr>
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" name="alternatives[]" value="{{ $alternative->code }}" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                </td>
                                <td class="px-3 py-3 font-medium">{{ $alternative->code }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $alternative->name }}</td>
                                @foreach ($criteria as $criterion)
                                    @php $value = $alternative->values->firstWhere('criteria_id', $criterion->id); @endphp
                                    <td class="px-3 py-3 text-center">
                                        <div>{{ $value ? number_format((float) $value->raw_value, 0, ',', '.') : '-' }}</div>
                                        <div class="text-indigo-600 font-semibold">{{ $value->score ?? '-' }}</div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Bandingkan yang Dipilih (2-4 HP)</button>
                    <a href="{{ route('user.weighting.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">Cari HP Sesuai Kriteria</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
