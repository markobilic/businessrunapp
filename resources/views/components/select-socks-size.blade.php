@props(['selected', 'runnerId', 'optionsSocksSize'])
<div>
    <select class="w-full" wire:change="socksSizeChanged($event.target.value, {{ $runnerId }})">
            @if(!$selected)
                <option value="">...</option>
            @endif
            @foreach ($optionsSocksSize as $id => $name)
                <option
                    value="{{ $id }}"
                    @if ($id == $selected)
                        selected="selected"
                    @endif
                >
                    {{ $name }}
                </option>
            @endforeach
    </select>
</div>