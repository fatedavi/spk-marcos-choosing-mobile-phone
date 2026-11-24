<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Subkriteria" description="Atur rentang nilai mentah menjadi skor 1-5 untuk setiap kriteria.">
            <a href="{{ route('admin.sub-criteria.create', $criterion) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Tambah Subkriteria</a>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-5">
            @include('partials.alerts')

            <div class="flex flex-wrap gap-2">
                @foreach ($criteria as $item)
                    <a href="{{ route('admin.sub-criteria.index', $item) }}"
                        class="px-3 py-2 rounded-md text-sm border {{ $item->id === $criterion->id ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:border-indigo-300' }}">
                        {{ $item->code }} · {{ $item->name }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b text-sm text-gray-600">
                    Kriteria <strong>{{ $criterion->code }} — {{ $criterion->name }}</strong>
                    ({{ $criterion->direction === 'desc' ? 'makin kecil makin baik' : 'makin besar makin baik' }})
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Skor</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Batas Bawah (min_value)</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Label Rentang</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($criterion->subCriteria as $sub)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $sub->score }}</td>
                                <td class="px-4 py-3">{{ $sub->min_value === null ? '-' : number_format((float) $sub->min_value, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $sub->label ?: '-' }}</td>
                                <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('admin.sub-criteria.edit', $sub) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.sub-criteria.destroy', $sub) }}" class="inline" onsubmit="return confirm('Hapus subkriteria ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada subkriteria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500">
                Aturan: skor = skor dengan <code>min_value</code> terbesar yang masih &lt;= nilai mentah.
                Untuk Harga, simpan batas bawah tiap rentang (0; 3.000.000; 5.000.000; 7.000.000; 10.000.000).
            </p>
        </div>
    </div>
</x-app-layout>
