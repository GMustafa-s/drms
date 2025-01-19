<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ 
        start: @entangle('start_date'),
        end: @entangle('end_date'),
        compareMode: @entangle('compare_mode')
    }">
        <div class="space-y-4">
            <div class="flex space-x-4">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="date"
                        wire:model="start_date"
                        :placeholder="__('Start Date')"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper>
                    <x-filament::input
                        type="date"
                        wire:model="end_date"
                        :placeholder="__('End Date')"
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="flex space-x-2">
                @foreach($getPresets() as $key => $preset)
                    <button
                        type="button"
                        wire:click="$set('start_date', '{{ $preset['start']->format('Y-m-d') }}'), $set('end_date', '{{ $preset['end']->format('Y-m-d') }}')"
                        class="px-3 py-1 text-sm font-medium rounded-md text-gray-700 bg-white border border-gray-300 hover:bg-gray-50"
                    >
                        {{ $preset['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="mt-4">
                <x-filament::select
                    wire:model="compare_mode"
                    :options="$getComparisonModes()"
                    :placeholder="__('Compare with...')"
                />
            </div>
        </div>
    </div>
</x-dynamic-component>
