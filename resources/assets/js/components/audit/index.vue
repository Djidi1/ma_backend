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
                    <v-btn color="pink darken-1" flat @click.native="close">{{ $t('cancel') }}</v-btn>
                    <v-btn color="blue darken-1" flat @click.native="save">{{ $t('save') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>
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
            </v-card-title>
            <ag-grid-vue style="width: 100%; height: 300px;"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="filteredItems"

                         :enableColResize="true"
                         :enableSorting="true"
                         :enableFilter="true"
            >
            </ag-grid-vue>
            <!--<v-data-table-->
                    <!--:no-data-text="$t('no_data')"-->
                    <!--:headers="headers"-->
                    <!--:items="filteredItems"-->
                    <!--:search="search"-->
                    <!--:loading="loading"-->
                    <!--:rows-per-page-items='[50,100,500,{"text":"All","value":-1}]'-->
                    <!--class="elevation-1"-->
            <!--&gt;-->
                <!--<template slot="items" slot-scope="props">-->
                    <!--<tr>-->
                        <!--<td class="text-xs-right">{{ props.item.id }}</td>-->
                        <!--<td>{{ props.item.title }}</td>-->
                        <!--<td>{{ checklists.find(x => x.id === props.item.checklist_id).title }}</td>-->
                        <!--&lt;!&ndash;<td>{{ objects.find(x => x.id === props.item.object_id).title }}</td>&ndash;&gt;-->
                        <!--<td>{{ users.find(x => x.id === props.item.user_id).name }}</td>-->
                        <!--<td>-->
                            <!--<v-chip-->
                                    <!--text-color="white"-->
                                    <!--:color="props.item.audit_result.length === 0-->
                                    <!--? 'blue'-->
                                    <!--: (countResults(props.item.audit_result) === props.item.audit_result.length ? 'green' : 'orange')">-->
                                <!--{{ countResults(props.item.audit_result) }} / {{ props.item.audit_result.length }}-->
                            <!--</v-chip>-->
                        <!--</td>-->
                        <!--<td>{{ props.item.comment }}</td>-->
                        <!--<td>{{ frontEndDateFormat(props.item.date) }}</td>-->
                        <!--<td class="justify-center layout px-0">-->
                            <!--<v-btn icon class="mx-0" @click="openResult(props.item.id)" v-if="props.item.audit_result.length > 0">-->
                                <!--<v-icon color="blue">open_in_browser</v-icon>-->
                            <!--</v-btn>-->
                            <!--<v-btn icon class="mx-0" @click="editItem(props.item)" v-if="props.item.audit_result.length === 0">-->
                                <!--<v-icon color="teal">edit</v-icon>-->
                            <!--</v-btn>-->
                            <!--<v-btn icon class="mx-0" @click="deleteItem(props.item)" v-if="props.item.audit_result.length === 0">-->
                                <!--<v-icon color="pink">delete</v-icon>-->
                            <!--</v-btn>-->
                        <!--</td>-->
                    <!--</tr>-->
                <!--</template>-->
                <!--<v-alert slot="no-results" :value="true" color="error" icon="warning">-->
                    <!--Your search for "{{ search }}" found no results.-->
                <!--</v-alert>-->
            <!--</v-data-table>-->
        </v-card>
    </div>
</template>

<script>
    import {AgGridVue} from "ag-grid-vue";
    import Vue from "vue";

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
                picker: false,
                date: '',
                loading: true,
                search: '',
                headers: [
                    { text: 'id', align: 'right', value: 'id' },
                    { text: this.$t('title'), align: 'left', value: 'name' },
                    { text: this.$t('checklist'), align: 'left', value: 'checklist' },
                    // { text: this.$t('object'), align: 'left', value: 'object' },
                    { text: this.$t('auditor'), align: 'left', value: 'user' },
                    { text: this.$t('results'), align: 'left', value: 'results' },
                    { text: this.$t('comment'), align: 'left', value: 'comment' },
                    { text: this.$t('date'), align: 'left', value: 'date' },
                    { text: this.$t('actions'), align: 'center', sortable: false, value: '' }
                ],
                title: '',
                items: [],
                checklists: [],
                object_select: 0,
                object_selected: 0,
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
                params: null
            }
        },
        components: {
            'ag-grid-vue': AgGridVue
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
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
                this.$router.push({path: '/audit_results/' + id });
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
                        this.object_selected = this.items[0].object_id || 0;
                        this.object_select = this.object_selected;
                        this.checklists = response.data.checklists;
                        this.objects = response.data.objects;
                        this.users = response.data.users;
                        this.loading = false;
                        this.gridOptions.api.sizeColumnsToFit();
                        this.gridOptions.api.hideOverlay();
                    });
                this.columnDefs = [
                    // {headerName: 'id', align: 'right', field: 'id'},
                    {headerName: this.$t('title'), suppressSizeToFit: true, align: 'left', field: 'title'},
                    {headerName: this.$t('checklist'), align: 'left', field: 'checklist.title', enableRowGroup: true},
                    {
                        headerName: this.$t('auditor'), align: 'left', valueGetter: function (params) {
                            return params.data.user.name
                        },
                        enableRowGroup: true
                    },
                    {
                        headerName: this.$t('results'), align: 'left', field: 'audit_result',
                        cellRenderer: function(params) {
                            let good_results = 0;
                            let results = params.value;
                            for (let result in results) {
                                if (results.hasOwnProperty(result) && results[result].result === 1) {
                                    good_results++;
                                }
                            }
                            let color = params.value.length === 0 ? 'blue' : (good_results === params.value.length ? 'green' : 'orange');
                            return '<b class="' + color + '--text">' + good_results + '/' + params.value.length+'</b>';
                        }
                    },
                    {headerName: this.$t('comment'), align: 'left', field: 'comment'},
                    {headerName: this.$t('date'), align: 'left', field: 'date', enableRowGroup: true},
                    {
                        headerName: this.$t('actions'), field: 'id',
                        cellRendererFramework: ActionButtons,
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
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], editedItem);
                                this.gridOptions.api.refreshCells();
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
                rowGroupPanelShow: 'always'
            };
        },
        mounted() {
            this.getItems();
        }
    }
</script>
