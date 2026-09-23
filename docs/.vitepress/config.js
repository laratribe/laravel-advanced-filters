import { defineConfig } from 'vitepress'

// GitHub Pages serves from /<repo>/, a custom domain from /. Set DOCS_BASE=/ in the
// workflow (or add a CNAME) when the docs move to laratribe.com.
const base = process.env.DOCS_BASE ?? '/laravel-advanced-filters/'

export default defineConfig({
    base,
    lang: 'en-US',
    title: 'Laravel Advanced Filters',
    description:
        'Declare filters on any Eloquent model, then render them with Blade + Alpine, Livewire, Inertia, or no UI at all.',
    cleanUrls: true,
    lastUpdated: true,

    head: [['meta', { name: 'theme-color', content: '#f53003' }]],

    themeConfig: {
        nav: [
            { text: 'Guide', link: '/guide/installation' },
            { text: 'Frontends', link: '/frontends/blade-alpine' },
            { text: 'Extending', link: '/extending/operators' },
            { text: 'Config', link: '/reference/configuration' },
            // No trailing arrow in the text — VitePress appends its own external-link icon.
            { text: 'Live demo', link: 'https://advanced-filters.laratribe.com', target: '_blank' },
        ],

        sidebar: [
            {
                text: 'Guide',
                items: [
                    { text: 'Installation', link: '/guide/installation' },
                    { text: 'Quick start', link: '/guide/quick-start' },
                    { text: 'Filter types', link: '/guide/filter-types' },
                    { text: 'The wire contract', link: '/guide/wire-contract' },
                ],
            },
            {
                text: 'Frontends',
                items: [
                    { text: 'Blade + Alpine', link: '/frontends/blade-alpine' },
                    { text: 'Livewire', link: '/frontends/livewire' },
                    { text: 'Inertia (Vue / React)', link: '/frontends/inertia' },
                    { text: 'JSON API (no UI)', link: '/frontends/json-api' },
                ],
            },
            {
                text: 'Extending',
                items: [
                    { text: 'Custom operators', link: '/extending/operators' },
                    { text: 'Custom filter types', link: '/extending/filter-types' },
                    { text: 'Custom panels', link: '/extending/panel' },
                ],
            },
            {
                text: 'Reference',
                items: [{ text: 'Configuration', link: '/reference/configuration' }],
            },
        ],

        socialLinks: [
            { icon: 'github', link: 'https://github.com/laratribe/laravel-advanced-filters' },
        ],

        editLink: {
            pattern:
                'https://github.com/laratribe/laravel-advanced-filters/edit/main/docs/:path',
            text: 'Edit this page on GitHub',
        },

        search: { provider: 'local' },

        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © Ram Sharma',
        },
    },
})
