<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Bandingkan Smartphone" description="Pilih 2-4 HP untuk melihat perbedaan nilai, skor, dan keunggulan tiap kriteria." />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.alerts')

            <form method="GET" action="{{ route('comparison.index') }}" class="bg-white shadow rounded-lg">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <h3 class="font-semibold text-gray-800">Pilih HP (2-4)</h3>
                    <p class="text-xs text-gray-500 mt-1">Centang HP yang ingin dibandingkan.</p>
                </div>
                <div class="p-4 grid md:grid-cols-3 gap-2">
                    @foreach ($alternatives as $alternative)
                        <label class="flex items-center gap-2 p-2 border rounded-md hover:bg-gray-50 cursor-pointer text-sm">
                            <input type="checkbox" name="alternatives[]" value="{{ $alternative->code }}"
                                {{ in_array($alternative->code, old('alternatives', $selectedCodes), true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span><strong>{{ $alternative->code }}</strong> — {{ $alternative->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('alternatives')
                    <p class="px-4 pb-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('alternatives.*')
                    <p class="px-4 pb-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="px-4 py-3 border-t">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Tampilkan Perbandingan</button>
                </div>
            </form>

            @if (! $result)
                <p class="text-sm text-gray-500">Pilih minimal 2 HP untuk menampilkan perbandingan.</p>
            @endif

            @if ($result)
                <div class="bg-white shadow rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600">Kriteria</th>
                                @foreach ($result['codes'] as $code)
                                    <th class="px-3 py-3 text-center font-semibold text-gray-600">
                                        {{ $code }}<br><span class="font-normal text-gray-400">{{ $result['labels'][$code] }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($result['rows'] as $row)
                                <tr>
                                    <td class="px-3 py-3 font-medium whitespace-nowrap">
                                        {{ $row['code'] }} — {{ $row['name'] }}
                                        <div class="font-normal text-gray-400">{{ $row['unit'] ?: '-' }} · {{ $row['type'] }} · {{ number_format($row['weight'] * 100, 4) }}%</div>
                                    </td>
                                    @foreach ($result['codes'] as $code)
                                        @php
                                            $cell = $row['cells'][$code];
                                            $raw = $row['code'] === 'C1'
                                                ? 'Rp '.number_format($cell['raw'], 0, ',', '.')
                                                : number_format($cell['raw'], 0, ',', '.').' '.($row['unit'] ?: '');
                                        @endphp
                                        <td class="px-3 py-3 text-center">
                                            <div>{{ $raw }}</div>
                                            <div class="text-indigo-600 font-semibold">skor {{ $cell['score'] }}</div>
                                            @if ($cell['state'] === 'unggul')
                                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px]">Unggul</span>
                                            @elseif ($cell['state'] === 'sama')
                                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px]">Sama</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-3 py-3">Rata-rata skor</td>
                                @foreach ($result['codes'] as $code)
                                    <td class="px-3 py-3 text-center">{{ number_format($result['summary'][$code]['average'], 4) }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-3 py-3">Kriteria terbaik</td>
                                @foreach ($result['codes'] as $code)
                                    <td class="px-3 py-3 text-center">{{ $result['summary'][$code]['wins'] }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-3 py-3">Skor terbobot</td>
                                @foreach ($result['codes'] as $code)
                                    <td class="px-3 py-3 text-center">{{ number_format($result['summary'][$code]['weighted'], 6) }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <p class="text-xs text-gray-500">Skor terbobot adalah ringkasan skor 1-5 dikali bobot kriteria, bukan peringkat MARCOS.</p>
                    <form method="POST" action="{{ route('user.weighting.calculate') }}" class="mt-3">
                        @csrf
                        @foreach ($result['codes'] as $code)
                            <input type="hidden" name="alternatives[]" value="{{ $code }}">
                        @endforeach
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Hitung MARCOS untuk HP Terpilih</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
