
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.moment = require('moment');
import 'moment/locale/ru'

window.axios = require('axios');
window.axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').getAttribute('content')
};


import Vuex from 'vuex';
import vuexI18n from 'vuex-i18n';
import Vuetify from 'vuetify';
import VueRouter from 'vue-router';
import VueAxios from 'vue-axios';
import VueLocalStorage from 'vue-ls';
import fullCalendar from 'vue-fullcalendar'
import VuetifyConfirm from 'vuetify-confirm'
import 'vuetify/dist/vuetify.min.css'

Vue.component('full-calendar', fullCalendar);

Vue.use(Vuetify);
Vue.use(VueRouter);
Vue.use(VueAxios, axios);
axios.defaults.baseURL = '/api';

Vue.use(VueLocalStorage, { namespace: 'vuejs__' });

Vue.component('home', require('./components/home/Index.vue'));
import App from './components/home/Index.vue';

const store = new Vuex.Store();
Vue.use(vuexI18n.plugin, store);

import local_ru from '../lang/locale-ru.json';
import local_en from '../lang/locale-en.json';

Vue.i18n.add('ru', local_ru);
Vue.i18n.add('en', local_en);

// Значение по умолчанию
Vue.i18n.set('en');

// Плагин подтверждений
Vue.use(VuetifyConfirm, {
    buttonTrueText: 'Yes',
    buttonFalseText: 'No',
    buttonFalseColor: 'grey',
    color: 'primary',
    icon: 'warning',
    title: Vue.i18n.translate('warning'),
    width: 300,
    property: '$confirm'
});

import routes from './routes.js'

const router = new VueRouter({
    routes: routes,
    linkActiveClass: 'list__tile--active'
});


Vue.router = router;

Vue.use(require('@websanova/vue-auth'), {
    auth: require('@websanova/vue-auth/drivers/auth/bearer.js'),
    http: require('@websanova/vue-auth/drivers/http/axios.1.x.js'),
    router: require('@websanova/vue-auth/drivers/router/vue-router.2.x.js'),
});


/*
router.beforeEach(function(to, from, next) {
    // use the language from the routing param or default language
    let locale = to.params.lang;
    if (!locale) {
        locale = 'en';
    }
    Vue.i18n.set(locale);
    next();
});
*/


const app = new Vue({
    el: '#app',
    store: store,
    router: router,
    render: app => app(App),
    data(){
        return {
            routes: routes
        }
    },
    mounted: function(){
        let lang = Vue.ls.get('lang', 'en');
        Vue.i18n.set(lang);
    },
});

/*
const app = new Vue({
    store: store,
    router: router,
    el: '#app',
    data(){
        return {
            routes: routes
        }
    },
});
*/