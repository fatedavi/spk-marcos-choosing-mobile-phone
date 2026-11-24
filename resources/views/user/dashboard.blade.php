<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Beranda" description="Sistem Pendukung Keputusan Pemilihan Smartphone Gaming dengan metode MARCOS." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.alerts')

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800">Selamat datang, {{ Auth::user()->name }}</h3>
                <p class="text-sm text-gray-600 mt-2">
                    Tersedia {{ $counts['alternatives'] }} smartphone dan {{ $counts['criteria'] }} kriteria untuk dibandingkan.
                    Masukkan kriteria yang diinginkan, lalu lihat HP yang cocok beserta peringkatnya.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('user.weighting.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Cari HP Sesuai Kriteria</a>
                    <a href="{{ route('user.alternatives.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Lihat Daftar HP</a>
                </div>
            </div>

            @if (config('spk.price_mode') === 'proposal_compat')
                <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    Kriteria Harga tidak membedakan alternatif pada mode ini.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
