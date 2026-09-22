import DefaultTheme from 'vitepress/theme'
import HomeHero from './components/HomeHero.vue'
import HomeFeatures from './components/HomeFeatures.vue'
import './style/custom.css'

export default {
    extends: DefaultTheme,
    enhanceApp({ app }) {
        app.component('HomeHero', HomeHero)
        app.component('HomeFeatures', HomeFeatures)
    },
}
