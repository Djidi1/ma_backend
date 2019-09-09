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
                                <v-text-field :label="$t('name')" v-model="editedItem.name" :rules="[rules.required('name', editedItem.name)]"
                                              required></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-select
                                        :items="roles_items"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.role_id"
                                        :label="$t('role')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-select
                                        :items="departments"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.department_id"
                                        :label="$t('department')"
                                        required
                                        :rules="[rules.required('departments', editedItem.department_id)]"
                                ></v-select>
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-select
                                        :items="positions"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.position_id"
                                        :label="$t('position')"
                                        required
                                        :rules="[rules.required('positions', editedItem.position_id)]"
                                ></v-select>
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-text-field label="Email" v-model="editedItem.email" :rules="[rules.required('email', editedItem.email), rules.email('invalidEmail', editedItem.email)]"
                                              required></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6>
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
                                        :rules="(editedItem.id > 0) ? [] :[rules.required('password', editedItem.password, true)]"
                                ></v-text-field>
                            </v-flex>
                            <!-- Ответственные -->

                            <v-flex xs12>
                                <p class="title">{{ $t('responsible_for') }} {{ $t('objects') }}</p>
                                <v-select
                                        :items="grouped_objects"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.responsible.object_id"
                                        :label="$t('objects')"
                                        multiple
                                        chips
                                        autocomplete
                                >
                                    <template slot="selection" slot-scope="data">
                                        <v-chip
                                                :selected="data.selected"
                                                :key="JSON.stringify(data.item)"
                                                close
                                                small
                                                class="chip--select-multi"
                                                @input="data.parent.selectItem(data.item)"
                                        >{{ data.item.title }}
                                        </v-chip>
                                    </template>
                                    <template slot="item" slot-scope="data">
                                        <template v-if="typeof data.item !== 'object'">
                                            <v-list-tile-content v-text="data.item"></v-list-tile-content>
                                        </template>
                                        <template v-else>
                                            <v-list-tile-content>
                                                <v-list-tile-title v-html="data.item.title"></v-list-tile-title>
                                                <v-list-tile-sub-title v-html="data.item.group"></v-list-tile-sub-title>
                                            </v-list-tile-content>
                                        </template>
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-flex xs12>
                                <p class="title">{{ $t('responsible_for') }} {{ $t('requirements') }}</p>
                            </v-flex>
                            <v-flex xs4>
                                <v-select
                                        :items="object_groups"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.object_group_id"
                                        :label="$t('object_groups')"
                                        multiple
                                        chips
                                        autocomplete
                                >
                                    <template slot="selection" slot-scope="data">
                                        <v-chip
                                                :selected="data.selected"
                                                :key="JSON.stringify(data.item)"
                                                close
                                                small
                                                class="chip--select-multi"
                                                @input="data.parent.selectItem(data.item)"
                                        >{{ data.item.title }}
                                        </v-chip>
                                    </template>
                                    <template slot="item" slot-scope="data">
                                        <template v-if="typeof data.item !== 'object'">
                                            <v-list-tile-content v-text="data.item"></v-list-tile-content>
                                        </template>
                                        <template v-else>
                                            <v-list-tile-content>
                                                <v-list-tile-title v-html="data.item.title"></v-list-tile-title>
                                                <v-list-tile-sub-title
                                                        v-html="data.item.group"></v-list-tile-sub-title>
                                            </v-list-tile-content>
                                        </template>
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-flex xs8>
                                <v-select
                                        :items="grouped_requirements"
                                        item-text="title"
                                        item-value="id"
                                        v-model="editedItem.responsible.requirement_id"
                                        :label="$t('requirements')"
                                        multiple
                                        chips
                                        autocomplete
                                >
                                    <template slot="selection" slot-scope="data">
                                        <v-chip
                                                :selected="data.selected"
                                                :key="JSON.stringify(data.item)"
                                                close
                                                small
                                                class="chip--select-multi"
                                                @input="data.parent.selectItem(data.item)"
                                        >{{ data.item.title }}
                                        </v-chip>
                                    </template>
                                    <template slot="item" slot-scope="data">
                                        <template v-if="typeof data.item !== 'object'">
                                            <v-list-tile-content v-text="data.item"></v-list-tile-content>
                                        </template>
                                        <template v-else>
                                            <v-list-tile-content>
                                                <v-list-tile-title v-html="data.item.title"></v-list-tile-title>
                                                <v-list-tile-sub-title
                                                        v-html="data.item.group"></v-list-tile-sub-title>
                                            </v-list-tile-content>
                                        </template>
                                    </template>
                                </v-select>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="pink darken-1" flat @click.native="close">{{ $t('cancel') }}</v-btn>
                    <v-btn color="blue darken-1" :disabled="!validForm" flat @click.native="save">{{ $t('save') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
            <v-card-title>
                <!-- fix autocomplete bug -->
                <input title="" name="hidden" type="text" style="width: 0; height: 0; margin: 0; padding: 0"/>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  dark
                  slot="activator"
                  @click="importForm.visible = true"
                  class="mb-2"
                >
                  {{$t('import')}}
                </v-btn>
                <v-btn
                  color="primary"
                  dark
                  slot="activator"
                  @click="itemsExport()"
                  class="mb-2"
                >
                  {{$t('export')}}
                </v-btn>
                <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}
                </v-btn>
            </v-card-title>
            <ag-grid-vue style="width: 100%;"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="items"

                         :enableColResize="true"
            >
            </ag-grid-vue>
            <resize-observer @notify="handleResize" />
        </v-card>
        <input type="file" style="display: none" ref="file" accept="xlsx" @change="onFilePicked">
    <v-dialog v-model="importForm.visible" width="500">
      <v-card>
        <v-card-title class="headline">{{this.$t('import')}}</v-card-title>
        <v-card-text>
          <v-text-field
            label="Select file"
            @click="pickFile"
            v-model="importForm.file.name"
            prepend-icon="attach_file"
          ></v-text-field>
          <p class="text--red">{{importForm.error}}</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="red darken-1" flat="flat" @click.native="importForm.visible = false">{{this.$t('cancel')}}</v-btn>
          <v-btn color="blue darken-1" flat="flat" @click="importFile" :disabled="!!!importForm.file.name.length">{{this.$t('send')}}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </div>
</template>

<script>
    import {AgGridVue} from "ag-grid-vue";
    import formValidationMixin from "../../mixins/formValidation"
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
                importForm: {
                    visible: false,
                    error: '',
                    file: {
                        name: '',
                        url: '',
                        file: ''
                    }
                },
                dialog: false,
                loading: true,
                search: '',
                items: [],
                roles_items: [],
                departments: [],
                positions: [],
                editedIndex: -1,
                editedItem: {
                    title: '',
                    password: '',
                    object_group_id: [],
                    role_id: 2,
                    responsible: {object_id: [], requirement_id: []}
                },
                defaultItem: {
                    title: '',
                    password: '',
                    object_group_id: [],
                    role_id: 2,
                    responsible: {object_id: [], requirement_id: []}
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
                email: '',
                password: '',
                e1: true,
                gridOptions: {},
                columnDefs: null,
                rowData: null,
                params: null,
                errors: []
            }
        },        
        mixins: [
            formValidationMixin
        ],
        components: {
            'ag-grid-vue': AgGridVue
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            },
            /*
            filteredObjectItems() {
                return this.objects_items.filter(item => {
                    // фильтр по выбранной группе
                    return item.audit_object_group_id === this.object_groups_selected || this.object_groups_selected === 0
                })
            },
            */
            /*  filteredRequirements() {
                  return this.requirements_items.filter(item => {
                      // фильтр по выбранной группе
                      return item.checklist_id === this.checklists_selected || this.checklists_selected === 0
                  })
              },*/
            grouped_objects() {
                let grouped_items = [];
                for (let index in this.object_groups) {
                    let obj_group = this.object_groups[index];
                    // Если в группе есть элементы
                    if (this.objects_items.findIndex(x => x.audit_object_group_id === obj_group.id) > -1) {
                        grouped_items.push({header: obj_group.title});
                    }
                    for (let index in this.objects_items) {
                        // Добавляем элементы в массив
                        if (this.objects_items[index].audit_object_group_id === obj_group.id) {
                            this.objects_items[index]['group'] = obj_group.title;
                            grouped_items.push(this.objects_items[index]);
                        }
                    }
                    grouped_items.push({divider: true});
                }
                return grouped_items;
            },
            grouped_requirements() {
                let grouped_items = {};
                // for (let index in this.checklists) {
                //     let item_group = this.checklists[index];
                //     // Если в группе есть элементы
                //     if (this.requirements_items.findIndex(x => x.checklist_id === item_group.id) > -1) {
                //         grouped_items.push({header: item_group.title});
                //     }
                //     for (let index in this.requirements_items) {
                //         // Добавляем элементы в массив
                //         if (this.requirements_items[index].checklist_id === item_group.id) {
                //             this.requirements_items[index]['group'] = item_group.title;
                //             grouped_items.push(this.requirements_items[index]);
                //         }
                //     }
                //     grouped_items.push({divider: true});
                // }                
                return this.requirements_items;
            }
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
            pickFile() {
              this.$refs.file.click();
            },
            onFilePicked(e) {
              const files = e.target.files;
              if (files[0] !== undefined) {
                this.importForm.file.name = files[0].name;
                if (this.importForm.file.name.lastIndexOf(".") <= 0) {
                  return false;
                }
                const fr = new FileReader();
                fr.readAsDataURL(files[0]);
                fr.addEventListener("load", () => {
                  this.importForm.file.url = fr.result;
                  this.importForm.file.file = files[0]; // this is an image file that can be sent to server...
                });
              } else {
                this.importForm.file.name = "";
                this.importForm.file.url = "";
                this.importForm.file.file = "";
              }
            },    
            async importFile () {      
              let fd = new FormData;
              fd.append('file', this.$refs.file.files[0]);
              await axios.post("/users_import", fd).then( async response => {
                if (response.status === 200) {
                  this.importForm.error = '';
                  this.importForm.visible = false;
                  await this.getItems();
                  this.checklist_select = this.checklist_groups[0]["id"] || 0;
                } else {
                  this.importForm.error = response.data.message;
                }
              });
            },
            async itemsExport() {
              let self = this;
              await axios
                .get("/users_export", {
                  responseType: "arraybuffer"
                })
                .then(response => {
                  let filename = "";
                  let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                  let matches = filenameRegex.exec(
                    response.headers["content-disposition"]
                  );
                  if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, "");
                  }
                  const url = window.URL.createObjectURL(
                    new Blob([response.data], {
                      type: response.headers["content-type"]
                    })
                  );
                  const link = document.createElement("a");
                  link.href = url;
                  link.download = filename;
                  document.body.appendChild(link);
                  link.click();
                });
            },
            handleResize () {
                setTimeout(() => {
                    this.gridOptions.api.sizeColumnsToFit();
                }, 500)
            },
            getItems() {
                this.gridOptions.api.showLoadingOverlay();
                axios.get('/users_all')
                    .then(response => {
                        this.items = response.data.users;
                        this.roles_items = response.data.roles;
                        this.departments = response.data.departments;
                        this.positions = response.data.positions;
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
                        valueGetter: function (params) {
                            return self.roles_items.find(x => x.id === params.data.role_id).title;
                        }
                    },
                   /* {
                        headerName: this.$t('work_in'),
                        valueGetter: function (params) {
                            let value = '-';
                            if (params.data.object_id !== null && params.data.object_id.length > 0) {
                                let item_names = [];
                                for (let index in self.objects_items) {
                                    if (self.objects_items.hasOwnProperty(index)) {
                                        let attr = self.objects_items[index];
                                        if (params.data.object_id.indexOf(attr.id) > -1) {
                                            item_names.push(self.objects_items[index].title);
                                        }
                                    }
                                }
                                value = Array.from(new Set(item_names)).join(', ');
                            }
                            return value;
                        }
                    },*/
                    {
                        headerName: this.$t('objects'),
                        valueGetter: function (params) {
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
                            return '<span title="' + params.value + '">' + params.value + '</span>';
                        }

                    },
                    {
                        headerName: this.$t('requirements'),
                        valueGetter: function (params) {
                            let value = '-';
                            if (params.data.responsible !== null && params.data.responsible.requirement_id.length > 0) {
                                let item_names = [];
                                for (let index in self.requirements_items) {
                                    if (self.requirements_items.hasOwnProperty(index)) {
                                        let attr = self.requirements_items[index];
                                        if (params.data.responsible.requirement_id.indexOf(attr.id) > -1) {
                                            item_names.push(self.requirements_items[index].title);
                                        }
                                    }
                                }
                                value = Array.from(new Set(item_names)).join(', ');
                            }
                            return value;
                        },
                        cellRenderer: function (params) {
                            return '<span title="' + params.value + '">' + params.value + '</span>';
                        }
                    },
                    {
                        headerName: this.$t('actions'), field: 'id',
                        cellStyle: {textAlign: "center"},
                        cellRendererFramework: ActionButtons,
                        colId: "params",
                        sortable: false,
                        suppressCellSelection: true
                    }
                ];
            },
            editItem(item) {
                this.editedIndex = this.items.indexOf(item);
                if (item.responsible === null) {
                    item.responsible = {object_id: [], requirement_id: []};
                }
                if (item.object_group_id === null) {
                    item.object_group_id = [];
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
                let self = this;
                if (this.editedIndex > -1) {
                    let item_index = this.editedIndex;
                    let item = this.editedItem;
                    delete item['role'];
                    delete item['position'];
                    delete item['department'];
                    // Object.assign(this.items[this.editedIndex], this.editedItem)
                    axios.put('/users_update/' + item.id, this.editedItem)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], self.editedItem);
                                self.gridOptions.api.refreshCells();
                                this.close()
                            }
                            self.loading = false;
                        })
                        .catch(e => {
                            self.errors.push(e)
                        });
                } else {
                    axios.post(`/users_save`, self.editedItem)
                        .then(response => {
                            self.items.push(response.data);
                            self.gridOptions.api.refreshCells();
                            self.loading = false;
                            this.close()
                        })
                        .catch(e => {
                            self.errors.push(e)
                        });
                }
            }
        },
        beforeMount() {
            this.gridOptions = {
                context: {componentParent: this},
                suppressDragLeaveHidesColumns: true,
                suppressMakeColumnVisibleAfterUnGroup: true,
                floatingFilter: true,
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
