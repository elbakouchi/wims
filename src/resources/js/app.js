import './bootstrap';
import i18n from '@/Core/i18n';
import mixin from '@/Core/mixin';
import { createApp, h } from 'vue';
import Icons from '@/Shared/Icons.vue';
import Boolean from '@/Shared/Boolean.vue';
import Loading from '@/Shared/Loading.vue';
// import Layout from '@/Layouts/AdminLayout.vue';
import { InertiaProgress } from '@inertiajs/progress';
import { App as InertiaApp, plugin as InertiaPlugin } from '@inertiajs/inertia-vue3';

const el = document.getElementById('app');
let resolveComponent = name => require(`./Pages/${name}`).default;
// For vitejs start only
// if (process.env.NODE_ENV != 'production') {
//   import('@r/css/app.css');
//   import('vite/dynamic-import-polyfill');
//   resolveComponent = async name => {
//     const pages = import.meta.glob('./Pages/**/*.vue');
//     return (await pages[`./Pages/${name}.vue`]()).default;
//   };
// }

const app = createApp({
  render: () =>
    h(InertiaApp, {
      initialPage: JSON.parse(el.dataset.page),
      resolveComponent: resolveComponent,
    }),
});

app.component('Icons', Icons);
app.component('Boolean', Boolean);
app.component('Loading', Loading);
app.config.globalProperties.$route = route;
app.mixin({ methods: { route } }).mixin(mixin).use(i18n).use(InertiaPlugin).mount('#app');
InertiaProgress.init({ delay: 250, color: '#2563EB', includeCSS: false, showSpinner: true });
