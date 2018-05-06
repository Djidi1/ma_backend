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
                                <v-select
                                        :items="checklist_groups"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.checklist_id"
                                        :label="$t('checklist')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="requirement_groups"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.requirement_groups_id"
                                        :label="$t('requirement_groups')"
                                        required
                                ></v-select>
                            </v-flex>
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
        <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>
        <v-card fluid fill-height fill-width>
            <v-card-title>
                <v-select
                        :items="checklist_groups"
                        v-model="checklist_select"
                        :label = "$t('checklist')"
                        item-text = "title"
                        item-value = "id"
                ></v-select>
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
                    :items="filteredItems"
                    :search="search"
                    :loading="loading"
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <td class="text-xs-right">{{ props.item.id }}</td>
                    <!--<td>{{ checklist_groups.find(x => x.id === props.item.checklist_id).title }}</td>-->
                    <!--<td>{{ requirement_groups.find(x => x.id === props.item.requirement_groups_id).title }}</td>-->
                    <td>{{ props.item.title }}</td>
                    <td>
                        <v-badge
                                :title="props.item.warning_level < 5 ? 'notice' : (props.item.warning_level > 7 ? 'danger' : 'warning')"
                                :color="props.item.warning_level < 5 ? 'green' : (props.item.warning_level > 7 ? 'red' : 'orange')"
                                :bottom="true" >
                            <span slot="badge">{{ props.item.warning_level }}</span>
                        </v-badge>
                    </td>
                    <td class="justify-center layout px-0">
                        <v-btn icon class="mx-0" @click="editItem(props.item)">
                            <v-icon color="teal">edit</v-icon>
                        </v-btn>
                        <v-btn icon class="mx-0" @click="deleteItem(props.item)">
                            <v-icon color="pink">delete</v-icon>
                        </v-btn>
                    </td>
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
                loading: true,
                search: '',
                headers: [
                    { text: 'id', align: 'right', value: 'id' },
                    // { text: this.$t('checklist'), align: 'left', value: 'checklist' },
                    // { text: this.$t('group'), align: 'left', value: 'group' },
                    { text: this.$t('title'), align: 'left', value: 'name' },
                    { text: this.$t('level'), align: 'left', value: 'warning_level' },
                    { text: this.$t('actions'), align: 'center', sortable: false, value: '' }
                ],
                title: '',
                checklist_select: 0,
                checklist_selected: 0,
                items: [],
                requirement_groups: [],
                checklist_groups: [],
                editedIndex: -1,
                editedItem: {
                    title: ''
                },
                defaultItem: {
                    title: ''
                },
                levels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                valid: false,

            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            },
            filteredItems() {
                return this.items.filter(item => {
                    return item.checklist_id === this.checklist_selected
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
                axios.get('/requirements_all')
                    .then(response => {
                        this.items = response.data.requirements;
                        this.checklist_selected = this.items[0].checklist_id || 0;
                        this.checklist_select = this.checklist_selected;
                        this.requirement_groups = response.data.requirement_groups;
                        this.checklist_groups = response.data.checklist_groups;
                        this.loading = false;
                    });
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
                    axios.put('/requirements_update/' + item.id, item)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], editedItem);
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    axios.post(`/requirements_save`, this.editedItem)
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
            this.getItems();
        }
    }
</script>
