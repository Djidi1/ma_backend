<template>
  <div style="width: 100%; height: 100%">
    <v-dialog v-model="dialog" persistent max-width="700px">
      <v-card>
        <v-card-title>
          <span class="headline">{{ formTitle }}</span>
        </v-card-title>
        <v-card-text>
          <v-container grid-list-md>
            <v-layout wrap>
              <v-flex xs12>
                <v-text-field
                  :label="$t('title')"
                  :rules="[rules.required('title', editedItem.title)]"
                  v-model="editedItem.title"
                  required
                ></v-text-field>
              </v-flex>
              <v-flex xs12>
                <v-select
                  :items="groups"
                  item-text="title"
                  item-value="id"
                  v-model="editedItem.cl_category_id"
                  :label="$t('checklist_categories')"
                  required
                  :rules="[rules.required('category', editedItem.cl_category_id)]"
                ></v-select>
              </v-flex>
              <v-flex xs12>
                <v-text-field :label="$t('search')" v-model="searchReq"></v-text-field>
              </v-flex>
            </v-layout>
            <v-layout wrap>
              <v-flex xs6>
                <h3>Все требования</h3>
                <draggable
                  class="transfer-list"
                  v-bind="dragOptions"
                  :list="filterBy(requirements, searchReq, 'title')"
                  :clone="onReqClone"
                  :group="{ name: 'checklists', pull: 'clone', put: () => true }"
                >
                  <div
                    :title="element.title"
                    class="transfer-list-item"
                    v-for="element in filterBy(requirements, searchReq, 'title')"
                    :key="element.id"
                  >
                    <span
                      :class="(element.warning_level < 5 ? 'green--text' : (element.warning_level < 8 ? 'orange--text' : 'red--text'))"
                    >
                      <b>{{element.warning_level}}</b>
                    </span>
                    &nbsp;&nbsp;
                    {{ element.title }}
                  </div>
                </draggable>
              </v-flex>
              <v-flex xs6>
                <h3>Требавания чек-листа</h3>
                <draggable
                  class="transfer-list"
                  :list="editedItem.requirements"
                  group="checklists"
                  v-bind="dragOptions"
                >
                  <div
                    :title="element.title"
                    class="transfer-list-item"
                    v-for="element in editedItem.requirements"
                    :key="element.id"
                  >
                    <span
                      :class="(element.warning_level < 5 ? 'green--text' : (element.warning_level < 8 ? 'orange--text' : 'red--text'))"
                    >
                      <b>{{element.warning_level}}</b>
                    </span>
                    &nbsp;&nbsp;
                    {{ element.title }}
                  </div>
                </draggable>
              </v-flex>
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="pink darken-1" flat @click.native="close">{{ $t('cancel') }}</v-btn>
          <v-btn
            color="blue darken-1"
            :disabled="!validForm"
            flat
            @click.native="save"
          >{{ $t('save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="alertDialog" max-width="290">
      <v-card>
        <v-card-title class="headline">{{this.alertDialogHeader}}</v-card-title>
        <v-card-text>{{this.alertDialogMessage}}</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" flat="flat" @click="alertDialog = false">ОК</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-card fluid fill-height fill-width style="height: 100%">
      <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
      <v-card-title>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          dark
          slot="activator"
          @click="cloneList()"
          class="mb-2"
        >{{$t('copy')}}</v-btn>
        <v-btn
          v-if="$auth.user().role_id !== 2"
          color="primary"
          dark
          slot="activator"
          @click="dialog = true"
          class="mb-2"
        >{{$t('new_item')}}</v-btn>
      </v-card-title>
      <ag-grid-vue
        style="width: 100%;"
        class="ag-theme-balham"
        :gridOptions="gridOptions"
        :columnDefs="columnDefs"
        :rowData="items"
        :enableColResize="true"
        :suppressRowClickSelection="true"
        :rowSelection="rowSelection"
      ></ag-grid-vue>
      <resize-observer @notify="handleResize"/>
    </v-card>
    <v-snackbar color="info" v-model="snackbar" top right :timeout="2000">
      {{ snackbarMessage }}
      <v-btn dark flat @click="snackbar = false">ОК</v-btn>
    </v-snackbar>
  </div>
</template>

<script>
import { AgGridVue } from "ag-grid-vue";
import draggable from "vuedraggable";
import formValidationMixin from "../../mixins/formValidation";
import Vue2Filters from "vue2-filters";
import Vue from "vue";

const ActionButtons = Vue.extend({
  template: `<span>
                    <v-btn small icon class="mx-0 my-0" @click="editItem"><v-icon color="teal">edit</v-icon></v-btn>
                    <v-btn small icon class="mx-0 my-0" @click="deleteItem" v-if="params.data.requirements.length === 0"><v-icon color="pink">delete</v-icon></v-btn>

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
      alertDialog: false,
      alertDialogHeader: "",
      alertDialogMessage: "",
      loading: true,
      search: "",
      title: "",
      items: [],
      groups: [],
      editedIndex: -1,
      editedItem: {
        title: "",
        requirements: []
      },
      defaultItem: {
        title: "",
        requirements: []
      },
      valid: false,

      gridOptions: {},
      columnDefs: null,
      rowData: null,
      params: null,
      rowSelection: null,
      requirements: [],
      searchReq: "",
      snackbar: false,
      snackbarMessage: ""
    };
  },
  mixins: [formValidationMixin, Vue2Filters.mixin],
  components: {
    "ag-grid-vue": AgGridVue,
    draggable: draggable
  },
  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "requirements",
        disabled: false,
        ghostClass: "ghost"
      };
    },
    formTitle() {
      return this.editedIndex === -1
        ? this.$t("new_item")
        : this.$t("edit_item");
    }
  },
  watch: {
    dialog(val) {
      val || this.close();
    }
  },
  methods: {
    onReqClone(element) {
      let currentItem = this.editedItem.requirements.filter(
        v => v.id === element.id
      );
      if (currentItem.length === 0) {
        return element;
      } else {
        this.snackbarMessage = "Это требование уже добавлено";
        this.snackbar = true;
      }
    },
    showMessage(title = "Внимание", message = "") {
      this.alertDialogHeader = title;
      this.alertDialogMessage = message;
      this.alertDialog = true;
    },
    handleResize() {
      setTimeout(() => {
        this.gridOptions.api.sizeColumnsToFit();
      }, 500);
    },
    getRequirements() {
      axios.get("/requirements_all").then(response => {
        this.requirements = response.data.requirements;
      });
    },
    getItems() {
      axios.get("/checklists_all").then(response => {
        this.items = response.data.checklists;
        this.groups = response.data.checklist_categories;
        this.gridOptions.api.sizeColumnsToFit();
        this.gridOptions.api.hideOverlay();
        this.loading = false;
      });
      this.columnDefs = [
        // {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
        {
          headerName: this.$t("title"),
          align: "left",
          field: "title",
          headerCheckboxSelection: true,
          headerCheckboxSelectionFilteredOnly: true,
          checkboxSelection: true
        },
        {
          headerName: this.$t("checklist_categories"),
          field: "cl_category",
          valueGetter: function(params) {
            return params.data.cl_category.title;
          }
        },
        {
          headerName: this.$t("requirements"),
          field: "requirements",
          width: 90,
          valueGetter: function(params) {
            return params.data.requirements.length || "0";
          }
        },
        this.$auth.user().role_id !== 2
          ? {
              headerName: this.$t("actions"),
              field: "id",
              cellStyle: { textAlign: "center" },
              cellRendererFramework: ActionButtons,
              suppressFilter: true,
              suppressSorting: true,
              colId: "params",
              suppressCellSelection: true
            }
          : {}
      ];
    },
    editItem(item) {
      this.editedIndex = this.items.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.items.indexOf(item);
      this.$confirm(this.$t("sure_delete_item")).then(res => {
        if (res) {
          axios.delete("/checklists_delete/" + item.id);
          this.items.splice(index, 1);
          this.gridOptions.api.refreshCells();
        }
      });
    },
    cloneList() {
      const selectedRows = this.gridOptions.api.getSelectedNodes();
      if (selectedRows.length === 0) {
        this.showMessage(this.$t("checklists"), this.$t("no_items_selected"));
      } else {
        const selectedId = selectedRows.map(item => item.data.id);
        axios
          .post(`/checklists_copy`, { id_list: selectedId })
          .then(response => {
            this.items = [...this.items, ...response.data];
            this.gridOptions.api.refreshCells();
          })
          .catch(e => {
            this.errors.push(e);
          });
      }
    },
    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = { ...this.defaultItem };
        this.editedIndex = -1;
      }, 300);
    },
    save() {
      if (this.editedIndex > -1) {
        let item_index = this.editedIndex;
        let editedItem = this.editedItem;
        let item = { ...this.editedItem };
        item.requirements = item.requirements.map(val => val.id);
        delete item["cl_category"];
        delete item["requirement"];
        axios
          .put("/checklists_update/" + item.id, item)
          .then(response => {
            if (response.data === 1) {
              Object.assign(this.items[item_index], editedItem);
              this.gridOptions.api.refreshCells();
            }
          })
          .catch(e => {
            this.errors.push(e);
          });
      } else {
        let item = { ...this.editedItem };
        item.requirements = item.requirements.map(val => val.id);
        axios
          .post(`/checklists_save`, item)
          .then(response => {
            response.data.requirement = [];
            response.data.cl_category = this.groups.find(
              x => x.id === response.data.cl_category_id
            );
            this.items.push(response.data);
            this.gridOptions.api.refreshCells();
          })
          .catch(e => {
            this.errors.push(e);
          });
      }
      this.close();
    }
  },
  beforeMount() {
    this.gridOptions = {
      context: { componentParent: this },
      suppressDragLeaveHidesColumns: true,
      suppressMakeColumnVisibleAfterUnGroup: true,
      floatingFilter: true,
      enableSorting: true,
      domLayout: "autoHeight",
      rowGroupPanelShow: "always"
    };
    this.rowSelection = "multiple";
  },
  mounted() {
    this.getItems();
    this.getRequirements();
  }
};
</script>

<style scoped>
.transfer-list {
  margin: 10px 0 0 0;
  height: 400px;
  overflow: auto;
}
.transfer-list-item {
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
  padding: 5px;
  /* background: #f8f8f9; */
  border-bottom: solid 1px #dadada;
  cursor: grab;
  transition: all 0.2s;
}
.transfer-list-item:hover {
  background: #f4f4f4;
}
.transfer-list-move {
  transition: transform 0.5s;
}
.ghost {
  opacity: 0.5;
  background: #c8ebfb;
}
</style>

