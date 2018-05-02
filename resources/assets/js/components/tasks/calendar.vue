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
                                <v-text-field :label="$t('title')" v-model="editedItem.title" required></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="objects"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.object_id"
                                        :label="$t('object')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="checklists"
                                        item-text = "title"
                                        item-value = "id"
                                        v-model="editedItem.checklist_id"
                                        :label="$t('checklists')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                        :items="users"
                                        item-text = "name"
                                        item-value = "id"
                                        v-model="editedItem.user_id"
                                        :label="$t('users')"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-dialog
                                        ref="picker"
                                        persistent
                                        v-model="picker"
                                        lazy
                                        full-width
                                        width="290px"
                                        :return-value.sync="editedItem.date"
                                >
                                    <v-text-field
                                            slot="activator"
                                            :label="$t('date')"
                                            v-model="editedItem.date"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker
                                            v-model="editedItem.date"
                                            first-day-of-week="1"
                                            scrollable
                                    >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="pink" @click="picker = false">{{$t('cancel')}}</v-btn>
                                        <v-btn flat color="primary" @click="$refs.picker.save(editedItem.date)">OK</v-btn>
                                    </v-date-picker>
                                </v-dialog>
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
        <full-calendar
                :events="fcEvents"
                locale="ru"
                first-day='1'
                @changeMonth="changeMonth"
                @eventClick="eventClick"
                @dayClick="dayClick"
                @moreClick="moreClick"
        ></full-calendar>
    </div>
</template>

<script>
    let demoEvents = [
        {
            title : 'Демонстрация MobileAudit',
            start : '2018-04-14',
            end : '2018-04-14'
        }
    ];
    export default {
        data () {
            return {
                dialog: false,
                picker: false,
                fcEvents : demoEvents,

                editedItem: {
                    title: '',
                    date: ''
                },
                defaultItem: {
                    title: '',
                    date: ''
                },
                checklists: [],
                objects: [],
                users: [],
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            }
        },
        methods: {
            getItems() {
                axios.get('/audit_tasks_all')
                    .then(response => {
                        let audit_tasks = response.data;
                        let i;
                        for(i = 0; i < audit_tasks.length; i++){
                            if (audit_tasks.hasOwnProperty(i)) {
                                Object.defineProperty(audit_tasks[i], 'start', Object.getOwnPropertyDescriptor(audit_tasks[i], 'date'));
                            }
                            let cssClass = 'new-item';
                            // Если все результаты положительные, то аудит успешный
                            if (audit_tasks[i]['audit_result'].length > 0) {
                                cssClass = 'done-item';
                                let k;
                                for(k = 0; k < audit_tasks[i]['audit_result'].length; k++){
                                    // Если есть неудовлетворительный результат, то считаем аудит в работе
                                    if (audit_tasks[i]['audit_result'][k]['result'] === 0) {
                                        cssClass = 'in-work-item';
                                    }
                                }
                            }
                            Object.defineProperty(audit_tasks[i], 'cssClass', {value: cssClass});
                        }
                        this.fcEvents = audit_tasks;
                    });
                axios.get('/audits_all')
                    .then(response => {
                        // this.items = response.data.audits;
                        this.checklists = response.data.checklists;
                        this.objects = response.data.objects;
                        this.users = response.data.users;
                        this.loading = false;
                    });
            },
            close() {
                this.dialog = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.editedIndex = -1;
                }, 300)
            },
            save(event) {
                if (this.editedIndex > -1) {
                    let item_index = this.editedIndex;
                    let editedItem = this.editedItem;
                    let item = this.editedItem;
                    delete item['audit_object'];
                    delete item['audit_result'];
                    delete item['checklist'];
                    delete item['user'];
                    delete item['start'];
                    delete item['end'];
                    delete item['cellIndex'];
                    delete item['isShow'];
                    axios.put('/audits_update/' + item.id, item)
                        .then(response => {
                            if (response.data === 1) {
                                Object.defineProperty(editedItem, 'start', Object.getOwnPropertyDescriptor(editedItem, 'date'));
                                Object.assign(this.fcEvents[item_index], editedItem);
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    let new_item = this.editedItem;
                    new_item.date_add = this.editedItem.date;
                    axios.post(`/audits_save`, new_item)
                        .then(response => {
                            let addedItem = response.data;
                            Object.defineProperty(addedItem, 'start', Object.getOwnPropertyDescriptor(addedItem, 'date'));
                            this.fcEvents.push(addedItem)
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                }
                this.close()
            },
            'changeMonth' (start, end, current) {
            },
            'eventClick' (event) {
                this.editedIndex = this.fcEvents.indexOf(event);
                this.editedItem = Object.assign({}, event);
                this.dialog = true
            },
            'dayClick' (day) {
                this.editedIndex = -1;
                Object.defineProperty(this.editedItem, 'date', {value: moment(day).format('YYYY-MM-DD')});
                this.dialog = true
            },
            'moreClick' (day, events, jsEvent) {
                console.log('moreCLick', day, events, jsEvent)
            }
        },
        mounted () {
            let d = document.getElementsByClassName('prev-month');
            d[0].className += " btn btn--icon";
            d[0].innerHTML = '<div class="btn__content"><i aria-hidden="true" class="icon blue--text material-icons">keyboard_arrow_left</i></div>';
            let n = document.getElementsByClassName('next-month');
            n[0].className += " btn btn--icon";
            n[0].innerHTML = '<div class="btn__content"><i aria-hidden="true" class="icon blue--text material-icons">keyboard_arrow_right</i></div>';

            this.getItems();
        }
    }
</script>

<style lang="scss">
    .full-calendar-body {
        .weeks{
            .week{
                &:nth-last-child(-n+2) {
                    background-color: #FBE9E7;
                }
            }
        }
        .dates {
            .week-row {
                .day-cell {
                    &.today {
                        background-color: #fcf8e3;
                    }
                    &:nth-last-child(-n+2) {
                        background-color: #FBE9E7;
                    }
                }
            }
        }
    }
    .in-work-item {
        background-color: #ffec85 !important;
    }
    .done-item {
        background-color: #bbe3ab !important;
    }
</style>