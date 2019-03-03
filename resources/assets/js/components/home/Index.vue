<template>
    <v-app standalone>
        <div v-if="$auth.ready()" style="height: 100%">
            <v-navigation-drawer fixed
                                 app
                                 :mini-variant.sync="mini"
                                 v-model="drawer">
                <v-toolbar flat class="transparent">
                    <v-card flat width="100%" style="border-radius: 0;box-shadow: 0 2px 4px -1px rgba(0,0,0,.2), 0 4px 5px 0 rgba(0,0,0,.14), 0 1px 10px 0 rgba(0,0,0,.12);">
                        <v-card-media src="/images/office.jpg">
                            <v-layout column class="media">
                                <v-spacer></v-spacer>
                                <v-list subheader class="mt-1">
                                    <v-list-tile avatar>
                                        <!--<v-list-tile-avatar>-->
                                        <!--<img src="/images/user.png">-->
                                        <!--</v-list-tile-avatar>-->
                                        <v-list-tile-content>
                                            <v-list-tile-title class="white--text">{{ $auth.user().name }}</v-list-tile-title>
                                            <v-list-tile-sub-title class="white--text">{{ $auth.user().email }}</v-list-tile-sub-title>
                                            <v-list-tile-sub-title class="white--text align-right">{{ typeof $auth.user().role !== 'undefined' ? $auth.user().role.title : '' }}</v-list-tile-sub-title>
                                        </v-list-tile-content>
                                    </v-list-tile>
                                </v-list>
                                <v-spacer></v-spacer>
                            </v-layout>
                        </v-card-media>
                    </v-card>
                </v-toolbar>
                <v-list class="pt-3" dense>
                    <v-divider></v-divider>
                    <li
                            v-for="(item, index) in menuItems"
                            :key="index"
                    >
                        <router-link :to="item.path" class="list__tile" exact v-if="!item.divider">
                            <v-list-tile-action>
                                <v-icon>{{ item.icon }}</v-icon>
                            </v-list-tile-action>
                            <v-list-tile-content>
                                <v-list-tile-title>{{ $t(item.title) }}</v-list-tile-title>
                            </v-list-tile-content>
                            <v-list-tile-action v-if="item.meta.badge > 0">
                                <v-badge color="orange" :left="true">
                                    <span slot="badge">{{ item.meta.badge }}</span>
                                </v-badge>
                            </v-list-tile-action>
                        </router-link>
                        <v-divider v-if="item.divider"></v-divider>
                    </li>
                    <v-divider></v-divider>
                    <li v-if="$auth.check()">
                        <a href="/#/login" class="list__tile" @click="$auth.logout()">
                            <div class="list__tile__action"><i aria-hidden="true" class="icon material-icons">exit_to_app</i></div>
                            <div class="list__tile__content">
                                <div class="list__tile__title">{{ $t('logout') }}</div>
                            </div>
                        </a>
                    </li>
                </v-list>
                <v-footer v-if="is_scroll" class="pa-3 scroll_down">
                    <div>Ver. {{ this.version }}</div>
                    <v-spacer></v-spacer>
                    <div>&copy; {{ new Date().getFullYear() }}</div>
                </v-footer>
                <v-footer v-else class="pa-3" absolute>
                    <div>Ver. {{ this.version }}</div>
                    <v-spacer></v-spacer>
                    <div>&copy; {{ new Date().getFullYear() }}</div>
                </v-footer>
            </v-navigation-drawer>
            <v-toolbar color="blue"
                       dark
                       fixed
                       app>
                <v-toolbar-side-icon @click.native.stop="draw_navigation"></v-toolbar-side-icon>
                <!--<v-btn icon @click.stop="mini = !mini">-->
                <!--<v-icon v-html="mini ? 'chevron_right' : 'chevron_left'"></v-icon>-->
                <!--</v-btn>-->
                <v-toolbar-title>{{ $t(this.$route.name) }}</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-menu :nudge-width="100">
                    <v-toolbar-title slot="activator" :title="$t('lang')">
                        <i class="material-icons">language</i>
                        <v-icon dark>arrow_drop_down</v-icon>
                    </v-toolbar-title>
                    <v-list>
                        <v-list-tile v-for="item in languages" :key="item.code" @click="set_local(item.code)">
                            <v-list-tile-title v-text="item.title"></v-list-tile-title>
                        </v-list-tile>
                    </v-list>
                </v-menu>
            </v-toolbar>
            <v-content fill-height fill-width style="height: 100%">
                <v-container fluid fill-height fill-width>
                    <slot></slot>
                    <router-view></router-view>
                </v-container>
            </v-content>
        </div>
        <v-container fill-height v-if="!$auth.ready()">
            <v-layout row wrap align-center>
                <v-flex class="text-xs-center">
                    <v-progress-circular indeterminate :size="100" color="primary"></v-progress-circular>
                </v-flex>
            </v-layout>
        </v-container>
    </v-app>
