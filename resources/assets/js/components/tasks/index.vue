<template>
    <v-flex style="width: 100%">
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12>
                                <v-text-field :label="$t('comment')" v-model="editedItem.comment" required></v-text-field>
                            </v-flex>
                            <v-flex xs8>
                                <v-select
                                        :items="statuses"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.task_status_id"
                                        :label="$t('status')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs4>
                                <v-text-field :label="$t('done_percent')" v-model="editedItem.done_percent" required></v-text-field>
                            </v-flex>
                            <v-flex xs6>
                                <v-dialog
                                        ref="picker_start"
                                        persistent
                                        v-model="picker_start"
                                        lazy
                                        full-width
                                        width="290px"
                                        :return-value.sync="editedItem.start"
                                >
                                    <v-text-field
                                            slot="activator"
                                            :label="$t('date_start')"
                                            v-model="editedItem.start"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker
                                            v-model="editedItem.start"
                                            first-day-of-week="1"
                                            scrollable
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="picker_start = false">{{$t('cancel')}}</v-btn>
                                        <v-btn flat color="primary" @click="$refs.picker_start.save(editedItem.start)">OK</v-btn>
                                    </v-date-picker>
                                </v-dialog>
                            </v-flex>
                            <v-flex xs6>
                                <v-dialog
                                        ref="picker_end"
                                        persistent
                                        v-model="picker_end"
                                        lazy
                                        full-width
                                        width="290px"
                                        :return-value.sync="editedItem.end"
                                >
                                    <v-text-field
                                            slot="activator"
                                            :label="$t('date_end')"
                                            v-model="editedItem.end"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker
                                            v-model="editedItem.end"
                                            first-day-of-week="1"
                                            scrollable
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="picker_end = false">{{$t('cancel')}}</v-btn>
                                        <v-btn flat color="primary" @click="$refs.picker_end.save(editedItem.end)">OK</v-btn>
                                    </v-date-picker>
                                </v-dialog>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="pink darken-1" flat @click.native="close">{{ $t('cancel') }}</v-btn>
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
                <v-select
                        :items="objects"
                        v-model="object_select"
                        :label = "$t('object')"
                        item-text = "title"
                        item-value = "id"
                ></v-select>
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
                    :items="filteredItems"
                    :search="search"
                    :loading="loading"
                    :rows-per-page-items='[50,100,500,{"text":"All","value":-1}]'
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <tr @click="props.expanded = !props.expanded">
                        <td class="text-xs-right">{{ props.item.id }}</td>
                        <!--<td>{{ props.item.object_title }}</td>-->
                        <td>{{ props.item.audit_title }}</td>
                        <td>{{ props.item.requrement_title }}</td>
                        <td>{{ frontEndDateFormat(props.item.date_checking) }}</td>
                        <td>{{ props.item.comment }}</td>
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
                        <td>
                            <span v-html="result_icon(props.item.result, props.item.task)"></span>
                            <b v-if="props.item.task.hasOwnProperty(0)"> {{ props.item.task[0].done_percent}}%</b>
                        </td>
                        <td class="justify-center layout px-0">
                            <!--<v-btn icon class="mx-0" @click="openResult(props.item.id)">-->
                                <!--<v-icon color="blue">open_in_browser</v-icon>-->
                            <!--</v-btn>-->
                            <v-btn icon class="mx-0" @click.stop="editItem(props.item)">
                                <v-icon color="blue">work</v-icon>
                            </v-btn>
                            <!--<v-btn icon class="mx-0" @click="deleteItem(props.item)">-->
                                <!--<v-icon color="pink">delete</v-icon>-->
                            <!--</v-btn>-->
                        </td>
                    </tr>
                </template>
                <template slot="expand" slot-scope="props">
                    <v-card flat v-if="props.item.task.hasOwnProperty(0)">
                        <v-card-text>
                            <v-layout wrap>
                                <v-flex xs6>{{ $t('comment') }}: <b>{{ props.item.task[0].comment }}</b></v-flex>
                                <v-flex xs2>{{ $t('done_percent') }}: <b>{{ props.item.task[0].done_percent }}</b></v-flex>
                                <v-flex xs2>{{ $t('date_start') }}: <b>{{ props.item.task[0].start }}</b></v-flex>
                                <v-flex xs2>{{ $t('date_end') }}: <b>{{ props.item.task[0].end }}</b></v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </template>
                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </v-data-table>
        </v-card>
    </v-flex>
