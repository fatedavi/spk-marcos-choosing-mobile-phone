<form method="POST" action="{{ $action }}" class="bg-white shadow rounded-lg p-6 space-y-5">
    @csrf
    @if ($criterion) @method('PUT') @endif

    <div class="grid md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700">Kode</label>
            <input type="text" name="code" value="{{ old('code', $criterion->code ?? '') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Nama Kriteria</label>
            <input type="text" name="name" value="{{ old('name', $criterion->name ?? '') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Satuan</label>
            <input type="text" name="unit" value="{{ old('unit', $criterion->unit ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Jenis</label>
            <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                @foreach (['benefit' => 'Benefit', 'cost' => 'Cost'] as $value => $text)
                    <option value="{{ $value }}" @selected(old('type', $criterion->type ?? 'benefit') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Arah Skala</label>
            <select name="direction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                @foreach (['asc' => 'Naik (makin besar makin baik)', 'desc' => 'Turun (makin kecil makin baik)'] as $value => $text)
                    <option value="{{ $value }}" @selected(old('direction', $criterion->direction ?? 'asc') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Bobot Awal (0 - 1)</label>
            <input type="number" step="0.00000001" min="0" max="1" name="default_weight"
                value="{{ old('default_weight', $criterion->default_weight ?? 0) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Urutan</label>
            <input type="number" min="1" name="sort_order"
                value="{{ old('sort_order', $criterion->sort_order ?? 1) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">{{ $label }}</button>
        <a href="{{ route('admin.criteria.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Batal</a>
    </div>
</form>
