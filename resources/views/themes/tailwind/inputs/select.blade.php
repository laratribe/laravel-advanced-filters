{{-- Tailwind themed select / set input --}}
@php($afInput = 'w-full rounded-md border border-gray-300 px-2 py-1.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500')
<template x-if="selectedField().input === 'select'">
    <div class="flex flex-col gap-2" data-af-part="input">
        <template x-if="!isMultiValue()">
            <select class="{{ $afInput }}" x-model="pending.value">
                <option value="">&mdash;</option>
                <template x-for="opt in optionItems()" :key="opt.value">
                    <option :value="opt.value" x-text="opt.label"></option>
                </template>
            </select>
        </template>
        <template x-if="isMultiValue()">
            <select class="{{ $afInput }} min-h-24" multiple x-model="pending.value">
                <template x-for="opt in optionItems()" :key="opt.value">
                    <option :value="opt.value" x-text="opt.label"></option>
                </template>
            </select>
        </template>
    </div>
</template>
