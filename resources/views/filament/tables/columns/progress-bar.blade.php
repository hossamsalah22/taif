<div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
    @php
        $progress = (int) $getState();
        $color = $progress < 20 ? 'bg-red-600' : ($progress < 80 ? 'bg-yellow-400' : 'bg-green-500');
    @endphp
    <div class="{{ $color }} h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
</div>
<span class="text-xs text-gray-500">{{ $progress }}%</span>
