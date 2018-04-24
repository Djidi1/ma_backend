<template>
    <div style="width:100%">
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
                    <v-btn color="blue darken-1" flat @click.native="close">{{ $t('cancel') }}</v-btn>
                    <v-btn color="blue darken-1" flat @click.native="save">{{ $t('save') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-card fluid fill-height fill-width>
            <v-card-title>
                <v-btn color="primary" dark slot="activator" @click="dialog = true" class="mb-2">{{$t('new_item')}}</v-btn>
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
                    <td>{{ users_items.find(x => x.id === props.item.user_id).name }}</td>
                    <td>
                        <template v-for="(item, index) in props.item.object_id">
                           <v-chip v-html="objects_items.find(x => x.id === item).title"></v-chip>
                        </template>
                    </td>
                    <td>
                        <template v-for="(item, index) in props.item.requirement_id">
                            <v-chip v-html="requirements_items.find(x => x.id === item).title"></v-chip>
                        </template>
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
                    { text: 'User', align: 'left', value: 'user' },
                    { text: 'Objects', align: 'left', value: 'objects' },
                    { text: 'Requirements', align: 'left', value: 'requirements' },
                    { text: 'Actions', align: 'center', sortable: false, value: '' }
                ],
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
                }
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
                        axios.delete('/responsible_delete/' + item.id);
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
                    let item = this.editedItem;
                    // Object.assign(this.items[this.editedIndex], this.editedItem)
                    axios.put('/responsible_update/' + item.id, this.editedItem)
                        .then(response => {
                            Object.assign(this.items[item_index], item);
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    axios.post(`/responsible_save`, this.editedItem)
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
