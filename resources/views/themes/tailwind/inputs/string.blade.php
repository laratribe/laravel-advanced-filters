{{-- Tailwind themed text input --}}
@php($afInput = 'w-full rounded-md border border-gray-300 px-2 py-1.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500')
<template x-if="selectedField().input === 'string'">
    <div class="flex flex-col gap-2" data-af-part="input">
        <template x-if="supportsMultilineOr()">
            <textarea class="{{ $afInput }} resize-y" x-model="pending.value" rows="2" placeholder="One value per line = OR (Shift+Enter for new line)" @keydown.enter.exact.prevent="addPending()"></textarea>
        </template>
        <template x-if="!supportsMultilineOr()">
            <input type="text" class="{{ $afInput }}" x-model="pending.value" placeholder="Value" @keydown.enter.prevent="addPending()">
        </template>
    </div>
</template>
