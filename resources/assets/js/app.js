/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue");

/**
 * Uncomment below when compiling to production
 */
// Vue.config.devtools = false;
// Vue.config.debug = false;
// Vue.config.silent = true;

window.moment = require("moment");
import "moment/locale/ru";

window.axios = require("axios");
window.axios.defaults.headers.common = {
  "X-Requested-With": "XMLHttpRequest",
  "X-CSRF-TOKEN": document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content")
};

import Vuex from "vuex";
import vuexI18n from "vuex-i18n";
import Vuetify from "vuetify";
import Vue2Filters from "vue2-filters";
import VueRouter from "vue-router";
import VueAxios from "vue-axios";
import VueLocalStorage from "vue-ls";
import VuetifyConfirm from "vuetify-confirm";
import "vuetify/dist/vuetify.min.css";
import { ResizeObserver } from "vue-resize";

import "ag-grid/dist/styles/ag-grid.css";
import "ag-grid/dist/styles/ag-theme-balham.css";

Vue.use(Vuetify);
Vue.use(VueRouter);
Vue.use(VueAxios, axios);
Vue.use(Vue2Filters);
axios.defaults.baseURL = "/api";

Vue.use(VueLocalStorage, { namespace: "vuejs__" });

Vue.component("home", require("./components/home/Index.vue"));
Vue.component("resize-observer", ResizeObserver);

import App from "./components/home/Index.vue";

// Хранение в сторе данных о странице входа
const store = new Vuex.Store({
  state: { enter_url: "/", user: null },
  getters: {
    enter_url: state => {
      return state.enter_url;
    },
    get_user: state => {
      return state.user;
    }
  },
  mutations: {
    set_url(state, val) {
      state.enter_url = val;
    },
    set_user(state, val) {
      state.user = val;
    }
  }
});

Vue.use(vuexI18n.plugin, store);

import local_ru from "../lang/locale-ru.json";
import local_en from "../lang/locale-en.json";

Vue.i18n.add("ru", local_ru);
Vue.i18n.add("en", local_en);

// Значение по умолчанию
Vue.i18n.set("en");

// Плагин подтверждений
Vue.use(VuetifyConfirm, {
  buttonTrueText: "Yes",
  buttonFalseText: "No",
  buttonFalseColor: "grey",
  color: "primary",
  icon: "warning",
  title: Vue.i18n.translate("warning"),
  width: 300,
  property: "$confirm"
});

import routes from "./routes.js";

const router = new VueRouter({
  // mode: 'history',
  routes: routes,
  linkActiveClass: "list__tile--active"
});

// По какому УРЛ вошли
router.beforeEach((to, from, next) => {
  // Если не авторизован, то фиксируем в сторе путь куда шел пользователь
  if (store.state.user === null && to.name !== "login") {
    store.commit("set_url", to.path);
  }
  // Если путь неизвестный, то вместо 404 показываем главную страницу
  if (to.name === null) {
    next("/");
  }
  if (to.path === "/") {
    // Если пользователь не админ, то отправляем в задачи
    if (store.state.user !== null && store.state.user.role_id === 2) {
      next("/tasks_list/:id");
    } else {
      next();
    }
  } else {
    next();
  }
});

router.beforeResolve((to, from, next) => {
  // Если пользователь авторизован, но не администратор
  if (
    store.state.user !== null &&
    store.state.user.role_id !== 1 &&
    to.name !== "login"
  ) {
    if (
      to.meta.auth &&
      to.meta.role_id.indexOf(parseInt(store.state.user.role_id)) > -1
    ) {
      next();
    } else {
      next("/tasks_list/:id");
    }
  } else {
    next();
  }
});

Vue.router = router;

Vue.use(require("@websanova/vue-auth"), {
  auth: require("@websanova/vue-auth/drivers/auth/bearer.js"),
  http: require("@websanova/vue-auth/drivers/http/axios.1.x.js"),
  router: require("@websanova/vue-auth/drivers/router/vue-router.2.x.js")
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
  el: "#app",
  store: store,
  router: router,
  render: app => app(App),
  data() {
    return {
      routes: routes
    };
  },
  methods: {
    get_user() {
      axios.get("/auth/user").then(response => {
        store.commit("set_user", response.data.data);
      });
    }
  },
  beforeMount: function() {
    this.get_user();
  },
  mounted: function() {
    let lang = Vue.ls.get("lang", "en");
    Vue.i18n.set(lang);
  }
});
