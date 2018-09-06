<template>
    <div style="width: 100%">
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
                    <td>{{ props.item.title }}</td>
                    <td>{{ typeof props.item.requirement !== 'undefined' ? props.item.requirement.length : '0' }}</td>
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
            <resize-observer @notify="handleResize" />
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
                    { text: this.$t('title'), align: 'left', value: 'name' },
                    { text: this.$t('requirements'), align: 'left', value: 'count' },
                    { text: this.$t('actions'), align: 'center', sortable: false, value: '' }
                ],
                title: '',
                items: [],
                groups: [],
                editedIndex: -1,
                editedItem: {
                    title: '',
                    password: ''
                },
                defaultItem: {
                    title: '',
                    password: ''
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
            }
        },
        methods: {
            handleResize () {
                setTimeout(() => {
                    this.gridOptions.api.sizeColumnsToFit();
                }, 500)
            },
            getItems() {
                axios.get('/requirement_groups_all')
                    .then(response => {
                        this.items = response.data;
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
                        axios.delete('/requirement_groups_delete/' + item.id);
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
                    axios.put('/requirement_groups_update/' + item.id, this.editedItem)
                        .then(response => {
                            if (response.data === 1) {
                                Object.assign(this.items[item_index], item);
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    axios.post(`/requirement_groups_save`, this.editedItem)
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