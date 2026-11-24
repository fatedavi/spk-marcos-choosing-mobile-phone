<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Kriteria" description="Ubah data kriteria {{ $criterion->code }}." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')
            @include('admin.criteria._form', ['action' => route('admin.criteria.update', $criterion), 'label' => 'Perbarui', 'criterion' => $criterion])
        </div>
    </div>
</x-app-layout>
