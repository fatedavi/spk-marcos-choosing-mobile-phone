<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Ubah Kuesioner" description="Masukkan jumlah responden untuk tiap pilihan jawaban." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')

            <form method="POST" action="{{ route('admin.questionnaire.update') }}" class="bg-white shadow rounded-lg overflow-x-auto">
                @csrf @method('PUT')
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600">Kriteria</th>
                            @foreach (['n_ss' => 'SS', 'n_s' => 'S', 'n_c' => 'C', 'n_ts' => 'TS', 'n_sts' => 'STS'] as $label)
                                <th class="px-3 py-3 text-center font-semibold text-gray-600">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($criteria as $criterion)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $criterion->code }} — {{ $criterion->name }}</td>
                                @foreach (['n_ss', 'n_s', 'n_c', 'n_ts', 'n_sts'] as $field)
                                    <td class="px-2 py-2">
                                        <input type="number" min="0" required
                                            name="{{ $criterion->id }}[{{ $field }}]"
                                            value="{{ old($criterion->id.'.'.$field, $criterion->questionnaire?->$field ?? 0) }}"
                                            class="w-20 block rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-4 flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Simpan & Hitung Ulang Bobot</button>
                    <a href="{{ route('admin.questionnaire.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
