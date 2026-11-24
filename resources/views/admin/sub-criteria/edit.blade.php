<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Subkriteria" description="Kriteria {{ $criterion->code }} — {{ $criterion->name }}" />
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')
            @include('admin.sub-criteria._form', [
                'action' => route('admin.sub-criteria.update', $subCriterion),
                'label' => 'Perbarui',
                'criterion' => $criterion,
                'subCriterion' => $subCriterion,
            ])
        </div>
    </div>
</x-app-layout>
