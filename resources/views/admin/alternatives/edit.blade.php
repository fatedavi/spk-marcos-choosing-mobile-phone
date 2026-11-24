<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Alternatif" description="Ubah data {{ $alternative->code }} — {{ $alternative->name }}." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')
            @include('admin.alternatives._form', [
                'action' => route('admin.alternatives.update', $alternative),
                'label' => 'Perbarui',
                'criteria' => $criteria,
                'alternative' => $alternative,
            ])
        </div>
    </div>
</x-app-layout>
