{{-- Value input for the app-defined "boolean" filter type. Self-gates on the field's
     `input` name, exactly like the partials the package ships. --}}
<template x-if="selectedField().input === 'boolean'">
    <div class="{{ $prefix }}-input-group" data-af-part="input">
        <select class="{{ $prefix }}-select" x-model="pending.value">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
</template>
