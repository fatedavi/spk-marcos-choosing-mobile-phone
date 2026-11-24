<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard Admin" description="Ringkasan data pendukung SPK Pemilihan Smartphone Gaming." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.alerts')

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([['Kriteria', $counts['criteria'], 'Indigo'], ['Subkriteria', \App\Models\SubCriterion::count(), 'Sky'], ['Alternatif', $counts['alternatives'], 'Emerald'], ['Perhitungan', $counts['calculations'], 'Amber']] as [$label, $value, $color])
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="text-sm text-gray-500">{{ $label }}</div>
                        <div class="text-3xl font-bold text-gray-800 mt-2">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Aksi cepat</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.criteria.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Kelola Kriteria</a>
                    <a href="{{ route('admin.alternatives.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Kelola Alternatif</a>
                    <a href="{{ route('admin.questionnaire.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Lihat Kuesioner</a>
                    <a href="{{ route('admin.calculations.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Riwayat Hasil</a>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    Mode harga aktif: <strong>{{ config('spk.price_mode') }}</strong>.
                    @if (config('spk.price_mode') === 'proposal_compat')
                        <span class="text-red-600"> Kriteria Harga tidak membedakan alternatif pada mode ini.</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
