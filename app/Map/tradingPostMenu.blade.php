<div style="min-width:200px">
    <div class="flex justify-between my-2">
        <span class="mx-2">Selling 4 </span>
        <select name="trader-input" wire:model="form.input">
            @foreach(\App\Map\Tile\ResourceTile\Resource::cases() as $resource)
                <option value="{{ $resource->value }}">
                    {{ $resource->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex justify-between my-2">
        <span class="mx-2">For 1</span>
        <select name="trader-output" wire:model="form.output">
            @foreach(\App\Map\Tile\ResourceTile\Resource::cases() as $resource)
                <option value="{{ $resource->value }}">
                    {{ $resource->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="my-2 flex justify-end">
        <button class="mx-2" wire:click="closeMenu()">Close</button>
        <button class="mx-2" wire:click="saveMenu()">Save</button>
    </div>
</div>
