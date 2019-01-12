<template>
    <div style="width: 100%; height: 100%;">
        <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-card-title>
                <span class="headline">{{ formTitle }}</span>
            </v-card-title>
            <v-card-text>
                <v-container grid-list-md>
                    <v-layout wrap>
                        <v-flex xs12>
                            <v-text-field
                                label="PowerBI URL"
                                v-model="settings.power_bi_url"
                                :counter="500"
                                required
                                :rules="[rules.required('power_bi_url', settings.power_bi_url)]"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field
                                :label="$t('mail_subject')"
                                v-model="settings.mail_subject"
                                :counter="500"
                                required
                                :rules="[rules.required('mail_subject', settings.mail_subject)]"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field
                                :label="$t('mail_body')"
                                v-model="settings.mail_body"
                                :counter="4000"
                                required
                                :rules="[rules.required('mail_body', settings.mail_body)]"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field
                                :label="$t('task_finish_days')"
                                v-model="settings.task_finish_days"
                                required
                                :rules="[rules.required('task_finish_days', settings.task_finish_days)]"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-container grid-list-md>
                    <v-layout wrap>
                        <v-flex xs12>
                            <v-btn color="success" :disabled="!validForm" @click.native="save">{{ $t('save') }}</v-btn>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-actions>
        </v-card>
        <v-snackbar
                v-model="snackbar"
                color="success"
                timeout="3000"
                >
                {{ snackbar_text }}
                <v-btn
                    dark
                    flat
                    @click="snackbar = false"
                >
                    Close
                </v-btn>
            </v-snackbar>
    </div>
</template>

<script>
    import formValidationMixin from "../../mixins/formValidation"

    export default {
        data() {
            return {
                loading: true,
                settings: {power_bi_url: '', mail_subject: '', mail_body: '', task_finish_days: '0'},
                errors: [],
                snackbar: false,
                snackbar_text: 'Настройки успешно сохранены'
            }
        },
        mixins: [
            formValidationMixin
        ],
        methods: {
            getSettings() {
                axios.get('/get-settings')
                    .then(response => {
                        this.settings = response.data.settings;
                        this.loading = false;
                    });
            },
            
            save() {
                this.loading = true;
                axios.put('/update-settings', this.settings)
                    .then(response => {
                        this.loading = false;
                        this.snackbar = true;
                    })
                    .catch(e => {
                        this.errors.push(e)
                    });
            }
        },
        mounted() {
            this.getSettings();
        }
    }
</script>
