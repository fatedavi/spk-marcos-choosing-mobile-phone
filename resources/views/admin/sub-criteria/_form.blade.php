<form method="POST" action="{{ $action }}" class="bg-white shadow rounded-lg p-6 space-y-5">
    @csrf
    @if (isset($subCriterion)) @method('PUT') @endif

    <div class="grid md:grid-cols-3 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700">Skor (1-5)</label>
            <input type="number" min="1" max="5" name="score" required
                value="{{ old('score', $subCriterion->score ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Batas Bawah (min_value)</label>
            <input type="number" step="0.01" name="min_value"
                value="{{ old('min_value', $subCriterion->min_value ?? 0) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <p class="text-xs text-gray-500 mt-1">Kosongkan hanya jika tidak ada batas bawah.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Label Rentang</label>
            <input type="text" name="label" value="{{ old('label', $subCriterion->label ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">{{ $label }}</button>
        <a href="{{ route('admin.sub-criteria.index', $criterion) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Batal</a>
    </div>
</form>
