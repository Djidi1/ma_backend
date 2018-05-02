<template>
    <div style="width:100%">
        <v-alert type="warning" :value="true" v-if="error">
            There was an error, unable to sign in with those credentials.
        </v-alert>
        <form autocomplete="off" @submit.prevent="login" method="post">
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
                    success: function () {},
                    error: function () {this.error = true},
                    rememberMe: true,
                    redirect: '/tasks_calendar',
                    fetchUser: true,
                });
            },
        }
    }
</script>

<style scoped>

</style>