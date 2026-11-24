<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Alternatif" description="Tambah smartphone beserta nilai mentah tiap kriteria." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')
            @include('admin.alternatives._form', [
                'action' => route('admin.alternatives.store'),
                'label' => 'Simpan',
                'criteria' => $criteria,
            ])
        </div>
    </div>
</x-app-layout>
