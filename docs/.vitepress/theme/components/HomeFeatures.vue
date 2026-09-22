<script setup>
import { withBase } from 'vitepress'
import CodeWindow from './CodeWindow.vue'
const STEPS = [
    {
        n: '01',
        title: 'Declare',
        body: 'Add the trait and a filters() method to the model. This is also the allow-list.',
        file: 'app/Models/Product.php',
        code: `<span class="c-key">namespace</span> <span class="c-cls">App\\Models</span>;

<span class="c-key">use</span> <span class="c-cls">Laratribe\\AdvancedFilters\\Concerns\\HasFilters</span>;
<span class="c-key">use</span> <span class="c-cls">Laratribe\\AdvancedFilters\\Contracts\\Filterable</span>;

<span class="c-key">class</span> <span class="c-cls">Product</span> <span class="c-key">extends</span> <span class="c-cls">Model</span> <span class="c-key">implements</span> <span class="c-cls">Filterable</span>
{
    <span class="c-key">use</span> <span class="c-cls">HasFilters</span>;

    <span class="c-key">public static function</span> <span class="c-fn">filters</span>(): <span class="c-cls">array</span>
    {
        <span class="c-key">return</span> [
            <span class="c-cls">TextFilter</span>::<span class="c-fn">make</span>(<span class="c-str">'name'</span>),
            <span class="c-cls">SetFilter</span>::<span class="c-fn">make</span>(<span class="c-str">'status'</span>),
        ];
    }
}`,
    },
    {
        n: '02',
        title: 'Apply',
        body: 'One scope, on any query, beside your own constraints — then hand the definitions to the view.',
        file: 'app/Http/Controllers/ProductController.php',
        code: `<span class="c-var">$filters</span> = <span class="c-var">$request</span>-&gt;<span class="c-fn">input</span>(<span class="c-str">'column_filters'</span>);

<span class="c-var">$products</span> = <span class="c-cls">Product</span>::<span class="c-fn">query</span>()
    -&gt;<span class="c-fn">applyFilters</span>(<span class="c-var">$filters</span>)
    -&gt;<span class="c-fn">paginate</span>(25);

<span class="c-key">return</span> <span class="c-fn">view</span>(<span class="c-str">'products.index'</span>, [
    <span class="c-str">'products'</span> =&gt; <span class="c-var">$products</span>,
    <span class="c-str">'fields'</span> =&gt; <span class="c-cls">Product</span>::<span class="c-fn">filterFieldsForFrontend</span>(),
    <span class="c-str">'active'</span> =&gt; <span class="c-cls">Product</span>::<span class="c-fn">normalizeFilters</span>(<span class="c-var">$filters</span>),
]);`,
    },
    {
        n: '03',
        title: 'Render',
        body: 'The packaged panel, or your own against the wire contract.',
        file: 'resources/views/products/index.blade.php',
        code: `<span class="c-com">{{-- Blade + Alpine --}}</span>
&lt;<span class="c-tag">x-advanced-filters::panel</span>
    <span class="c-attr">:fields</span>=<span class="c-str">"$fields"</span>
    <span class="c-attr">:active</span>=<span class="c-str">"$active"</span>
/&gt;

{{ <span class="c-var">$products</span>-&gt;<span class="c-fn">links</span>() }}`,
    },
]

const features = [
    {
        icon: '🪞',
        title: 'Self-describing',
        body: 'The server publishes which columns exist, which operators each allows, their labels and how many values they take. That is what makes a generic panel possible — the UI never hardcodes your schema.',
    },
    {
        icon: '🎛️',
        title: 'Four frontends, one backend',
        body: 'Blade + Alpine with no build step, Livewire 3 and 4, Inertia with Vue or React, or no UI at all as a JSON API for a SPA or mobile client.',
    },
    {
        icon: '🧩',
        title: 'Extensible where it counts',
        body: 'Add filter types with their own input views, and operators carrying their own labels and value shapes. Registering adds, publishing overrides — you never fork a shipped view.',
    },
    {
        icon: '🔒',
        title: 'Safe by construction',
        body: 'filters() is the allow-list. A column you did not declare, or an operator a field does not allow, is dropped before it reaches SQL — on every frontend, without extra validation.',
    },
]

</script>

