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
                    <v-btn color="blue darken-1" flat @click.native="close">{{ $t('cancel') }}</v-btn>
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

        <v-card fluid fill-height fill-width>
            <v-card-title>
                <!--<v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>-->
                <!--<v-select
                        :items="audits"
                        v-model="audit_select"
                        :label = "$t('audits')"
                        item-text = "title"
                        item-value = "id"
                        @change="getItems(audit_select.id)"
                        autocomplete
                ></v-select>-->
                <v-spacer></v-spacer>
                <v-text-field
                        append-icon="search"
                        :label="$t('search')"
                        single-line
                        hide-details
                        v-model="search"
                ></v-text-field>
            </v-card-title>
            <v-data-table
                    :no-data-text="$t('no_data')"
                    :headers="headers"
                    :items="items"
                    :search="search"
                    :loading="loading"
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <td class="text-xs-right">{{ props.item.id }}</td>
                    <td>{{ props.item.requirement.title }}</td>
                    <td>{{ props.item.comment }}</td>
                    <td>{{ props.item.created_at }}</td>
                    <td v-html="result_icon(props.item.result)"></td>
                    <td>
                        <v-btn icon
                               v-on:click='result_attaches(props.item.audit_result_attache)'
                               v-if="props.item.audit_result_attache.length > 0">
                            <v-badge color="orange">
                                <span slot="badge">{{ props.item.audit_result_attache.length }}</span>
                                <v-icon color="blue">photo</v-icon>
                            </v-badge>
                        </v-btn>
                    </td>
                    <!--<td class="justify-center layout px-0">-->
                        <!--<v-btn icon class="mx-0" @click="editItem(props.item)">-->
                            <!--<v-icon color="teal">edit</v-icon>-->
                        <!--</v-btn>-->
                        <!--<v-btn icon class="mx-0" @click="deleteItem(props.item)">-->
                            <!--<v-icon color="pink">delete</v-icon>-->
                        <!--</v-btn>-->
                    <!--</td>-->
                </template>
                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </v-data-table>
        </v-card>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                dialog: false,
                dialog_photo: false,
                loading: true,
                carousel: true,
                search: '',
                headers: [
                    { text: 'id', align: 'right', value: 'id' },
                    { text: 'Requirement', align: 'left', value: 'requirement' },
                    { text: 'Comment', align: 'left', value: 'comment' },
                    { text: 'Date', align: 'left', value: 'date' },
                    { text: 'Result', align: 'left', value: 'result' },
                    { text: 'Photos', align: 'left', value: 'photos' },
                    // { text: 'Actions', align: 'center', sortable: false, value: '' }
                ],
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

            }
        },
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
            getItems(audit_id) {
                this.loading = true;
                let audit_id_value = audit_id;
                axios.get('/audit_results_all/'+audit_id)
                    .then(response => {
                        this.items = response.data.audit_results;
                        this.audits = response.data.audits;
                        this.audit_select = parseInt(audit_id_value);
                        this.loading = false;
                    });
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
            editItem(item) {
                this.editedIndex = this.items.indexOf(item);
                this.editedItem = Object.assign({}, item);
                this.dialog = true
            },

            deleteItem(item) {
                const index = this.items.indexOf(item);
                this.$confirm(this.$t('sure_delete_item')).then(res => {
                    if (res) {
                        axios.delete('/checklists_delete/' + item.id);
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
                    delete item['cl_category'];
                    delete item['requirement'];
                    axios.put('/checklists_update/' + item.id, item)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], editedItem);
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    axios.post(`/checklists_save`, this.editedItem)
                        .then(response => {
                            this.items.push(response.data)
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                }
                this.close()
            }
        },
        mounted() {
            let audit_id = this.$route.params.id;
            this.getItems(audit_id);
        }
    }
</script>
