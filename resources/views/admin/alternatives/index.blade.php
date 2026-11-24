<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Alternatif" description="Kelola smartphone dan nilai mentah tiap kriteria. Skor 1-5 dihitung otomatis.">
            <a href="{{ route('comparison.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">Bandingkan HP</a>
            <a href="{{ route('admin.alternatives.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Tambah Alternatif</a>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600 sticky left-0 bg-gray-50">Kode</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600 sticky left-14 bg-gray-50">Nama</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600">Brand</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600">Kelas</th>
                            @foreach ($criteria as $criterion)
                                <th class="px-3 py-3 text-center font-semibold text-gray-600">{{ $criterion->code }}<br><span class="font-normal text-gray-400">{{ $criterion->unit }}</span></th>
                            @endforeach
                            <th class="px-3 py-3 text-right font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($alternatives as $alternative)
                            <tr>
                                <td class="px-3 py-3 font-medium sticky left-0 bg-white">{{ $alternative->code }}</td>
                                <td class="px-3 py-3 whitespace-nowrap sticky left-14 bg-white">{{ $alternative->name }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $alternative->brand ?: '-' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $alternative->kelas ?: '-' }}</td>
                                @foreach ($criteria as $criterion)
                                    @php $value = $alternative->values->firstWhere('criteria_id', $criterion->id); @endphp
                                    <td class="px-3 py-3 text-center">
                                        <div>{{ $value ? number_format((float) $value->raw_value, 0, ',', '.') : '-' }}</div>
                                        <div class="text-indigo-600 font-semibold">skor {{ $value->score ?? '-' }}</div>
                                    </td>
                                @endforeach
                                <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.alternatives.edit', $alternative) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.alternatives.destroy', $alternative) }}" class="inline" onsubmit="return confirm('Hapus alternatif ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $criteria->count() + 5 }}" class="px-4 py-6 text-center text-gray-500">Belum ada alternatif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
