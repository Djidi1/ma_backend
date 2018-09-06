<template>
    <div style="width:100%; height: 100%">
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-alert type="warning" :value="true" v-if="error && !success">
                There was an error, unable to complete registration.
            </v-alert>
            <v-alert type="success" :value="true" v-if="success">
                Registration completed. You can now <router-link :to="{title:'login'}">sign in.</router-link>
            </v-alert>
            <form autocomplete="off" class="mx-3" @submit.prevent="register" v-if="!success" method="post">
                <v-flex xs12>
                    <v-text-field
                            label="Name"
                            :counter="50"
                            :rules="nameRules"
                            v-model="name"
                            :error-messages="errors.name"
                            required
                    ></v-text-field>
                </v-flex>
                <v-flex xs12>
                    <v-text-field
                            label="E-mail"
                            type="email"
                            :counter="50"
                            :rules="emailRules"
                            v-model="email"
                            :error-messages="errors.email"
                            required
                    ></v-text-field>
                </v-flex>
                <v-flex xs12>
                    <v-text-field
                            label="Enter your password"
                            hint="At least 8 characters"
                            v-model="password"
                            min="8"
                            :error-messages="errors.password"
                            :append-icon="e1 ? 'visibility' : 'visibility_off'"
                            :append-icon-cb="() => (e1 = !e1)"
                            :type="e1 ? 'password' : 'text'"
                            required
                            counter
                    ></v-text-field>
                </v-flex>
                <v-btn type="submit" color="success">Submit</v-btn>
            </form>
        </v-card>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                name: '',
                email: '',
                password: '',
                error: false,
                errors: {},
                success: false,
                nameRules: [
                    v => !!v || 'Name is required',
                    v => typeof v !== 'undefined' ? (v.length  <= 50 || 'Name must be less than 50 characters') : ''
                ],
                emailRules: [
                    v => !!v || 'E-mail is required',
                    v => /.+@.+/.test(v) || 'E-mail must be valid'
                ],
                e1: true
            };
        },
        methods: {
            register(){
                let app = this;
                this.$auth.register({
                    params: {
                        name: app.name,
                        email: app.email,
                        password: app.password
                    },
                    success: function () {
                        app.success = true
                    },
                    error: function (resp) {
                        app.error = true;
                        app.errors = resp.response.data.errors;
                    },
                    redirect: '/login'
                });
            }
        }
    }
</script>

<style scoped>

</style>