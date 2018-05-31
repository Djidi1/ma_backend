<template>
    <div style="width: 100%; height:100%">
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
                                        :items="checklist_groups"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.checklist_id"
                                        :label="$t('checklist')"
                                        required
                                ></v-select>
                            </v-flex>
                            <!--<v-flex xs12>-->
                                <!--<v-select-->
                                        <!--:items="requirement_groups"-->
                                        <!--item-text = "title"-->
                                        <!--item-value = "id"-->
                                        <!--v-model="editedItem.requirement_groups_id"-->
                                        <!--:label="$t('requirement_groups')"-->
                                        <!--required-->
                                <!--&gt;</v-select>-->
                            <!--</v-flex>-->
                            <v-flex xs12>
                                <v-text-field :label="$t('title')" v-model="editedItem.title" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="levels"
                                        v-model="editedItem.warning_level"
                                        :label="$t('level')"
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
                        :items="checklist_groups"
                        v-model="checklist_select"
                        :label = "$t('checklist')"
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
            <v-alert :value="true" outline color="info" icon="info">
                <b class="green--text">1..4</b> - низкий уровень опасности<br/>
                <b class="orange--text">5..7</b> - средний уровень опасности <br/>
                <b class="red--text">8..10</b> - высокий уровень опасности <br/>
            </v-alert>
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
                title: '',
                checklist_select: 0,
                checklist_selected: 0,
                items: [],
                requirement_groups: [],
                checklist_groups: [],
                responsible: [],
                editedIndex: -1,
                editedItem: {
                    title: ''
                },
                defaultItem: {
                    title: ''
                },
                levels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                    return parseInt(item.checklist_id) === this.checklist_selected
                })
            }
        },
        watch: {
            dialog(val) {
                val || this.close()
            },
            checklist_select: function (newVal) {
                this.checklist_selected = newVal;
            }
        },
        methods: {
            getItems() {
                let self = this;
                axios.get('/requirements_all')
                    .then(response => {
                        this.items = response.data.requirements;
                        this.checklist_selected = this.items.hasOwnProperty(0) ? (this.items[0].checklist_id || 0) : 0;
                        this.checklist_select = parseInt(this.checklist_selected);
                        this.requirement_groups = response.data.requirement_groups;
                        this.checklist_groups = response.data.checklist_groups;
                        this.responsible = response.data.responsible;
                        this.loading = false;
                        this.gridOptions.api.sizeColumnsToFit();
                        this.gridOptions.api.hideOverlay();
                    });
                this.columnDefs = [
                    // {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
                    {headerName: this.$t('title'), align: 'left', field: 'title'},
                    {
                        headerName: this.$t('level'), width: 90, cellStyle: {textAlign: "center"}, field: 'warning_level',
                        cellRenderer: function(params) {
                            //:color="props.item.warning_level < 5 ? 'green' : (props.item.warning_level > 7 ? 'red' : 'orange')"
                            let color = params.value < 5 ? 'green' : (params.value > 7 ? 'red' : 'orange');
                            return '<b class="' + color + '--text">' + params.value +'</b>';
                        }
                    },
                    {
                        headerName: this.$t('responsible'), field: 'id',
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
                            return responsible_names.join(', ');
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
                        axios.delete('/requirements_delete/' + item.id);
                        this.gridOptions.api.refreshCells();
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
                    item['requirement_groups_id'] = '0';
                    delete item['cl_category'];
                    delete item['requirement'];
                    axios.put('/requirements_update/' + item.id, item)
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
                    let item = this.editedItem;
                    item['requirement_groups_id'] = '0';
                    axios.post(`/requirements_save`, item)
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
