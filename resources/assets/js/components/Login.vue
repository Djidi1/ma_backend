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
                            label="E-mail"
                            :counter="50"
                            :rules="emailRules"
                            v-model="email"
                            required
                    ></v-text-field>
                </v-flex>
                <v-flex xs12>
                    <v-text-field
                            name="passwors"
                            label="Enter your password"
                            hint="At least 8 characters"
                            v-model="password"
                            min="8"
                            :append-icon="e1 ? 'visibility' : 'visibility_off'"
                            :append-icon-cb="() => (e1 = !e1)"
                            :type="e1 ? 'password' : 'text'"
                            required
                            counter
                    ></v-text-field>
                </v-flex>
                <v-btn type="submit" color="success">Sign in</v-btn>
            </form>
        </v-card>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                email: null,
                password: null,
                error: false,
                emailRules: [
                    v => !!v || 'E-mail is required',
                    v => /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail must be valid'
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
                       // this.update_tasks_badge ();
                    },
                    error: function () {this.error = true},
                    rememberMe: true,
                    redirect: '/',
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
        }
    }
</script>

<style scoped>

</style>