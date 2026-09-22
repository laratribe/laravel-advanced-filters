{{-- Tailwind themed date input --}}
@php($afInput = 'w-full rounded-md border border-gray-300 px-2 py-1.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500')
<template x-if="selectedField().input === 'date'">
    <div class="flex flex-col gap-2" data-af-part="input">
        <input type="date" class="{{ $afInput }}" x-model="pending.value" :aria-label="isBetween() ? 'From' : 'Date'">
        <template x-if="isBetween()">
            <input type="date" class="{{ $afInput }}" x-model="pending.valueTo" aria-label="To">
        </template>
    </div>
</template>