</template>

<script>
    export default {
        data() {
            return {
                dialog: false,
                dialog_photo: false,
                carousel: false,
                picker_start: false,
                picker_end: false,
                date: '',
                loading: true,
                search: '',
                headers: [
                    { text: 'id', align: 'right', value: 'id' },
                    // { text: this.$t('object'), align: 'left', value: 'object' },
                    { text: this.$t('audit'), align: 'left', value: 'audit' },
                    { text: this.$t('requirement'), align: 'left', value: 'requirement' },
                    { text: this.$t('date'), align: 'left', value: 'date' },
                    { text: this.$t('comment'), align: 'left', value: 'comment' },
                    { text: this.$t('photo'), align: 'left', value: 'photo' },
                    { text: this.$t('results'), align: 'left', value: 'results' },
                    { text: this.$t('actions'), align: 'center', sortable: false, value: '' }
                ],
                title: '',
                items: [],
                requirements: [],
                objects: [],
                object_select: 0,
                object_selected: 0,
                users: [],
                statuses: [],
                editedIndex: -1,
                editedItem: {task_status_id: 1},
                defaultItem: {task_status_id: 1},
                valid: false,
                new_task: false,
            }
        },
        computed: {
            formTitle() {
                return this.new_task ? this.$t('new_item') : this.$t('edit_item')
            },
            filteredItems() {
                return this.items.filter(item => {
                    return item.object_id === this.object_selected
                })
            }
        },
        watch: {
            dialog(val) {
                val || this.close()
            },
            object_select: function (newVal) {
                this.object_selected = newVal;
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
                        this.object_selected = this.items[0].object_id || 0;
                        this.object_select = this.object_selected;
                        this.requirements = response.data.requirements;
                        this.objects = response.data.objects;
                        this.users = response.data.users;
                        this.statuses = response.data.statuses;
                        this.loading = false;
                    });
            },
            result_icon(result, task) {
                let icon;
                // Если работы по устранению замечаний завершены
                if (task.hasOwnProperty(0) && task[0].task_status_id === 3) {
                    icon = '<i class="icon teal--text material-icons">done</i>';
                }else if (task.hasOwnProperty(0) && task[0].task_status_id === 2) {
                    icon = '<i class="icon blue--text material-icons">schedule</i>';
                }else {
                    icon = '<i class="icon grey--text material-icons">remove</i>';
                    if (result === -1) {
                        icon = '<i class="icon pink--text material-icons">clear</i>';
                    } else if (result === 1) {
                        icon = '<i class="icon teal--text material-icons">done</i>';
                    }
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
                if (item.task.hasOwnProperty(0)) {
                    this.editedItem = Object.assign({}, item.task[0]);
                    this.new_task = false;
                }else{
                    this.new_task = true;
                }
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
                let self = this;
                let item_index = this.editedIndex;
                if (this.new_task) {
                    let new_item = this.editedItem;
                    new_item.result_id = this.items[this.editedIndex].id;
                    axios.post(`/task_save`, new_item)
                        .then(response => {
                            Object.assign(self.items[item_index].task, {0: response.data});
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    let editedItem = this.editedItem;
                    let item = this.editedItem;
                    axios.put('/task_update/' + item.id, item)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(self.items[item_index].task, {0: editedItem});
                            }
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
<style>
    img.jumbotron__image {
        width: 100%;
    }
</style>