
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.moment = require('moment');

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

// Страницы
import Register from './components/Register.vue';
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard.vue';

import Users from './components/admin/users.vue';

import Audit from './components/audit/index.vue';
import AuditResults from './components/audit/index_results.vue';
import Object from './components/object/index.vue';
import ObjectGroups from './components/object/groups.vue';
import Requirement from './components/requirement/index.vue';
import RequirementGroups from './components/requirement/groups.vue';
import CheckLists from './components/checklist/index.vue';
import CheckListCategories from './components/checklist/groups.vue';
import TaskCalendar from './components/tasks/calendar.vue';

const routes = [
    {title: 'home', name:'home',  icon: 'home', path: '/home', component: Dashboard, meta: { auth: false } },
    {title: 'login', name:'login', icon: 'input', path: '/login', component: Login, meta: { auth: false } },
    {title: 'register', name:'register', icon: 'lock_open', path: '/register', component: Register, meta: { auth: false } },
    {title: 'users', name:'users', icon: 'supervisor_account', path: '/users', component: Users, meta: { auth: true } },
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'checklists', name:'checklists', icon: 'check', path: '/checklists', component: CheckLists, meta: { auth: true }},
    {title: 'checklist_categories', name:'checklist_categories', icon: 'playlist_add_check', path: '/checklist_categories', component: CheckListCategories, meta: { auth: true }},
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'requirements', name:'requirements', icon: 'assignment_turned_in', path: '/requirements', component: Requirement, meta: { auth: true }},
    {title: 'requirement_groups', name:'requirement_groups', icon: 'assignment', path: '/requirement_groups', component: RequirementGroups, meta: { auth: true }},
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'objects', name:'objects', icon: 'store', path: '/objects', component: Object, meta: { auth: true }},
    {title: 'object_groups', name:'object_groups', icon: 'location_city', path: '/objects_groups', component: ObjectGroups, meta: { auth: true }},
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'audits', name:'audits', icon: 'folder', path: '/audits', component: Audit, meta: { auth: true }},
    {title: 'audit_results', name:'audit_results', icon: 'folder_special', path: '/audit_results', component: AuditResults, meta: { auth: true }},
    {title: 'tasks_calendar', name:'tasks_calendar', icon: 'event', path: '/tasks_calendar', component: TaskCalendar, meta: { auth: true }},
];

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