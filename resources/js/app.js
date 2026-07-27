import './bootstrap';
import '../css/app.css';
import './styles/theme.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';

import App from './App.vue';
import router from './router';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            cssLayer: false,
            darkModeSelector: 'system',
        },
    },
});

app.component('PButton', Button);
app.component('PCard', Card);
app.component('PColumn', Column);
app.component('PDataTable', DataTable);
app.component('PDropdown', Dropdown);
app.component('PInputText', InputText);
app.component('PPassword', Password);

app.mount('#app');