</template>
<script>
    const languages = [{title: 'English', code: 'en'}, {title: 'Русский', code: 'ru'}];
    export default {
        data() {
            return {
                version: '1.0.2.0',
                drawer: false,
                mini: false,
                languages: languages,
                routes: this.$router.options.routes,
                title: this.title,
                loading: true,
                is_scroll: false
            }
        },
        computed: {
            menuItems() {
                let self = this;
                return this.routes.filter(item => {
                    let result = false;
                    // Если этот пункт меню необходимо отобразить
                    if (typeof item !== 'undefined' && !item.meta.no_show) {
                        // Если пользователь не авторизован и пункт для неавторизованных
                        if ((!self.$auth.check() && !item.meta.auth)) {
                            result = true;
                            // Если авторизован и указан перечень групп
                        } else if ((self.$auth.check() && item.meta.auth && (item.meta.role_id.indexOf(parseInt(self.$auth.user().role_id)) > -1))) {
                            result = true;
                        }
                    }
                    return result;
                })
            },
        },
        methods: {
            set_local(code) {
                Vue.i18n.set(code);
                Vue.ls.set('lang', code);
                let event = new CustomEvent("langChanged", {
                    detail: { lang: code }
                });
                document.dispatchEvent(event);
            },
            draw_navigation() {
                this.detect_scroll();
                this.drawer = !this.drawer;
            },
            detect_scroll() {
                let root = document.getElementsByTagName("aside")[0];
                if (typeof root !== 'undefined') {
                    this.is_scroll = root.scrollHeight > root.clientHeight;
                }
            },
            update_tasks_badge() {
                // Пробегаемся по всем маршрутам
                for (let index in this.$router.options.routes) {
                    if (this.$router.options.routes.hasOwnProperty(index)) {
                        // Если это меню с задачами, то проставялем количество задач
                        if (this.$router.options.routes[index].name === 'tasks_list') {
                            axios.get('/responsible_tasks')
                                .then(response => {
                                    this.$router.options.routes[index].meta.badge = response.data.responsible_tasks.length;
                                });
                        }
                    }
                }
                // console.log(this.$router.options.routes[name='tasks_list'].meta.badge);
            }
        },
        beforeDestroy: function () {
            window.removeEventListener('resize', this.detect_scroll)
        },

        mounted() {
            let self = this;
            this.loading = false;
            if (this.$auth.check()) {
                this.$store.commit('set_user', this.$auth.user());
            }
            this.$nextTick(function () {
                self.detect_scroll();
                window.addEventListener('resize', self.detect_scroll);
            });
            // if (this.$auth.check()) {
            //  this.update_tasks_badge();
            // }
        }
    }
</script>

<style>
    .ag-floating-filter-input {
        background: #fff;
        border-radius: 4px;
    }

    .ag-bl-overlay {
        background: #F1F8E9;
        z-index: 4;
        box-shadow: 0 2px 1px -1px rgba(0, 0, 0, .1), 0 1px 1px 0 rgba(0, 0, 0, .07), 0 1px 3px 0 rgba(0, 0, 0, .06);
    }

    .navigation-drawer > .list .list__tile--active .list__tile__title,
    .list__tile--active .list__tile__action:first-of-type .icon {
        color: #2196F3 !important;
    }

    .align-right {
        text-align: right;
    }
    .scroll_down {
        bottom: -100px;
        position: relative;
    }
</style>
