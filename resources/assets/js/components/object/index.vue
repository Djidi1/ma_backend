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
                                <v-text-field label="Title" v-model="editedItem.title" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="groups"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.audit_object_group_id"
                                        label="Select"
                                        required
                                ></v-select>
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
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
            <v-card-title>
                <v-select
                        :items="groups"
                        v-model="object_group_select"
                        :label = "$t('group')"
                        item-text = "title"
                        item-value = "id"
                        autocomplete
                ></v-select>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>
            </v-card-title>
            <ag-grid-vue style="width: 100%;"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="filteredItems"

                         :enableColResize="true"
                         :enableSorting="true"
                         :enableFilter="true"
            >
            </ag-grid-vue>
            <resize-observer @notify="handleResize" />
        </v-card>
    </div>
</template>

<script>
    import {AgGridVue} from "ag-grid-vue";
    import Vue from "vue";

    const ActionButtons = Vue.extend({
        template: `<span>
                <v-btn small icon class="mx-0 my-0" @click="editItem"><v-icon color="teal">edit</v-icon></v-btn>
                <v-btn small icon class="mx-0 my-0" @click="deleteItem" v-if="params.data.audit.length === 0"><v-icon color="pink">delete</v-icon></v-btn>
        </span>`,
        methods: {
            editItem() {
                this.params.context.componentParent.editItem(this.params.data);
            },
            deleteItem() {
                this.params.context.componentParent.deleteItem(this.params.data);
            }
        }
    });

    export default {
        data() {
            return {
                dialog: false,
                loading: true,
                search: '',
                title: '',
                items: [],
                groups: [],
                responsible: [],
                object_group_select: 0,
                object_group_selected: 0,
                editedIndex: -1,
                editedItem: {
                    title: '',
                    audit_object_group_id: 0
                },
                defaultItem: {
                    title: '',
                    audit_object_group_id: 0
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
                    return parseInt(item.audit_object_group_id) === this.object_group_selected || this.object_group_selected === 0
                })
            }
        },
        watch: {
            dialog(val) {
                val || this.close()
            },
            object_group_select: function (newVal) {
                this.object_group_selected = newVal;
                this.editedItem.audit_object_group_id = newVal;
                this.defaultItem.audit_object_group_id = newVal;
            }
        },
        methods: {
            handleResize () {
                setTimeout(() => {
                    this.gridOptions.api.sizeColumnsToFit();
                }, 500)
            },
            getItems() {
                axios.get('/objects_all')
                    .then(response => {
                        this.items = response.data.objects;
                        this.object_group_selected = /*this.items.hasOwnProperty(0) ? (this.items[0].audit_object_group_id || 0) :*/ 0;
                        this.editedItem.audit_object_group_id = this.object_group_selected;
                        this.defaultItem.audit_object_group_id = this.object_group_selected;
                        this.object_group_select = parseInt(this.object_group_selected);
                        this.groups = [{id: 0, title: this.$t('all')}].concat(response.data.object_groups);
                        this.responsible = response.data.responsible;
                        this.loading = false;
                        this.gridOptions.api.sizeColumnsToFit();
                        this.gridOptions.api.hideOverlay();
                    });
                let self = this;
                this.columnDefs = [
                    // {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
                    // {
                    //     headerName: this.$t('group'), field: 'audit_object_group',
                    //     cellRenderer: function(params) {
                    //         return params.value.title;
                    //     }
                    // },
                    {headerName: this.$t('title'), align: 'left', field: 'title'},
                    {
                        headerName: this.$t('responsible'), field: 'id',
                        cellRenderer: function(params) {
                            let responsible_names = [];
                            for(let index in self.responsible) {
                                if (self.responsible.hasOwnProperty(index)) {
                                    let attr = self.responsible[index];
                                    if (attr.object_id.indexOf(params.value) > -1){
                                        responsible_names.push(self.responsible[index].user.name);
                                    }
                                }
                            }
                            return Array.from(new Set(responsible_names)).join(', ');
                        }
                    },
                    {
                        headerName: this.$t('audits'), width: 90, cellStyle: {textAlign: "center"}, field: 'audit',
                        cellRenderer: function(params) {
                            return params.value.length;
                        }
                    },
                    {
                        headerName: this.$t('actions'), field: 'id',
                        cellStyle: {textAlign: "center"},
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
                        axios.delete('/objects_delete/' + item.id);
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
                    this.loading = true;
                    let item_index = this.editedIndex;
                    let editedItem = this.editedItem;
                    let item = this.editedItem;
                    delete item['cl_category'];
                    delete item['audit_object_group'];
                    delete item['audit'];
                    axios.put('/objects_update/' + item.id, item)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], editedItem);
                                this.gridOptions.api.refreshCells();
                            }
                            this.loading = false;
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    axios.post(`/objects_save`, this.editedItem)
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
