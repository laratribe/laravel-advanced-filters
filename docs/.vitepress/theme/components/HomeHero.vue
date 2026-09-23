<script setup>
import { ref } from 'vue'
import { withBase } from 'vitepress'
import CodeWindow from './CodeWindow.vue'

const INSTALL = 'composer require laratribe/laravel-advanced-filters'

const MODEL_CODE = `<span class="c-key">class</span> <span class="c-cls">Product</span> <span class="c-key">extends</span> <span class="c-cls">Model</span> <span class="c-key">implements</span> <span class="c-cls">Filterable</span>
{
    <span class="c-key">use</span> <span class="c-cls">HasFilters</span>;

    <span class="c-key">public static function</span> <span class="c-fn">filters</span>(): <span class="c-cls">array</span>
    {
        <span class="c-key">return</span> [
            <span class="c-cls">TextFilter</span>::<span class="c-fn">make</span>(<span class="c-str">'name'</span>),
            <span class="c-cls">SetFilter</span>::<span class="c-fn">make</span>(<span class="c-str">'status'</span>)-&gt;<span class="c-fn">multiple</span>(),
            <span class="c-cls">NumericFilter</span>::<span class="c-fn">make</span>(<span class="c-str">'price'</span>),
        ];
    }
}`

const copied = ref(false)

async function copyInstall() {
    try {
        await navigator.clipboard.writeText(INSTALL)
    } catch {
        return // clipboard blocked (insecure origin, permissions) — fail quietly
    }
    copied.value = true
    setTimeout(() => (copied.value = false), 1800)
}
</script>

<template>
	<section class="af-hero">
		<div class="af-hero__inner">
			<div class="af-hero__copy">
				<a class="af-pill" :href="withBase('/guide/installation')">
					<span class="af-pill__dot" />
					LARAVEL 10 · 11 · 12 · 13
					<span class="af-pill__arrow">↗</span>
				</a>

				<h1 class="af-title">
					Advanced filters,
					<span class="af-title__accent">without building the UI.</span>
				</h1>

				<p class="af-lede">
					Declare filters once on an Eloquent model. Render them with Blade, Livewire,
					Inertia or nothing at all — one backend definition, no duplicated logic.
				</p>

				<div class="af-actions">
					<a class="af-btn af-btn--primary" :href="withBase('/guide/quick-start')">
						Start building <span aria-hidden="true">→</span>
					</a>
					<a
						class="af-btn af-btn--ghost af-btn--demo"
						href="https://advanced-filters.laratribe.com"
						target="_blank"
						rel="noreferrer"
					>
						<span class="af-btn__live" aria-hidden="true" />
						Live demo
					</a>
					<a
						class="af-btn af-btn--ghost"
						href="https://github.com/laratribe/laravel-advanced-filters"
						target="_blank"
						rel="noreferrer"
					>
						<svg viewBox="0 0 16 16" width="18" height="18" aria-hidden="true">
							<path
								fill="currentColor"
								d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8Z"
							/>
						</svg>
						View on GitHub
					</a>
				</div>

				<div class="af-install">
					<span class="af-install__prompt">$</span>
					<code class="af-install__cmd">{{ INSTALL }}</code>
					<button class="af-install__copy" type="button" @click="copyInstall">
						{{ copied ? 'COPIED' : 'COPY' }}
					</button>
				</div>

			</div>

			<div class="af-hero__visual" aria-hidden="true">
				<CodeWindow :code="MODEL_CODE" name="Product.php">
					<template #footer>
						<span class="af-window__label">RENDERS AS</span>
						<div class="af-chips">
							<span class="af-chip">Blade</span>
							<span class="af-chip">Livewire</span>
							<span class="af-chip">Inertia</span>
							<span class="af-chip">JSON API</span>
						</div>
					</template>
				</CodeWindow>

				<div class="af-float af-float--a">
					<span class="af-float__tick">✓</span> Allow-listed
				</div>
				<div class="af-float af-float--b">
					<span class="af-float__pulse" /> Self-describing
				</div>
			</div>
		</div>
	</section>
</template>

<style scoped>
.af-hero {
	position: relative;
	padding: 4rem 1.5rem 3rem;
	overflow: hidden;
}

/* Faint grid, so the section has texture without competing with the copy. */
.af-hero::before {
	content: '';
	position: absolute;
	inset: 0;
	background-image: linear-gradient(var(--af-grid) 1px, transparent 1px),
		linear-gradient(90deg, var(--af-grid) 1px, transparent 1px);
	background-size: 44px 44px;
	mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, #000 40%, transparent 100%);
	pointer-events: none;
}

.af-hero__inner {
	position: relative;
	max-width: 1180px;
	margin: 0 auto;
	display: grid;
	grid-template-columns: 1fr;
	gap: 3rem;
	align-items: center;
}

@media (min-width: 960px) {
	.af-hero__inner {
		grid-template-columns: 1.05fr 1fr;
		gap: 3.5rem;
	}
	.af-hero {
		padding: 5.5rem 2rem 4.5rem;
	}
}

/* ---------- copy ---------- */

