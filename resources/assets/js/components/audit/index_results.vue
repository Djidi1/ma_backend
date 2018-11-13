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
                                        :items="objects"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.object_id"
                                        label="Select"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="checklists"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.checklist_id"
                                        label="Select"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="users"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.user_id"
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

        <v-dialog v-model="dialog_photo" max-width="800px">
            <!--<v-btn color="primary"-->
                   <!--absolute-->
                   <!--small-->
                   <!--fab-->
                   <!--top-->
                   <!--right-->
                   <!--@click.stop="dialog_photo=false"><v-icon color="grey">clear</v-icon></v-btn>-->
            <v-card>
                <v-carousel :cycle="false" :lazy="true" v-if="carousel">
                    <v-carousel-item v-for="(item,i) in attaches_array" :src="item.file_path" :key="i"></v-carousel-item>
                </v-carousel>
            </v-card>
        </v-dialog>

        <v-card fluid fill-height fill-width style="height: 100%">
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
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
            <resize-observer @notify="handleResize" />
        </v-card>
    </div>
</template>

<script>
    import {AgGridVue} from "ag-grid-vue";
    import Vue from "vue";

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
    export default {
        data() {
            return {
                dialog: false,
                dialog_photo: false,
                loading: true,
                carousel: true,
                search: '',
                title: '',
                audit_select: null,
                items: [],
                attaches_array: [],
                checklists: [],
                objects: [],
                users: [],
                audits: [],
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
        props: ['result_id'],
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            }
        },
        watch: {
            dialog(val) {
                val || this.close()
            }/*,
            audit_select(val){
                this.getItems(val)
            }*/
        },
        methods: {
            handleResize () {
                setTimeout(() => {
                    this.gridOptions.api.sizeColumnsToFit();
                }, 500)
            },
            getItems(audit_id) {
                this.loading = true;
                let audit_id_value = audit_id;
                let self = this;
                if (audit_id > 0) {
                    axios.get('/audit_results_all/' + audit_id)
                        .then(response => {
                            this.items = response.data.audit_results;
                            this.audits = response.data.audits;
                            this.audit_select = parseInt(audit_id_value);
                            this.gridOptions.api.sizeColumnsToFit();
                            this.gridOptions.api.hideOverlay();
                            this.loading = false;
                        });
                    this.columnDefs = [
                        // {headerName: 'id', width: 90, field: 'id', cellStyle: {textAlign: "right"}},
                        {
                            headerName: this.$t('requirement'),
                            cellStyle: {'white-space': 'normal', 'line-height': 'normal'},
                            minWidth: 120,
                            align: 'left',
                            field: 'requirement.title',
                            tooltipField: 'requirement.title'
                        },
                        {
                            headerName: this.$t('comment'),
                            cellStyle: {'white-space': 'normal', 'line-height': 'normal'},
                            minWidth: 120,
                            align: 'left',
                            field: 'comment'
                        },
                        {
                            headerName: this.$t('date'),
                            align: 'left',
                            minWidth: 90,
                            valueGetter: function (params) {
                                return moment(params.data.created_at).format('DD.MM.YYYY');
                            }
                        },
                        {
                            headerName: this.$t('result'),
                            cellStyle: {textAlign: "center"},
                            field: 'result',
                            minWidth: 90,
                            suppressFilter: true,
                            suppressSorting: true,
                            cellRenderer: function (params) {
                                return self.result_icon(parseInt(params.value), params.data.task);
                            }
                        },
                        {
                            headerName: this.$t('photo'), field: 'id',
                            cellStyle: {textAlign: "center"},
                            minWidth: 90,
                            cellRendererFramework: Attaches,
                            suppressFilter: true,
                            suppressSorting: true,
                            colId: "params",
                            suppressCellSelection: true
                        }
                    ];
                }
            },
            result_icon(result) {
                let icon = '<i class="icon grey--text material-icons">remove</i>';
                if (result === -1) {
                    icon = '<i class="icon pink--text material-icons">clear</i>';
                }else if (result === 1){
                    icon = '<i class="icon teal--text material-icons">done</i>';
                }
                return icon
            },
            result_attaches(attaches) {
                this.carousel = false;
                this.attaches_array = attaches;
                this.$nextTick(() => (this.carousel = true));
                this.dialog_photo = true;
            },


            close() {
                this.dialog = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.editedIndex = -1
                }, 300)
            },

        },
        beforeMount() {
            this.gridOptions = {
                context: { componentParent: this },
                floatingFilter:true,
                enableFilter: true,
                enableSorting: true,
                domLayout: 'autoHeight',
                getRowHeight: function(params) {
                    return 32 * (Math.floor(params.data.requirement.title.length / 45) + 1);
                },
            };
        },
        mounted() {
            let audit_id = this.$route.params.id;
            this.getItems(audit_id);
        }
    }
</script>
<style>
    img.jumbotron__image {
        width: 100%;
    }
</style>