<template>
    <div style="width:100%; height: 100%">
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
                                        :items="users_items"
                                        item-text="name"
                                        item-value="id"
                                        v-model="editedItem.user_id"
                                        label="Users"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="objects_items"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.object_id"
                                        label="Objects"
                                        multiple
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="requirements_items"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.requirement_id"
                                        label="Requirements"
                                        multiple
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
                <v-spacer></v-spacer>
                <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>
            </v-card-title>
            <ag-grid-vue style="width: 100%;"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="items"

                         :enableColResize="true"
                         :enableSorting="true"
                         :enableFilter="true"
            >
            </ag-grid-vue>
        </v-card>
    </div>
</template>

<script>
    import {AgGridVue} from "ag-grid-vue";
    import Vue from "vue";

    const ActionButtons = Vue.extend({
        template: `<span>
                <v-btn small icon class="mx-0 my-0" @click="editItem"><v-icon color="teal">edit</v-icon></v-btn>
                <v-btn small icon class="mx-0 my-0" @click="deleteItem"><v-icon color="pink">delete</v-icon></v-btn>

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
                items: [],
                users_items: [],
                objects_items: [],
                requirements_items: [],
                editedIndex: -1,
                editedItem: {
                    user_id: '',
                    object_id: [],
                    requirement_id: []
                },
                defaultItem: {
                    user_id: '',
                    object_id: [],
                    requirement_id: []
                },
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
            }
        },
        watch: {
            dialog(val) {
                val || this.close()
            }
        },
        methods: {
            getItems() {
                axios.get('/responsible_all')
                    .then(response => {
                        this.items = response.data.responsible;
                        this.users_items = response.data.users;
                        this.objects_items = response.data.objects;
                        this.requirements_items = response.data.requirements;
                        this.gridOptions.api.sizeColumnsToFit();
                        this.gridOptions.api.hideOverlay();
                        this.loading = false;
                    });
                let self = this;
                this.columnDefs = [
                    // {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
                    {
                        headerName: this.$t('user'), field: 'user',
                        cellRenderer: function(params) {
                            return params.value.name;
                        }
                    },
                    {
                        headerName: this.$t('objects'), field: 'object_id',
                        cellRenderer: function(params) {
                            let item_names = [];
                            for(let index in self.objects_items) {
                                if (self.objects_items.hasOwnProperty(index)) {
                                    let attr = self.objects_items[index];
                                    if (params.value.indexOf(attr.id) > -1){
                                        item_names.push(self.objects_items[index].title);
                                    }
                                }
                            }
                            return Array.from(new Set(item_names)).join(', ');
                        }
                    },
                    {
                        headerName: this.$t('requirements'), field: 'requirement_id',
                        cellRenderer: function(params) {
                            let item_names = [];
                            for(let index in self.requirements_items) {
                                if (self.requirements_items.hasOwnProperty(index)) {
                                    let attr = self.requirements_items[index];
                                    if (params.value.indexOf(attr.id) > -1){
                                        item_names.push(self.requirements_items[index].title);
                                    }
                                }
                            }
                            return Array.from(new Set(item_names)).join(', ');
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
                        axios.delete('/responsible_delete/' + item.id);
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
                    let item = this.editedItem;
                    // Object.assign(this.items[this.editedIndex], this.editedItem)
                    axios.put('/responsible_update/' + item.id, this.editedItem)
                        .then(response => {
                            Object.assign(this.items[item_index], item);
                            this.gridOptions.api.refreshCells();
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    axios.post(`/responsible_save`, this.editedItem)
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