.af-pill {
	display: inline-flex;
	align-items: center;
	gap: 0.55rem;
	padding: 0.4rem 0.9rem;
	border: 1px solid var(--vp-c-divider);
	border-radius: 999px;
	font-size: 0.75rem;
	font-weight: 700;
	letter-spacing: 0.06em;
	color: var(--vp-c-text-2);
	text-decoration: none;
	transition: border-color 0.2s, color 0.2s;
}
.af-pill:hover {
	border-color: var(--af-brand-1);
	color: var(--vp-c-text-1);
}
.af-pill__dot {
	width: 7px;
	height: 7px;
	border-radius: 50%;
	background: #10b981;
	box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18);
}
.af-pill__arrow {
	opacity: 0.55;
}

.af-title {
	margin: 1.5rem 0 0;
	font-size: clamp(2.4rem, 6.2vw, 4rem);
	line-height: 1.05;
	letter-spacing: -0.03em;
	font-weight: 800;
	color: var(--vp-c-text-1);
}
.af-title__accent {
	display: block;
	background: linear-gradient(100deg, var(--af-brand-1), var(--af-brand-2));
	-webkit-background-clip: text;
	background-clip: text;
	color: transparent;
}

.af-lede {
	margin: 1.25rem 0 0;
	max-width: 34rem;
	font-size: 1.0625rem;
	line-height: 1.7;
	color: var(--vp-c-text-2);
}

.af-actions {
	display: flex;
	flex-wrap: wrap;
	gap: 0.75rem;
	margin-top: 2rem;
}
.af-btn {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.7rem 1.35rem;
	border-radius: 10px;
	font-weight: 600;
	font-size: 0.9375rem;
	text-decoration: none;
	transition: transform 0.15s, background 0.2s, border-color 0.2s;
}
.af-btn:active {
	transform: translateY(1px);
}
.af-btn--primary {
	color: #fff;
	background: linear-gradient(100deg, var(--af-brand-1), var(--af-brand-2));
	box-shadow: 0 6px 20px -8px var(--af-brand-ring);
}
.af-btn--primary:hover {
	filter: brightness(1.07);
}
.af-btn--ghost {
	color: var(--vp-c-text-1);
	border: 1px solid var(--vp-c-divider);
	background: var(--vp-c-bg);
}
.af-btn--ghost:hover {
	border-color: var(--af-brand-1);
}

/* The demo is a running app, not a page — the live dot says so at a glance. */
.af-btn--demo:hover {
	border-color: #10b981;
}
.af-btn__live {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #10b981;
	box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18);
	animation: af-live 2s ease-in-out infinite;
}
@keyframes af-live {
	50% {
		box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
	}
}
@media (prefers-reduced-motion: reduce) {
	.af-btn__live {
		animation: none;
	}
}

.af-install {
	display: flex;
	align-items: center;
	gap: 0.6rem;
	margin-top: 1.5rem;
	padding: 0.7rem 0.7rem 0.7rem 1rem;
	border: 1px solid var(--vp-c-divider);
	border-radius: 10px;
	background: var(--vp-c-bg-alt);
	max-width: 32rem;
}
.af-install__prompt {
	color: var(--af-brand-1);
	font-weight: 700;
}
.af-install__cmd {
	flex: 1;
	font-size: 0.8125rem;
	color: var(--vp-c-text-1);
	background: none;
	overflow-x: auto;
	white-space: nowrap;
}
.af-install__copy {
	flex-shrink: 0;
	padding: 0.35rem 0.7rem;
	border: 1px solid var(--vp-c-divider);
	border-radius: 6px;
	background: var(--vp-c-bg);
	color: var(--vp-c-text-2);
	font-size: 0.6875rem;
	font-weight: 700;
	letter-spacing: 0.06em;
	cursor: pointer;
	transition: color 0.2s, border-color 0.2s;
}
.af-install__copy:hover {
	color: var(--af-brand-1);
	border-color: var(--af-brand-1);
}

/* ---------- code window ---------- */

.af-hero__visual {
	position: relative;
}

.af-window__label {
	font-size: 0.625rem;
	font-weight: 700;
	letter-spacing: 0.1em;
	color: #64748b;
}
.af-chips {
	display: flex;
	flex-wrap: wrap;
	gap: 0.4rem;
}
.af-chip {
	padding: 0.2rem 0.6rem;
	border-radius: 999px;
	font-size: 0.6875rem;
	font-weight: 600;
	color: #e0e7ff;
	background: rgba(99, 102, 241, 0.22);
	border: 1px solid rgba(129, 140, 248, 0.35);
}

/* Floating callouts — decorative, so hidden on small screens where they'd crowd. */
.af-float {
	display: none;
	position: absolute;
	align-items: center;
	gap: 0.45rem;
	padding: 0.5rem 0.85rem;
	border-radius: 10px;
	font-size: 0.75rem;
	font-weight: 600;
	color: var(--vp-c-text-1);
	background: var(--vp-c-bg);
	box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.4), 0 0 0 1px var(--vp-c-divider);
}
@media (min-width: 1100px) {
	.af-float {
		display: inline-flex;
	}
}
.af-float--a {
	top: -0.9rem;
	right: -1rem;
}
.af-float--b {
	bottom: -0.9rem;
	left: -1.4rem;
}
.af-float__tick {
	color: #10b981;
	font-weight: 800;
}
.af-float__pulse {
	width: 7px;
	height: 7px;
	border-radius: 50%;
	background: var(--af-brand-1);
	box-shadow: 0 0 0 3px var(--af-brand-soft);
}
</style>
