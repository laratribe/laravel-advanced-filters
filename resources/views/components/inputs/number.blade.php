{{-- Numeric value input (input "number") --}}
<template x-if="selectedField().input === 'number'">
    <div class="{{ $prefix }}-input-group" data-af-part="input">
        <input
            type="number"
            class="{{ $prefix }}-input"
            x-model="pending.value"
            :placeholder="isBetween() ? 'From' : 'Value'"
            @keydown.enter.prevent="addPending()"
        >
        <template x-if="isBetween()">
            <input
                type="number"
                class="{{ $prefix }}-input"
                x-model="pending.valueTo"
                placeholder="To"
                @keydown.enter.prevent="addPending()"
            >
        </template>
    </div>
</template>
