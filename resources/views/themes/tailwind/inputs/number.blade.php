{{-- Tailwind themed numeric input --}}
@php($afInput = 'w-full rounded-md border border-gray-300 px-2 py-1.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500')
<template x-if="selectedField().input === 'number'">
    <div class="flex flex-col gap-2" data-af-part="input">
        <input type="number" class="{{ $afInput }}" x-model="pending.value" :placeholder="isBetween() ? 'From' : 'Value'" @keydown.enter.prevent="addPending()">
        <template x-if="isBetween()">
            <input type="number" class="{{ $afInput }}" x-model="pending.valueTo" placeholder="To" @keydown.enter.prevent="addPending()">
        </template>
    </div>
</template>
