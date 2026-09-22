<script setup>
/**
 * A macOS-style code window.
 *
 * The sample arrives as an HTML string via `code` rather than slot content, because
 * Vue's template compiler condenses whitespace — newlines written inside a <pre> in a
 * template are stripped, and the whole sample collapses onto one line. Passing it as
 * data and rendering with v-html preserves it. The content is authored here, never
 * user input.
 */
defineProps({
    code: { type: String, required: true },
    name: { type: String, default: '' },
    // Tighter type and padding, for the three-up steps grid.
    compact: { type: Boolean, default: false },
})
</script>

<template>
	<div class="cw" :class="{ 'cw--compact': compact }">
		<div class="cw__bar">
			<span class="cw__dot cw__dot--r" />
			<span class="cw__dot cw__dot--a" />
			<span class="cw__dot cw__dot--g" />
			<span v-if="name" class="cw__name">{{ name }}</span>
		</div>

		<pre class="cw__code"><code v-html="code" /></pre>

		<div v-if="$slots.footer" class="cw__foot">
			<slot name="footer" />
		</div>
	</div>
</template>

<style scoped>
.cw {
	border-radius: 14px;
	overflow: hidden;
	background: var(--af-code-bg);
	box-shadow: 0 24px 60px -20px rgba(15, 23, 42, 0.45), 0 0 0 1px rgba(148, 163, 184, 0.12);
}
.cw--compact {
	border-radius: 10px;
	box-shadow: 0 10px 28px -16px rgba(15, 23, 42, 0.4), 0 0 0 1px rgba(148, 163, 184, 0.12);
}

.cw__bar {
	display: flex;
	align-items: center;
	gap: 0.45rem;
	padding: 0.7rem 0.9rem;
	background: var(--af-code-head);
}
.cw--compact .cw__bar {
	padding: 0.5rem 0.7rem;
	gap: 0.35rem;
}

.cw__dot {
	width: 11px;
	height: 11px;
	border-radius: 50%;
	flex-shrink: 0;
}
.cw--compact .cw__dot {
	width: 8px;
	height: 8px;
}
.cw__dot--r {
	background: #ff5f57;
}
.cw__dot--a {
	background: #febc2e;
}
.cw__dot--g {
	background: #28c840;
}

.cw__name {
	margin-left: auto;
	font-size: 0.75rem;
	color: #94a3b8;
	white-space: nowrap;
}
.cw--compact .cw__name {
	font-size: 0.6875rem;
}

.cw__code {
	margin: 0;
	padding: 1.15rem 1.25rem;
	overflow-x: auto;
	font-size: 0.78rem;
	line-height: 1.7;
	color: #cbd5e1;
	background: none;
}
.cw--compact .cw__code {
	padding: 0.9rem 1rem;
	font-size: 0.68rem;
	line-height: 1.7;
}
.cw__code code {
	background: none;
	padding: 0;
	font-family: var(--vp-font-family-mono);
}

.cw__foot {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	flex-wrap: wrap;
	padding: 0.85rem 1.25rem;
	border-top: 1px solid rgba(148, 163, 184, 0.14);
	background: rgba(148, 163, 184, 0.05);
}
</style>
