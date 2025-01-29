import { createApp, h } from 'vue'
import { createInertiaApp,Link, Head  } from '@inertiajs/vue3'
import {route} from "ziggy-js"
createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
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
        // The delay after which the progress bar will appear, in milliseconds...
        delay: 250,
    
        // The color of the progress bar...
        color: '#29d',
    
        // Whether to include the default NProgress styles...
        includeCSS: true,
    
        // Whether the NProgress spinner will be shown...
        showSpinner: true,
      },
      // ...
});

