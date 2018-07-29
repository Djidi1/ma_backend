<template>
    <div style="width: 100%; height: 100%;">
        <v-dialog v-model="dialog" persistent scrollable max-width="800px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12 sm6>
                                <v-text-field label="Name" v-model="editedItem.name" :rules="nameRules" required></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-text-field label="Email" v-model="editedItem.email" :rules="emailRules" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field
                                        label="Password"
                                        hint="At least 8 characters"
                                        v-model="editedItem.password"
                                        min="8"
                                        :append-icon="e1 ? 'visibility' : 'visibility_off'"
                                        :append-icon-cb="() => (e1 = !e1)"
                                        :type="e1 ? 'password' : 'text'"
                                        required
                                        counter
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="roles_items"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.role_id"
                                        :label="$t('role')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="object_groups"
                                        item-text="title"
                                        item-value="id"
                                        v-model="object_groups_select"
                                        :label="$t('object_groups')"
                                        autocomplete
                                ></v-select>
                                <v-select
                                        :items="filteredObjectItems"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.responsible.object_id"
                                        :label="$t('objects')"
                                        multiple
                                        required
                                        chips
                                        autocomplete
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="checklists"
                                        item-text="title"
                                        item-value="id"
                                        v-model="checklists_select"
                                        :label="$t('checklist')"
                                        autocomplete
                                ></v-select>
                                <v-select
                                        :items="filteredRequirements"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.responsible.requirement_id"
                                        :label="$t('requirements')"
                                        multiple
                                        required
                                        chips
                                        autocomplete
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
                <!-- fix autocomplete bug -->
                <input title="" name="hidden" type="text" style="width: 0; height: 0; margin: 0; padding: 0"/>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>
            </v-card-title>
            <ag-grid-vue style="width: 100%;"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="items"

                         :enableColResize="true"
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
                <!--<v-btn small icon class="mx-0 my-0" @click="deleteItem"><v-icon color="pink">delete</v-icon></v-btn>-->
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
                roles_items: [],
                editedIndex: -1,
                editedItem: {
                    title: '',
                    password: '',
                    responsible: {object_id:[],requirement_id:[]}
                },
                defaultItem: {
                    title: '',
                    password: '',
                    responsible: {object_id:[],requirement_id:[]}
                },
                object_id: [],
                requirement_id: [],
                objects_items: [],
                object_groups: [],
                object_groups_select: 0,
                object_groups_selected: 0,
                requirements_items: [],
                checklists: [],
                checklists_select: 0,
                checklists_selected: 0,
                valid: false,
                name: '',
                nameRules: [
                    v => !!v || this.$t('name_required')
                ],
                email: '',
                emailRules: [
                    v => !!v || this.$t('email_required'),
                    v => /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || this.$t('email_not_valid')
                ],
                password: '',
                e1: true,

                gridOptions: {},
                columnDefs: null,
                rowData: null,
                params: null,
                errors: []
            }
        },
        components: {
            'ag-grid-vue': AgGridVue
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            },
            filteredObjectItems() {
                return this.objects_items.filter(item => {
                    // фильтр по выбранной группе
                    return item.audit_object_group_id === this.object_groups_selected || this.object_groups_selected === 0
                })
            },
            filteredRequirements() {
                return this.requirements_items.filter(item => {
                    // фильтр по выбранной группе
                    return item.checklist_id === this.checklists_selected || this.checklists_selected === 0
                })
            },
        },
        watch: {
            object_groups_select: function (newVal) {
                this.object_groups_selected = newVal;
            },
            checklists_select: function (newVal) {
                this.checklists_selected = newVal;
            },
            dialog(val) {
                val || this.close()
            }
        },
        methods: {
            getItems() {
                this.gridOptions.api.showLoadingOverlay();
                axios.get('/users_all')
                    .then(response => {
                        this.items = response.data.users;
                        this.roles_items = response.data.roles;
                        this.objects_items = response.data.objects;
                        this.requirements_items = response.data.requirements;
                        this.checklists = response.data.checklists;
                        this.object_groups = response.data.object_groups;
                        this.gridOptions.api.sizeColumnsToFit();
                        this.gridOptions.api.hideOverlay();
                        this.loading = false;
                    });
                let self = this;
                this.columnDefs = [
                    // {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
                    {headerName: this.$t('name'), align: 'left', field: 'name'},
                    {headerName: this.$t('email'), align: 'left', field: 'email'},
                    {
                        headerName: this.$t('group'),
                        valueGetter: function(params) {
                            return params.data.role.title;
                        }
                    },
                    {
                        headerName: this.$t('objects'),
                        valueGetter: function(params) {
                            let value = '-';
                            if (params.data.responsible !== null && params.data.responsible.object_id.length > 0) {
                                let item_names = [];
                                for (let index in self.objects_items) {
                                    if (self.objects_items.hasOwnProperty(index)) {
                                        let attr = self.objects_items[index];
                                        if (params.data.responsible.object_id.indexOf(attr.id) > -1) {
                                            item_names.push(self.objects_items[index].title);
                                        }
                                    }
                                }
                                value = Array.from(new Set(item_names)).join(', ');
                            }
                            return value;
                        },
                        cellRenderer: function (params) {
                            return '<span title="'+params.value+'">'+params.value+'</span>';
                        }

                    },
                    {
                        headerName: this.$t('requirements'),
                        valueGetter: function(params) {
                            let value = '-';
                            if (params.data.responsible !== null && params.data.responsible.requirement_id.length > 0) {
                                let item_names = [];
                                for(let index in self.requirements_items) {
                                    if (self.requirements_items.hasOwnProperty(index)) {
                                        let attr = self.requirements_items[index];
                                        if (params.data.responsible.requirement_id.indexOf(attr.id) > -1){
                                            item_names.push(self.requirements_items[index].title);
                                        }
                                    }
                                }
                                value = Array.from(new Set(item_names)).join(', ');
                            }
                            return value;
                        },
                        cellRenderer: function (params) {
                            return '<span title="'+params.value+'">'+params.value+'</span>';
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
                if (item.responsible === null) {
                    item.responsible = {object_id:[],requirement_id:[]};
                }
                this.editedItem = Object.assign({}, item);
                // Сброс значений в группах
                this.object_groups_selected = 0;
                this.object_groups_select = 0;
                this.checklists_selected = 0;
                this.checklists_select = 0;
                this.dialog = true
            },

            deleteItem(item) {
                const index = this.items.indexOf(item);
                this.$confirm(this.$t('sure_delete_item')).then(res => {
                    if (res) {
                        axios.delete('/users_delete/' + item.id);
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
                this.loading = true;
                if (this.editedIndex > -1) {
                    let item_index = this.editedIndex;
                    let item = this.editedItem;
                    delete item['role'];
                    // Object.assign(this.items[this.editedIndex], this.editedItem)
                    axios.put('/users_update/' + item.id, this.editedItem)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], item);
                                this.gridOptions.api.refreshCells();
                            }
                            this.loading = false;
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    axios.post(`/users_save`, this.editedItem)
                        .then(response => {
                            this.items.push(response.data);
                            this.gridOptions.api.refreshCells();
                            this.loading = false;
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
