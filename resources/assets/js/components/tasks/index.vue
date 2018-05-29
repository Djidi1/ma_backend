<template>
    <v-flex style="width: 100%; height: 100%">
        <v-dialog
                v-model="dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
                persistent>
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon dark @click.native="dialog = false">
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="save">{{ $t('save') }}</v-btn>
                    </v-toolbar-items>
                </v-toolbar>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12>
                                <v-text-field :label="$t('comment')" v-model="editedItem.comment" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="statuses"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.task_status_id"
                                        :label="$t('status')"
                                        required
                                ></v-select>
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
                            <v-flex xs12>
                                <v-divider></v-divider>
                                <v-list two-line subheader>
                                    <v-subheader>{{ $t('comment') }}</v-subheader>
                                    <template v-for="(item,index) in commentsItem">
                                        <v-list-tile avatar>
                                            <v-list-tile-content>
                                                <v-list-tile-sub-title>{{ item.user.name }}</v-list-tile-sub-title>
                                                <v-list-tile-title>{{ item.message }}</v-list-tile-title>
                                            </v-list-tile-content>
                                            <v-list-tile-action>
                                                <v-icon color="blue lighten-1">create</v-icon>
                                                <v-list-tile-action-text>{{ item.created_at }}</v-list-tile-action-text>
                                            </v-list-tile-action>
                                        </v-list-tile>
                                        <v-divider v-if="index + 1 < items.length" :key="index"></v-divider>
                                    </template>
                                </v-list>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-dialog v-model="dialog_photo" max-width="800px">
            <v-card>
                <v-carousel :cycle="false" :lazy="true" v-if="carousel">
                    <v-carousel-item v-for="(item,i) in attaches_array" :src="item.file_path" :key="i"></v-carousel-item>
                </v-carousel>
            </v-card>
        </v-dialog>
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
            <v-card-title>
                <v-select
                        :items="objects"
                        v-model="object_select"
                        :label = "$t('object')"
                        item-text = "title"
                        item-value = "id"
                        autocomplete
                ></v-select>
                <v-spacer></v-spacer>
                <v-alert v-for :value="true" outline color="info">
                    <b>{{ $t('responsible') }}:</b> <i v-for="(item,i) in responsibleUsers">{{item}}; </i>
                </v-alert>
            </v-card-title>

            <ag-grid-vue style="width: 100%; min-width: 500px"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="filteredItems"

                         :enableColResize="true"
                         :enableSorting="true"
                         :enableFilter="true"
            >
            </ag-grid-vue>
        </v-card>
    </v-flex>
</template>

