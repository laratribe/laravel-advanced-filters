{{-- Text value input (input "string") --}}
<template x-if="selectedField().input === 'string'">
    <div class="{{ $prefix }}-input-group" data-af-part="input">
        {{-- multi-line OR for positive clauses (contains / starts_with / ends_with / equals) --}}
        <template x-if="supportsMultilineOr()">
            <textarea
                class="{{ $prefix }}-textarea"
                x-model="pending.value"
                rows="2"
                placeholder="One value per line = OR (Shift+Enter for new line)"
                @keydown.enter.exact.prevent="addPending()"
            ></textarea>
        </template>

        {{-- single-line text --}}
        <template x-if="!supportsMultilineOr()">
            <input
                type="text"
                class="{{ $prefix }}-input"
                x-model="pending.value"
                placeholder="Value"
                @keydown.enter.prevent="addPending()"
            >
        </template>
    </div>
</template>
