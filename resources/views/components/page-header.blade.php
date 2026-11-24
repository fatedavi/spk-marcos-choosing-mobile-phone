@props(['title', 'description' => null])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
        @if ($description)
            <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
