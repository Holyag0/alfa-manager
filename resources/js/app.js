import { createApp, h } from 'vue'
import { createInertiaApp,Link, Head  } from '@inertiajs/vue3'
import {route} from "ziggy-js"
import Layout from './Shared/Layout.vue'
createInertiaApp({
    resolve: async name => {
        let page = (await import(`./Pages/${name}`)).default;

        if (page.layout === undefined) {
            page.layout = Layout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
        .mixin({ methods: { route } })
        .component('Link', Link)
        .component('Head', Head)
            .use(plugin)
            .mount(el);
    },
    title: title => "alfa - " + title,

    progress: {
        delay: 250,
        color: '#00FF00',
        includeCSS: true,
        showSpinner: true,
      },
});

