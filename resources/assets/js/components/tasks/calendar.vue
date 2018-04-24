<template>
    <div style="width: 100%">
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12>
                                <v-text-field label="Title" v-model="editedItem.title" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="objects"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.object_id"
                                        label="Select"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="checklists"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.checklist_id"
                                        label="Select"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="users"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.user_id"
                                        label="Select"
                                        required
                                ></v-select>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" flat @click.native="close">{{ $t('cancel') }}</v-btn>
                    <v-btn color="blue darken-1" flat @click.native="save">{{ $t('save') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <full-calendar :events="fcEvents" locale="en"></full-calendar>
    </div>
</template>

<script>
    let demoEvents = [
        {
            title : 'Демонстрация MobileAudit',
            start : '2018-04-14',
            end : '2018-04-14'
        }
    ];
    export default {
        data () {
            return {
                dialog: false,
                fcEvents : demoEvents,

                editedItem: {
                    title: ''
                },
                defaultItem: {
                    title: ''
                },
                checklists: [],
                objects: [],
                users: [],
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            }
        },
        methods: {
            getItems() {
                axios.get('/audit_tasks_all')
                    .then(response => {
                        let audit_tasks = response.data;
                        let i;
                        console.log(audit_tasks);
                        for(i = 0; i < audit_tasks.length; i++){
                            if (audit_tasks.hasOwnProperty(i)) {
                                Object.defineProperty(audit_tasks[i], 'start', Object.getOwnPropertyDescriptor(audit_tasks[i], 'date'));
                            }
                        }
                        this.fcEvents = audit_tasks;
                    });
            },
            eventClick(day, jsEvent){
                console.log(day);
                console.log(jsEvent);
            },
        },
        mounted () {
            let d = document.getElementsByClassName('prev-month');
            d[0].className += " btn btn--icon";
            d[0].innerHTML = '<div class="btn__content"><i aria-hidden="true" class="icon blue--text material-icons">keyboard_arrow_left</i></div>';
            let n = document.getElementsByClassName('next-month');
            n[0].className += " btn btn--icon";
            n[0].innerHTML = '<div class="btn__content"><i aria-hidden="true" class="icon blue--text material-icons">keyboard_arrow_right</i></div>';

            this.getItems();
        }
    }
</script>

<style scoped>

</style>