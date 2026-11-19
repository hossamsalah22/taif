{{-- Tab bar shared by the plans, goals, lessons and rewards lists. --}}
<x-filament::tabs :label="__('Learning content')" class="fi-learning-content-tabs">
    @foreach ($tabs as $tab)
        <x-filament::tabs.item
            tag="a"
            :href="$tab['url']"
            :active="$tab['active']"
            :icon="$tab['icon']"
            :badge="$tab['count']"
        >
            {{ $tab['label'] }}
        </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
