<?php

namespace Laratribe\AdvancedFilters\Tests\Fixtures;

use Laratribe\AdvancedFilters\Livewire\Panel;

/**
 * A host app extending the packaged Livewire panel: keep the engine (validation,
 * dispatching, server-derived field definitions), change the markup and add behaviour.
 */
class CustomPanel extends Panel
{
    public bool $panelOpen = false;

    public function toggle(): void
    {
        $this->panelOpen = ! $this->panelOpen;
    }

    /** Drop every row for one field — the sort of thing the packaged panel doesn't do. */
    public function clearField(string $field): void
    {
        $this->active = array_values(array_filter(
            $this->active,
            fn (array $row) => $row['field'] !== $field,
        ));

        $this->dispatch('advanced-filters-updated', filters: $this->active);
    }

    public function render()
    {
        return view('af-test::custom-livewire-panel', [
            'fields' => $this->fields(),
        ]);
    }
}
