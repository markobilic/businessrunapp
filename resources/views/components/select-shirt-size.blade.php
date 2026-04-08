@props(['selected', 'runnerId', 'optionsShirtSize'])
<div>
    <select class="w-full" wire:change="shirtSizeChanged($event.target.value, {{ $runnerId }})">
            @if(!$selected)
                <option value="">...</option>
            @endif
            @foreach ($optionsShirtSize as $id => $name)
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