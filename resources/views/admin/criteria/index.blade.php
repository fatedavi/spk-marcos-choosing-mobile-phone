<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kriteria" description="Kelola kriteria penilaian (C1..C8), jenis, dan bobot awal.">
            <a href="{{ route('admin.criteria.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Tambah Kriteria</a>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Kode</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Satuan</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Jenis</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Arah</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Bobot Awal</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($criteria as $criterion)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $criterion->code }}</td>
                                <td class="px-4 py-3">{{ $criterion->name }}</td>
                                <td class="px-4 py-3">{{ $criterion->unit ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $criterion->type }}</td>
                                <td class="px-4 py-3">{{ $criterion->direction }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $criterion->default_weight, 6) }}</td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.sub-criteria.index', $criterion) }}" class="text-sky-600 hover:underline">Subkriteria</a>
                                    <a href="{{ route('admin.criteria.edit', $criterion) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.criteria.destroy', $criterion) }}" class="inline" onsubmit="return confirm('Hapus kriteria ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada kriteria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
