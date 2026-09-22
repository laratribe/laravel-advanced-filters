{{-- Markup owned entirely by the host app; the component it belongs to extends the
     packaged Livewire panel, so validation and dispatching still come from the package. --}}
<div class="my-custom-livewire-panel">
    <button type="button" wire:click="toggle">{{ $panelOpen ? 'Hide' : 'Show' }} filters</button>

    @foreach ($active as $i => $row)
        <span class="my-chip" wire:key="chip-{{ $i }}">
            {{ $row['field'] }} {{ $row['operator'] }}
            <button type="button" wire:click="removeFilter({{ $i }})">&times;</button>
            <button type="button" wire:click="clearField('{{ $row['field'] }}')">clear column</button>
        </span>
    @endforeach

    @if ($panelOpen)
        <ul class="my-field-list">
            @foreach ($fields as $field)
                <li>{{ $field['label'] }} ({{ $field['input'] }})</li>
            @endforeach
        </ul>
    @endif
</div>
