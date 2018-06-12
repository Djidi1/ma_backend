<template>
    <div style="width: 100%; height: 100%">
        <v-card fluid fill-height fill-width style="height: 100%">
            <v-dialog v-model="dialog" persistent max-width="500px">
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-layout wrap>
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
                                            :label="$t('checklist')"
                                            required
                                    ></v-select>
                                </v-flex>
                                <v-flex xs12>
                                    <v-select
                                            :items="users"
                                            item-text = "name"
                                            item-value = "id"
                                            v-model="editedItem.user_id"
                                            :label="$t('auditor')"
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
                                <v-flex xs12>
                                    <v-text-field :label="$t('comment')" v-model="editedItem.title" required></v-text-field>
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
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
            <calendar-view
				:events="events"
                :show-date="showDate"
				:starting-day-of-week="1"
                eventContentHeight="2.4em"
                @show-date-change="setShowDate"
                class="theme-default wrap-event-title-on-hover"
                
				@click-date="onClickDay"
				@click-event="onClickEvent"
            >
            </calendar-view>
            <v-alert :value="true" outline color="info" icon="info">
                <b class="blue--text"><v-icon>today</v-icon></b> - запланирован (аудит не проводился)<br/>
                <b class="green--text"><v-icon>done_all</v-icon></b> - проведен (успешно пройденный аудит) <br/>
                <b class="orange--text"><v-icon>clear</v-icon></b> - просрочен (выявлены несоответствия требованиям) <br/>
            </v-alert>
        </v-card>
    </div>
</template>

<script>
    import CalendarView from "vue-simple-calendar"
    import CalendarMathMixin from "vue-simple-calendar/dist/calendar-math-mixin.js"
    
	require("vue-simple-calendar/dist/static/css/default.css")

    export default {
        data () {
            return {
                dialog: false,
                picker: false,
                loading: true,
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
                showDate: new Date(),
                events: [],
            }
        },
		components: {
			CalendarView
		},
	    mixins: [CalendarMathMixin],
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            }
        },
        updated() {
            if (this.events.length > 0) {
                let regex = /(<([^>]+)>)/ig;
                let elms = document.getElementsByClassName('cv-event')
                for (let i = 0; i < elms.length; i++) {
                    if (elms.hasOwnProperty(i)) {
                        elms[i].setAttribute("title", elms[i].getAttribute("title").replace(regex, ""));
                    }
                }
            }
        },
        methods: {
            thisMonth(d, h, m) {
                const t = new Date()
                return new Date(t.getFullYear(), t.getMonth(), d, h || 0, m || 0)
            },
            getItems() {
                axios.get('/audit_tasks_all')
                    .then(response => {
                        this.checklists = response.data.checklists;
                        this.objects = response.data.objects;
                        this.users = response.data.users;
                        let audit_tasks = response.data.audits;
                        let i;
                        for(i = 0; i < audit_tasks.length; i++){
                            if (audit_tasks.hasOwnProperty(i)) {
                                Object.defineProperty(audit_tasks[i], 'startDate', Object.getOwnPropertyDescriptor(audit_tasks[i], 'date'));
                            }
                            audit_tasks[i].title = '<span class="cut-text">' +
                                                    audit_tasks[i].audit_object.audit_object_group.title +
                                                   '</span><br/><b>' + audit_tasks[i].audit_object.title + '</b>';
                            let cssClass = 'new-item';
                            // Если все результаты положительные, то аудит успешный
                            if (audit_tasks[i]['audit_result'].length > 0) {
                                cssClass = 'done-item';
                                let k;
                                for(k = 0; k < audit_tasks[i]['audit_result'].length; k++){
                                    // Если есть неудовлетворительный результат, то считаем аудит в работе
                                    if (audit_tasks[i]['audit_result'][k]['result'] !== 1) {
                                        cssClass = 'in-work-item';
                                    }
                                }
                            }
                            Object.defineProperty(audit_tasks[i], 'classes', {value: cssClass});
                        }
                        this.events = audit_tasks;
                        this.loading = false;
                    });
            },
            setShowDate(d) {
				this.showDate = d;
			},
            close() {
                this.dialog = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.editedIndex = -1;
                }, 300)
            },
            save() {
                if (this.editedIndex > -1) {
                    let item_index = this.editedIndex;
                    let editedItem = this.editedItem;
                    let item = this.editedItem;
                    delete item['audit_object'];
                    delete item['audit_result'];
                    delete item['checklist'];
                    delete item['user'];
                    delete item['startDate'];
                    delete item['end'];
                    delete item['cellIndex'];
                    delete item['isShow'];
                    axios.put('/audits_update/' + item.id, item)
                        .then(response => {
                            if (response.data === 1) {
                                Object.defineProperty(editedItem, 'startDate', Object.getOwnPropertyDescriptor(editedItem, 'date'));
                                Object.assign(this.events[item_index], editedItem);
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
                            Object.defineProperty(addedItem, 'startDate', Object.getOwnPropertyDescriptor(addedItem, 'date'));
                            this.events.push(addedItem)
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                }
                this.close()
            },
            onClickDay(day) {
                this.editedIndex = -1;
                Object.defineProperty(this.editedItem, 'date', {value: moment(day).format('YYYY-MM-DD')});
                this.dialog = true
            },
            onClickEvent(event) {
                this.editedIndex = this.events.indexOf(event);
                this.editedItem = Object.assign({}, event.originalEvent);
                this.dialog = true
            },
            chg_btns(class_name, color, icon) {
                let d = document.getElementsByClassName(class_name);
                d[0].className += " btn btn--icon";
                d[0].innerHTML = '<div class="btn__content"><i aria-hidden="true" class="icon '+color+'--text material-icons">'+icon+'</i></div>';
            }
        },
        mounted () {
            this.chg_btns('previousPeriod', 'blue', 'keyboard_arrow_left')
            this.chg_btns('nextPeriod', 'blue', 'keyboard_arrow_right')
            this.chg_btns('currentPeriod', 'green', 'today')
            this.chg_btns('previousYear', 'orange', 'first_page')
            this.chg_btns('nextYear', 'orange', 'last_page')         
            this.getItems();
        }
    }
</script>

<style lang="scss">
    .dow6, .cv-day.dow0, .cv-header-day.dow5 {
        background-color: #FBE9E7 !important;
    }
    .in-work-item {
        background-color: #ffec85 !important;
    }
    .done-item {
        background-color: #bbe3ab !important;
    }
    .cv-event {
        cursor: pointer;
        height: 34px;
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .cut-text { 
        text-overflow: ellipsis;
        overflow: hidden; 
        white-space: nowrap;
    }
    button.previousPeriod, button.nextPeriod, button.currentPeriod, button.nextYear, button.previousYear {
        padding: 0;
        border: none;
    }
</style>