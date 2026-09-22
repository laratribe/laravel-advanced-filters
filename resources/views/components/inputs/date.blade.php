{{-- Date value input (input "date") --}}
<template x-if="selectedField().input === 'date'">
    <div class="{{ $prefix }}-input-group" data-af-part="input">
        <input
            type="date"
            class="{{ $prefix }}-input"
            x-model="pending.value"
            :aria-label="isBetween() ? 'From' : 'Date'"
        >
        <template x-if="isBetween()">
            <input
                type="date"
                class="{{ $prefix }}-input"
                x-model="pending.valueTo"
                aria-label="To"
            >
        </template>
    </div>
</template>