<template>
	<section class="af-sec">
		<div class="af-sec__inner">
			<div class="af-grid">
				<article v-for="f in features" :key="f.title" class="af-card">
					<span class="af-card__icon">{{ f.icon }}</span>
					<h3>{{ f.title }}</h3>
					<p>{{ f.body }}</p>
				</article>
			</div>

			<div class="af-steps">
				<article v-for="s in STEPS" :key="s.n" class="af-step">
					<header>
						<span class="af-step__n">{{ s.n }}</span>
						<h4>{{ s.title }}</h4>
						<p>{{ s.body }}</p>
					</header>
					<CodeWindow :code="s.code" :name="s.file" compact />
				</article>
			</div>

			<div class="af-cta">
				<h2>Three steps to a working filter UI.</h2>
				<p>No build step required, and nothing to publish unless you want to restyle it.</p>
				<a class="af-cta__btn" :href="withBase('/guide/quick-start')">
					Read the quick start <span aria-hidden="true">→</span>
				</a>
			</div>
		</div>
	</section>
</template>

<style scoped>
.af-sec {
	padding: 1rem 1.5rem 5rem;
}
.af-sec__inner {
	max-width: 1180px;
	margin: 0 auto;
}

.af-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 1rem;
}
@media (min-width: 640px) {
	.af-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}
@media (min-width: 1024px) {
	.af-grid {
		grid-template-columns: repeat(4, 1fr);
	}
}

.af-card {
	padding: 1.5rem;
	border: 1px solid var(--vp-c-divider);
	border-radius: 14px;
	background: var(--vp-c-bg-soft);
	transition: border-color 0.2s, transform 0.2s;
}
.af-card:hover {
	border-color: var(--af-brand-1);
	transform: translateY(-2px);
}
.af-card__icon {
	font-size: 1.5rem;
	line-height: 1;
}
.af-card h3 {
	margin: 0.9rem 0 0.5rem;
	font-size: 1.0625rem;
	font-weight: 700;
	letter-spacing: -0.01em;
	color: var(--vp-c-text-1);
}
.af-card p {
	margin: 0;
	font-size: 0.875rem;
	line-height: 1.65;
	color: var(--vp-c-text-2);
}

.af-steps {
	display: grid;
	grid-template-columns: 1fr;
	gap: 1.5rem;
	margin-top: 4rem;
	padding-top: 3rem;
	border-top: 1px solid var(--vp-c-divider);
}
@media (min-width: 900px) {
	.af-steps {
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 1.5rem;
	}
}

/* Grid rows so the three code windows start on one line however long the blurb is. */
.af-step {
	display: grid;
	grid-template-rows: auto 1fr;
	gap: 1rem;
}
.af-step header {
	display: flex;
	flex-direction: column;
}
.af-step__n {
	font-size: 0.75rem;
	font-weight: 800;
	letter-spacing: 0.1em;
	background: linear-gradient(100deg, var(--af-brand-1), var(--af-brand-2));
	-webkit-background-clip: text;
	background-clip: text;
	color: transparent;
}
.af-step h4 {
	margin: 0.35rem 0 0.3rem;
	font-size: 1.0625rem;
	font-weight: 700;
	letter-spacing: -0.01em;
	color: var(--vp-c-text-1);
}
.af-step p {
	margin: 0;
	font-size: 0.875rem;
	line-height: 1.6;
	color: var(--vp-c-text-2);
}

.af-cta {
	margin-top: 4rem;
	padding: 3rem 2rem;
	border-radius: 18px;
	text-align: center;
	background: var(--af-brand-soft);
	border: 1px solid var(--vp-c-divider);
}
.af-cta h2 {
	margin: 0;
	font-size: clamp(1.4rem, 3.4vw, 2rem);
	font-weight: 800;
	letter-spacing: -0.02em;
	color: var(--vp-c-text-1);
	border: 0;
}
.af-cta p {
	margin: 0.6rem 0 1.6rem;
	color: var(--vp-c-text-2);
	font-size: 0.9375rem;
}
.af-cta__btn {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.7rem 1.4rem;
	border-radius: 10px;
	font-weight: 600;
	font-size: 0.9375rem;
	text-decoration: none;
	color: #fff;
	background: linear-gradient(100deg, var(--af-brand-1), var(--af-brand-2));
}
.af-cta__btn:hover {
	filter: brightness(1.07);
}
</style>
