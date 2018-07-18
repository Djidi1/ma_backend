<template>
    <div style="width: 100%; height: 100%">
        <v-dialog v-model="dialog" persistent max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12>
                                <v-select
                                        :items="filteredObjects"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.object_id"
                                        :label="$t('object')"
                                        required
                                        autocomplete
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="checklists"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.checklist_id"
                                        :label="$t('checklist')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="users"
                                        item-text = "name"
                                        item-value = "id"
                                        v-model="editedItem.user_id"
                                        :label="$t('auditor')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-menu
                                        ref="picker"
                                        v-model="picker"
                                        :nudge-right="40"
                                        lazy
                                        transition="scale-transition"
                                        offset-y
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
                                            :min="today"
                                            no-title 
                                            scrollable
                                            @input="$refs.picker.save(editedItem.date)"
                                    >
                                    </v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('comment')" v-model="editedItem.title"></v-text-field>
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
        <v-dialog
                v-model="dialog_results"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
                persistent>
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon dark @click.native="dialog_results = false">
                        <v-icon>arrow_back</v-icon>
                    </v-btn>
                    <v-toolbar-title>{{ $t('results') }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <results ref="results"></results>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
            <v-card-title>
                <v-select
                        :items="object_groups"
                        v-model="object_select"
                        :label = "$t('object_groups')"
                        item-text = "title"
                        item-value = "id"
                        autocomplete
                ></v-select>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>
            </v-card-title>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn-toggle v-model="toggle_multiple" multiple>
                    <v-btn flat class="mx-0" :value="1">
                        <v-icon color="teal">done</v-icon>
                    </v-btn>
                    <v-btn flat class="mx-0" :value="2">
                        <v-icon color="orange">clear</v-icon>
                    </v-btn>
                    <v-btn flat class="mx-0" :value="0">
                        ALL
                    </v-btn>
                </v-btn-toggle>
            </v-card-actions>
            <ag-grid-vue style="width: 100%;"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="filteredItems"
                         :enableColResize="true"
            >
            </ag-grid-vue>
            <v-alert :value="true" outline color="info" icon="info">
                <b class="blue--text">N</b> - аудит не проводился (запланирован)<br/>
                <b class="orange--text">N</b> - выявлены несоответствия требованиям <br/>
                <b class="green--text">N</b> - закрытый аудит <br/>
            </v-alert>
        </v-card>
    </div>
</template>

<script>
    import {AgGridVue} from "ag-grid-vue";
    import Vue from "vue";
    import Results from './index_results.vue';

    const ActionButtons = Vue.extend({
        template: `<span>
                <v-btn small icon class="mx-0 my-0" @click="editItem" v-if="params.data.audit_result.length === 0"><v-icon color="teal">edit</v-icon></v-btn>
                <v-btn small icon class="mx-0 my-0" @click="deleteItem" v-if="params.data.audit_result.length === 0"><v-icon color="pink">delete</v-icon></v-btn>
                <v-btn small icon class="mx-0 my-0" @click="openResult" v-if="params.data.audit_result.length > 0"><v-icon color="blue">open_in_browser</v-icon></v-btn>
        </span>`,
        methods: {
            editItem() {
                this.params.context.componentParent.editItem(this.params.data);
            },
            deleteItem() {
                this.params.context.componentParent.deleteItem(this.params.data);
            },
            openResult() {
                this.params.context.componentParent.openResult(this.params.value);
            }
        }
    });


    export default {
        data() {
            return {
                dialog: false,
                dialog_results: false,
                picker: false,
                date: '',
                loading: true,
                search: '',
                title: '',
                items: [],
                checklists: [],
                object_select: 0,
                object_selected: 0,
                object_groups: [],
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
                gridOptions: {},
                columnDefs: null,
                rowData: null,
                params: null,
                toggle_multiple: [0],
                errors: []
            }
        },
        components: {
            'ag-grid-vue': AgGridVue,
            'results': Results
        },
        computed: {
            today() {
                return moment(new Date()).format('YYYY-MM-DD');
            },
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            },
            filteredObjects() {
                return this.objects.filter(object => {
                    return parseInt(object.audit_object_group_id) === this.object_selected || this.object_selected === 0
                })
            },
            filteredItems() {
                return this.items.filter(item => {
                    let good_results = 0;
                    for (let result in item.audit_result) {
                        if (item.audit_result.hasOwnProperty(result) && parseInt(item.audit_result[result].result) === 1) {
                            good_results++;
                        }
                    }
                    // фильтр по статусу
                    let filter_status = true;
                    if (item.task !== null) {
                        if (this.toggle_multiple.length === 1 && this.toggle_multiple.indexOf(0) === 0) {
                            filter_status = true;
                        } else if (this.toggle_multiple.indexOf(1) > -1 && good_results === item.audit_result.length) {
                            filter_status = true;
                        } else if (this.toggle_multiple.indexOf(2) > -1 && good_results < item.audit_result.length) {
                            filter_status = true;
                        } else {
                            filter_status = false;
                        }
                    }
                    // фильтр по выбранной группе
                    return filter_status && (parseInt(item.audit_object.audit_object_group_id) === this.object_selected || this.object_selected === 0)
                })
            }
        },
        watch: {
            dialog(val) {
                val || this.close()
            },
            object_select: function (newVal) {
                this.object_selected = newVal;
            },
            toggle_multiple: function (new_value, old_value) {
                // Если новое значение не равно старому и не установлено одно значение
                if (new_value !== old_value && this.toggle_multiple.length !== 1 ) {
                    if (new_value.length === 0) {
                        this.toggle_multiple = [0]
                    } else {
                        if (old_value.length === 1 && old_value.indexOf(0) === 0 && new_value.length > 1){
                            let index = this.toggle_multiple.indexOf(0);
                            if (index !== -1) this.toggle_multiple.splice(index, 1);
                        } else if (new_value.length > 1 && new_value.indexOf(0) > -1){
                            this.toggle_multiple = [0]
                        }
                    }                 
                }
            }
        },
        methods: {
            countResults(results) {
                let good_results = 0;
                for (let result in results) {
                    if (results.hasOwnProperty(result) && results[result].result === 1) {
                        good_results++;
                    }
                }
                return good_results;
            },
            openResult(id) {
                this.$refs.results.getItems(id);
                this.dialog_results = true;
            },
            frontEndDateFormat: function(date) {
                return moment(date, 'YYYY-MM-DD').format('DD.MM.YYYY');
            },
            backEndDateFormat: function(date) {
                return moment(date, 'DD.MM.YYYY').format('YYYY-MM-DD');
            },
            getItems() {
                this.gridOptions.api.showLoadingOverlay();
                axios.get('/audits_all')
                    .then(response => {
                        this.items = response.data.audits;
                        this.object_selected = /*this.items.hasOwnProperty(0) ? (this.items[0].audit_object.audit_object_group_id || 0) :*/ 0;
                        this.object_select = parseInt(this.object_selected);
                        this.checklists = response.data.checklists;
                        this.object_groups = [{id: 0, title: this.$t('all')}].concat(response.data.object_groups);
                        this.objects = response.data.objects;
                        this.users = response.data.users;
                        this.loading = false;
                        this.gridOptions.api.sizeColumnsToFit();
                        this.gridOptions.api.hideOverlay();
                    });
                this.columnDefs = [
                    {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
                    {headerName: this.$t('title'), width: 90, suppressSizeToFit: true, align: 'left', field: 'audit_object.title'},
                    {
                        headerName: this.$t('checklist'), align: 'left', field: 'checklist.title', 
                        tooltipField: 'checklist.title', enableRowGroup: true
                    },
                    {
                        headerName: this.$t('auditor'), align: 'left', valueGetter: function (params) {
                            return params.data.user.name
                        },
                        enableRowGroup: true
                    },
                    {
                        headerName: this.$t('nonconformity'), cellStyle: {textAlign: "center"}, field: 'audit_result',
                        cellRenderer: function(params) {
                            let good_results = 0;
                            let results = params.value;
                            for (let result in results) {
                                if (results.hasOwnProperty(result) && parseInt(results[result].result) === 1) {
                                    good_results++;
                                }
                            }
                            let color = params.value.length === 0 ? 'blue' : (good_results === params.value.length ? 'green' : 'orange');
                            return '<b class="' + color + '--text">' + (params.value.length - good_results) +'</b>';
                        }
                    },
                    {headerName: this.$t('comment'), align: 'left', field: 'comment'},
                    {headerName: this.$t('date'), align: 'left', field: 'date', enableRowGroup: true},
                    {
                        headerName: this.$t('actions'), field: 'id',
                        cellStyle: {textAlign: "center"},
                        cellRendererFramework: ActionButtons,
                        suppressFilter: true,
                        suppressSorting: true,
                        colId: "params",
                        suppressCellSelection: true
                    }
                ];
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
                        this.items.splice(index, 1);
                        this.gridOptions.api.refreshCells();
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
                            Object.assign(this.items[item_index], response.data);
                            this.gridOptions.api.refreshCells();
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    let new_item = this.editedItem;
                    new_item.date_add = new_item.date;
                    axios.post(`/audits_save`, new_item)
                        .then(response => {
                            this.items.push(response.data);
                            this.gridOptions.api.refreshCells();
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
                enableSorting: true,
                domLayout: 'autoHeight',
                rowGroupPanelShow: 'always',
            };
        },
        mounted() {
            this.getItems();
        }
    }
</script>
