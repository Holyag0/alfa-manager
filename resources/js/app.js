import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import AppLayout from './Layouts/AppLayout.vue';
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title}  ${appName}`,
    resolve: async (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue');
        const page = (await pages[`./Pages/${name}.vue`]()).default;

        if (page.layout === undefined) {
            page.layout = AppLayout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#00FF00',
        showSpinner: true
    },
});