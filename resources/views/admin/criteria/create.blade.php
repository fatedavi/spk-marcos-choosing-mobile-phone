<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Kriteria" description="Buat kriteria baru untuk penilaian." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')
            @include('admin.criteria._form', ['action' => route('admin.criteria.store'), 'label' => 'Simpan', 'criterion' => null])
        </div>
    </div>
</x-app-layout>
