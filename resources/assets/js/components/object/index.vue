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
                                <v-text-field :label="$t('title')" :rules="[rules.required('title', editedItem.title)]" v-model="editedItem.title" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                    :items="groups"
                                    item-text = "title"
                                    item-value = "id"
                                    v-model="editedItem.audit_object_group_id"
                                    :label="$t('group')"
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
                                    :return-value.sync="editedItem.archive"
                                >
                                    <v-text-field
                                        slot="activator"
                                        :label="$t('archive_date')"
                                        v-model="editedItem.archive"
                                        prepend-icon="event"
                                        clearable
                                        readonly
                                    ></v-text-field>
                                    <v-date-picker
                                        v-model="editedItem.archive"
                                        first-day-of-week="1"
                                        no-title
                                        scrollable
                                        @input="$refs.picker.save(editedItem.archive)"
                                    >
                                    </v-date-picker>
                                </v-menu>
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
                <v-select
                        :items="groups"
                        v-model="object_group_select"
                        :label = "$t('group')"
                        item-text = "title"
                        item-value = "id"
                        autocomplete
                ></v-select>
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
    import formValidationMixin from "../../mixins/formValidation";
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
                params: null,
                picker: false,
                langListener: ''
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
              await axios.post("/objects_import", fd).then( async response => {
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
                .get("/objects_export", {
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
                    {
                        headerName: this.$t('title'), align: 'left', field: 'title'
                    },
                    {
                        headerName: this.$t('responsible'), field: 'id',
                        valueGetter: function(params) {
                            let responsible_names = [];
                            for(let index in self.responsible) {
                                if (self.responsible.hasOwnProperty(index)) {
                                    let attr = self.responsible[index];
                                    if (attr.object_id.indexOf(params.data.id) > -1){
                                        responsible_names.push(self.responsible[index].user.name);
                                    }
                                }
                            }
                            return (responsible_names.length > 0) ? Array.from(new Set(responsible_names)).join(', ') : '-';
                        }
                    },
                    {
                        headerName: this.$t('audits'), width: 90, cellStyle: {textAlign: "center"}, field: 'audit',
                        valueGetter: function(params) {
                            return params.data.audit.length || '0';
                        }
                    },
                    {
                        headerName: this.$t('archive_date'), align: 'left', field: 'archive',
                        cellStyle: function(params) {                            
                            let color = (moment().add(1, 'd').isAfter(moment(params.data.archive, 'YYYY-MM-DD'), 'day')) ? 'darkred ' : 'inherit';
                            return { color: color};
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
            this.langListener = (e) => {
                if (this.groups.length > 0) {
                    this.groups[0].title = this.$t('all');
                }
            }            
            this.getItems();
            document.addEventListener('langChanged', this.langListener);
        },
        beforeDestroy() {
            document.removeEventListener('langChanged', this.langListener);
        }
    }
</script>
