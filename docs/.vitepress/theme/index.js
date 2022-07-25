import DefaultTheme from 'vitepress/theme'
import './custom.css'
import CustomViews from './CustomViews.vue'

export default {
    ...DefaultTheme,
    Layout: CustomViews
}
