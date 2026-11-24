<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if (isset($alternative)) @method('PUT') @endif

    <div class="bg-white shadow rounded-lg p-6 grid md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700">Kode (A1, A2, ...)</label>
            <input type="text" name="code" required value="{{ old('code', $alternative->code ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Nama Smartphone</label>
            <input type="text" name="name" required value="{{ old('name', $alternative->name ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Brand</label>
            <input type="text" name="brand" value="{{ old('brand', $alternative->brand ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Kelas</label>
            <input type="text" name="kelas" value="{{ old('kelas', $alternative->kelas ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Nilai Mentah per Kriteria</h3>
        <div class="grid md:grid-cols-2 gap-5">
            @foreach ($criteria as $criterion)
                @php
                    $existing = isset($alternative) ? $alternative->values->firstWhere('criteria_id', $criterion->id) : null;
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ $criterion->code }} — {{ $criterion->name }} ({{ $criterion->unit ?: '-' }})
                    </label>
                    <input type="number" step="0.01" min="0.01" required
                        name="values[{{ $criterion->id }}]"
                        value="{{ old('values.'.$criterion->id, $existing->raw_value ?? 0) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">{{ $label }}</button>
        <a href="{{ route('admin.alternatives.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Batal</a>
    </div>
</form>