<script>
    import {AgGridVue} from "ag-grid-vue";
    import Vue from "vue";

    const ActionButtons = Vue.extend({
        template: `<span>
                <v-btn small icon class="mx-0 my-0" @click="editItem"><v-icon color="blue">work</v-icon></v-btn>
        </span>`,
        methods: {
            editItem() {
                this.params.context.componentParent.editItem(this.params.data);
            }
        }
    });

    const Attaches = Vue.extend({
        template: `<span>
                <v-btn  small icon class="mx-0 my-0"
                   v-on:click='result_attaches()'
                   v-if="params.data.audit_result_attache.length > 0">
                    <v-icon color="blue">photo</v-icon>
                </v-btn>
            </span>`,
        methods: {
            result_attaches() {
                this.params.context.componentParent.result_attaches(this.params.data.audit_result_attache);
            },
        }
    });


    /*

     */
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
                title: '',
                items: [],
                requirements: [],
                objects: [],
                responsible: [],
                object_select: 0,
                object_selected: 0,
                users: [],
                statuses: [],
                editedIndex: -1,
                commentsItem: {},
                defaultCommentsItem: {},
                editedItem: {task_status_id: 1},
                defaultItem: {task_status_id: 1},
                valid: false,
                new_task: false,

                gridOptions: {},
                columnDefs: null,
                rowData: null,
                params: null
            }
        },
        components: {
            'ag-grid-vue': AgGridVue
        },
        computed: {
            formTitle() {
                return this.new_task ? this.$t('new_item') : this.$t('edit_item')
            },
            filteredItems() {
                return this.items.filter(item => {
                    return item.object_id === this.object_selected
                })
            },
            responsibleUsers() {
                let responsible_names = [];
                for(let index in this.responsible) {
                    if (this.responsible.hasOwnProperty(index)) {
                        let attr = this.responsible[index];
                        if (attr.object_id.indexOf(this.object_selected) > -1){
                            responsible_names.push(this.responsible[index].user.name);
                        }
                    }
                }
                return [...new Set(responsible_names)];
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
                let self = this;
                axios.get('/responsible_tasks')
                    .then(response => {
                        this.items = response.data.responsible_tasks;
                        this.object_selected = this.items[0].object_id || 0;
                        this.object_select = this.object_selected;
                        this.requirements = response.data.requirements;
                        this.responsible = response.data.responsible;
                        this.objects = response.data.objects;
                        this.users = response.data.users;
                        this.statuses = response.data.statuses;
                        this.gridOptions.api.sizeColumnsToFit();
                        this.gridOptions.api.hideOverlay();
                        this.loading = false;
                    });
                this.columnDefs = [
                    // {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
                    // {headerName: this.$t('title'), suppressSizeToFit: true, align: 'left', field: 'title'},
                    // {headerName: this.$t('audit'), align: 'left', field: 'audit_title', enableRowGroup: true},
                    {headerName: this.$t('requirement'), align: 'left', field: 'requrement_title', enableRowGroup: true},
                    {headerName: this.$t('date'), align: 'left', field: 'date_checking', enableRowGroup: true},
                    {
                        headerName: this.$t('responsible'), field: 'requirement_id',
                        cellRenderer: function(params) {
                            let responsible_names = [];
                            for(let index in self.responsible) {
                                if (self.responsible.hasOwnProperty(index)) {
                                    let attr = self.responsible[index];
                                    if (attr.requirement_id.indexOf(params.value) > -1){
                                        responsible_names.push(self.responsible[index].user.name);
                                    }
                                }
                            }
                            return [...new Set(responsible_names)].join(', ');
                        }
                    },
                    {
                        headerName: this.$t('date_start'), align: 'left', field: 'task',
                        cellRenderer: function (params) {
                            return params.value !== null ? params.value.start : '-';
                        }
                    },
                    {
                        headerName: this.$t('date_end'), align: 'left', field: 'task',
                        cellRenderer: function (params) {
                            return params.value !== null ? params.value.end : '-';
                        }
                    },
                    {headerName: this.$t('comment'), align: 'left', field: 'comment', enableRowGroup: true},
                    {
                        headerName: this.$t('photo'), field: 'id', width: 120,
                        cellStyle: {textAlign: "center"},
                        cellRendererFramework: Attaches,
                        colId: "params",
                        suppressCellSelection: false
                    },
                    {
                        headerName: this.$t('result'), cellStyle: {textAlign: "center"}, field: 'result', width: 120,
                        cellRenderer: function(params) {
                            return self.result_icon(params.value, params.data.task);
                        }
                    },
                    {
                        headerName: this.$t('actions'), field: 'id', width: 90,
                        cellStyle: {textAlign: "center"},
                        cellRendererFramework: ActionButtons,
                        colId: "params",
                        suppressCellSelection: true
                    }
                ];
            },
            result_icon(result, task) {
                let icon;
                // Если работы по устранению замечаний завершены
                if (task !== null && task.task_status_id === 3) {
                    icon = '<i class="icon teal--text material-icons">done</i>';
                }else if (task !== null && task.task_status_id === 2) {
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
                if (item.task !== null) {
                    this.editedItem = Object.assign({}, item.task);
                    this.commentsItem = Object.assign({}, item.task_comments);
                    this.new_task = false;
                }else{
                    this.new_task = true;
                }
                this.dialog = true
            },

            deleteItem(item) {
                let self = this;
                const index = this.items.indexOf(item);
                this.$confirm(this.$t('sure_delete_item')).then(res => {
                    if (res) {
                        axios.delete('/audits_delete/' + item.id);
                        this.items.splice(index, 1);
                        self.gridOptions.api.refreshCells({force: true});
                    }
                });
            },

            close() {
                this.dialog = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.commentsItem = Object.assign({}, this.defaultCommentsItem);
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
                            self.gridOptions.api.refreshCells({force: true});
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
                                self.gridOptions.api.refreshCells({force: true});
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                }
                this.close()
            }
        },
        beforeMount() {
            this.gridOptions = {
                context: { componentParent: this },
                suppressDragLeaveHidesColumns: true,
                suppressMakeColumnVisibleAfterUnGroup: true,
                floatingFilter:true,
                enableFilter: true,
                enableSorting: true,
                suppressMenu: true,
                domLayout: 'autoHeight',
                rowGroupPanelShow: 'always',
            };
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