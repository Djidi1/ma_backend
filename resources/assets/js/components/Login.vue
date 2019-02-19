<template>
    <div style="width:100%; height: 100%">
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-alert type="warning" :value="true" v-if="error">
                There was an error, unable to sign in with those credentials.
            </v-alert>
            <form autocomplete="off" class="mx-3" @submit.prevent="login" method="post">
                <v-flex xs12>
                    <v-text-field
                            name="email"
                            type="email"
                            :label="$t('enter_email')"
                            :counter="50"
                            :rules="emailRules"
                            :class="{ 'input-group--dirty': dirtyEmail }"
                            v-model="email"
                            required
                    ></v-text-field>
                </v-flex>
                <v-flex xs12>
                    <v-text-field
                            name="passwors"
                            :label="$t('enter_password')"
                            hint="At least 8 characters"
                            v-model="password"
                            min="8"
                            :append-icon="e1 ? 'visibility' : 'visibility_off'"
                            :append-icon-cb="() => (e1 = !e1)"
                            :type="e1 ? 'password' : 'text'"
                            :class="{ 'input-group--dirty': dirtyPwd }"
                            required
                            counter
                    ></v-text-field>
                </v-flex>
                <v-btn type="submit" color="success">{{$t('sign_in')}}</v-btn>
            </form>
        </v-card>
    </div>
</template>

<script>
    export default {
        data(){
            return {

                dirtyEmail: null,
                dirtyPwd: null,
                email: '',
                password: '',
                error: false,
                emailRules: [
                    v => !!v || 'E-mail is required',
                    v => /.+@.+/.test(v) || 'E-mail must be valid'
                ],
                e1: true
            }
        },
        methods: {
            login(){
                let app = this;
                this.error = false;
                this.$auth.login({
                    params: {
                        email: app.email,
                        password: app.password
                    },
                    success: function () {
                        this.$store.commit('set_user', this.$auth.user());
                       // this.update_tasks_badge ();
                    },
                    error: function () {this.error = true},
                    rememberMe: true,
                    redirect: this.$store.state.enter_url,
                    fetchUser: true,
                });
            },
            update_tasks_badge (){
                // Пробегаемся по всем маршрутам
                for (let index in this.$router.options.routes) {
                    if( this.$router.options.routes.hasOwnProperty( index ) ) {
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
        mounted () {
            let times = 0;
            const interval = setInterval(() => {
                times += 1;
                if ((this.dirtyEmail && this.dirtyPwd) || times === 20) {
                    clearInterval(interval);
                }
                this.dirtyEmail = document.querySelector('input[type="email"]:-webkit-autofill');
                this.dirtyPwd = document.querySelector('input[type="password"]:-webkit-autofill');
            }, 100);
        }
    }
</script>

<style>
    /* Change autocomplete styles in WebKit */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    textarea:-webkit-autofill,
    textarea:-webkit-autofill:hover,
    textarea:-webkit-autofill:focus,
    select:-webkit-autofill,
    select:-webkit-autofill:hover,
    select:-webkit-autofill:focus {
        border: none;
        -webkit-text-fill-color: black;
        -webkit-box-shadow: 0 0 0 1000px #E3F2FD inset;
        transition: background-color 5000s ease-in-out 0s;
    }
</style>