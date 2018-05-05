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
                                <v-text-field :label="$t('title')" v-model="editedItem.title" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="objects"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.object_id"
                                        :label="$t('object')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="users"
                                        item-text = "name"
                                        item-value = "id"
                                        v-model="editedItem.user_id"
                                        :label="$t('users')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-dialog
                                        ref="picker"
                                        persistent
                                        v-model="picker"
                                        lazy
                                        full-width
                                        width="290px"
                                        :return-value.sync="editedItem.date"
                                >
                                    <v-text-field
                                            slot="activator"
                                            :label="$t('date')"
                                            v-model="editedItem.date"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker
                                            v-model="editedItem.date"
                                            first-day-of-week="1"
                                            scrollable
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="picker = false">{{$t('cancel')}}</v-btn>
                                        <v-btn flat color="primary" @click="$refs.picker.save(editedItem.date)">OK</v-btn>
                                    </v-date-picker>
                                </v-dialog>
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
        <v-dialog v-model="dialog_photo" max-width="800px">
            <v-card>
                <v-carousel :cycle="false" :lazy="true" v-if="carousel">
                    <v-carousel-item v-for="(item,i) in attaches_array" :src="item.file_path" :key="i"></v-carousel-item>
                </v-carousel>
            </v-card>
        </v-dialog>
        <v-card fluid fill-height fill-width>
            <v-card-title>
                <v-spacer></v-spacer>
                <v-text-field
                        append-icon="search"
                        :label="$t('search')"
                        single-line
                        hide-details
                        v-model="search"
                ></v-text-field>
            </v-card-title>
            <v-data-table
                    :no-data-text="$t('no_data')"
                    :headers="headers"
                    :items="items"
                    :search="search"
                    :loading="loading"
                    :rows-per-page-items='[50,100,500,{"text":"All","value":-1}]'
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <tr>
                        <td class="text-xs-right">{{ props.item.id }}</td>
                        <td>{{ props.item.object_title }}</td>
                        <td>{{ props.item.audit_title }}</td>
                        <td>{{ props.item.requrement_title }}</td>
                        <td>{{ frontEndDateFormat(props.item.date_checking) }}</td>
                        <td>{{ props.item.comment }}</td>
                        <td v-html="result_icon(props.item.result)"></td>
                        <td>
                            <v-btn icon
                                   v-on:click='result_attaches(props.item.audit_result_attache)'
                                   v-if="props.item.audit_result_attache.length > 0">
                                <v-badge color="orange">
                                    <span slot="badge">{{ props.item.audit_result_attache.length }}</span>
                                    <v-icon color="blue">photo</v-icon>
                                </v-badge>
                            </v-btn>
                        </td>
                        <td class="justify-center layout px-0">
                            <!--<v-btn icon class="mx-0" @click="openResult(props.item.id)">-->
                                <!--<v-icon color="blue">open_in_browser</v-icon>-->
                            <!--</v-btn>-->
                            <!--<v-btn icon class="mx-0" @click="editItem(props.item)">-->
                                <!--<v-icon color="teal">edit</v-icon>-->
                            <!--</v-btn>-->
                            <!--<v-btn icon class="mx-0" @click="deleteItem(props.item)">-->
                                <!--<v-icon color="pink">delete</v-icon>-->
                            <!--</v-btn>-->
                        </td>
                    </tr>
                </template>
                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </v-data-table>
        </v-card>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                dialog: false,
                dialog_photo: false,
                carousel: false,
                picker: false,
                date: '',
                loading: true,
                search: '',
                headers: [
                    { text: 'id', align: 'right', value: 'id' },
                    { text: 'Object', align: 'left', value: 'object' },
                    { text: 'Audit', align: 'left', value: 'audit' },
                    { text: 'Requirement', align: 'left', value: 'requirement' },
                    { text: 'Date', align: 'left', value: 'date' },
                    { text: 'Comment', align: 'left', value: 'comment' },
                    { text: 'Results', align: 'left', value: 'results' },
                    { text: 'Photo', align: 'left', value: 'photo' },
                    { text: 'Actions', align: 'center', sortable: false, value: '' }
                ],
                title: '',
                items: [],
                requirements: [],
                objects: [],
                users: [],
                editedIndex: -1,
                editedItem: {
                    title: ''
                },
                defaultItem: {
                    title: ''
                },
                valid: false,
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            }
        },
        watch: {
            dialog(val) {
                val || this.close()
            }
        },
        methods: {

            openResult(id) {
                this.$router.push({path: '/audit_results/' + id });
            },
            frontEndDateFormat: function(date) {
                return moment(date, 'YYYY-MM-DD').format('DD.MM.YYYY');
            },
            backEndDateFormat: function(date) {
                return moment(date, 'DD.MM.YYYY').format('YYYY-MM-DD');
            },
            getItems() {
                axios.get('/responsible_tasks')
                    .then(response => {
                        this.items = response.data.responsible_tasks;
                        this.requirements = response.data.requirements;
                        this.objects = response.data.objects;
                        this.users = response.data.users;
                        this.loading = false;
                    });
            },
            result_icon(result) {
                let icon = '<i class="icon grey--text material-icons">remove</i>';
                if (result === -1) {
                    icon = '<i class="icon pink--text material-icons">clear</i>';
                }else if (result === 1){
                    icon = '<i class="icon teal--text material-icons">done</i>';
                }
                return icon
            },
            result_attaches(attaches) {
                this.carousel = false;
                this.attaches_array = attaches;
                this.$nextTick(() => (this.carousel = true));
                this.dialog_photo = true;
            },
            editItem(item) {
                this.editedIndex = this.items.indexOf(item);
                this.editedItem = Object.assign({}, item);
                this.dialog = true
            },

            deleteItem(item) {
                const index = this.items.indexOf(item);
                this.$confirm(this.$t('sure_delete_item')).then(res => {
                    if (res) {
                        axios.delete('/audits_delete/' + item.id);
                        this.items.splice(index, 1)
                    }
                });
            },

            close() {
                this.dialog = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.editedIndex = -1
                }, 300)
            },
            save() {
                if (this.editedIndex > -1) {
                    let item_index = this.editedIndex;
                    let editedItem = this.editedItem;
                    let item = this.editedItem;
                    delete item['audit_object'];
                    delete item['audit_result'];
                    delete item['checklist'];
                    delete item['user'];
                    axios.put('/audits_update/' + item.id, item)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], editedItem);
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    let new_item = this.editedItem;
                    new_item.date_add = new_item.date;
                    axios.post(`/audits_save`, new_item)
                        .then(response => {
                            this.items.push(response.data)
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                }
                this.close()
            }
        },
        mounted() {
            this.getItems();
        }
    }
</script>
