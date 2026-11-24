<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Cari HP Sesuai Kriteria" description="Masukkan rentang kriteria yang diinginkan. HP yang memenuhi akan diurutkan dengan metode MARCOS." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.alerts')

            @if ($priceMode === 'proposal_compat')
                <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    Kriteria Harga tidak membedakan alternatif pada mode ini.
                </div>
            @endif

            <form method="POST" action="{{ route('user.weighting.calculate') }}" class="space-y-6">
                @csrf

                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <h3 class="font-semibold text-gray-800">Rentang Kriteria yang Diinginkan</h3>
                        <p class="text-xs text-gray-500 mt-1">Isi minimum dan/atau maksimum untuk tiap kriteria. Kosongkan jika tidak peduli.</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($criteria as $criterion)
                            <div class="px-4 py-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
                                <div class="md:col-span-2">
                                    <div class="font-medium text-gray-800">{{ $criterion->code }} — {{ $criterion->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Satuan {{ $criterion->unit ?: '-' }} · bobot kriteria {{ number_format((float) $criterion->default_weight * 100, 4) }}%
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Minimum</label>
                                    <input type="number" min="0" step="any"
                                        class="block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        name="filters[{{ $criterion->code }}][min]"
                                        placeholder="{{ $criterion->code === 'C1' ? '5000000' : '8' }}"
                                        value="{{ old('filters.'.$criterion->code.'.min') }}">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Maksimum</label>
                                    <input type="number" min="0" step="any"
                                        class="block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        name="filters[{{ $criterion->code }}][max]"
                                        placeholder="{{ $criterion->code === 'C1' ? '15000000' : '16' }}"
                                        value="{{ old('filters.'.$criterion->code.'.max') }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <h3 class="font-semibold text-gray-800">Batasi ke HP Tertentu (opsional)</h3>
                        <p class="text-xs text-gray-500 mt-1">Default: semua alternatif. Minimal pilih 2.</p>
                    </div>
                    <div class="p-4 grid md:grid-cols-2 gap-3">
                        @foreach ($alternatives as $alternative)
                            <label class="flex items-center gap-3 p-3 border rounded-md hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="alternatives[]" value="{{ $alternative->code }}"
                                    {{ in_array($alternative->code, old('alternatives', $alternatives->pluck('code')->all())) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span class="text-sm">
                                    <strong>{{ $alternative->code }}</strong> — {{ $alternative->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <button type="submit" class="px-5 py-3 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                        Cari HP & Hitung Peringkat
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
