{{-- A host app's own value input for a custom "boolean" filter type. --}}
<template x-if="selectedField().input === 'boolean'">
    <div class="{{ $prefix }}-input-group" data-af-part="input">
        <select class="{{ $prefix }}-select" x-model="pending.value">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
</template>
