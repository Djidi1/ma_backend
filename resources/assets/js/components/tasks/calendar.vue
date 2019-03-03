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
                                            :items="filteredObjects"
                                            item-text = "title"
                                            item-value = "id"
                                            v-model="editedItem.object_id"
                                            :label="$t('object')"
                                            required
                                            :rules="[rules.required('object', editedItem.object_id)]"
                                    ></v-select>
                                </v-flex>
                                <v-flex xs12>
                                    <v-select
                                            key="s1"
                                            v-if="(editedItem.hasOwnProperty('id'))"
                                            :items="checklists"
                                            item-text = "title"
                                            item-value = "id"
                                            v-model="editedItem.checklist_id"
                                            :label="$t('checklist')"
                                            :rules="[rules.required('checklist', editedItem.checklist_id)]"
                                            required
                                    ></v-select>
                                    <v-select
                                            key="s2"
                                            v-if="(!editedItem.hasOwnProperty('id'))"
                                            :items="checklists"
                                            item-text = "title"
                                            item-value = "id"
                                            v-model="newItemChecklists"
                                            :label="$t('checklist')"
                                            required
                                            :rules="[rules.required('checklist', newItemChecklists)]"
                                            multiple
                                    ></v-select>
                                </v-flex>
                                <v-flex xs12>
                                    <v-select
                                            :items="users"
                                            item-text = "name"
                                            item-value = "id"
                                            v-model="editedItem.user_id"
                                            :label="$t('auditor')"
                                            :rules="[rules.required('auditor', editedItem.user_id)]"
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
                                                :min="today()"
                                                scrollable
                                        >
                                            <v-spacer></v-spacer>
                                            <v-btn flat color="pink" @click="picker = false">{{$t('cancel')}}</v-btn>
                                            <v-btn flat color="primary" @click="$refs.picker.save(editedItem.date)">OK</v-btn>
                                        </v-date-picker>
                                    </v-dialog>
                                </v-flex>
                                <v-flex xs12>
                                    <v-text-field :label="$t('comment')" v-model="editedItem.orig_title"></v-text-field>
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
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>

                <v-select
                        id="selectPeriodUom"
                        v-model="displayPeriodUom"
                        :items="periods"
                        item-text = "title"
                        item-value = "id"
                        :label="$t('calendar_period')"
                >
                    <option>month</option>
                    <option>week</option>
                    <option>year</option>
                </v-select>
            <calendar-view v-if="$auth.user().role_id === 1"
				:events="events"
                :show-date="showDate"
				:starting-day-of-week="1"
				:display-period-uom="displayPeriodUom"
                :locale="calendar_locale"
                eventContentHeight="2.4em"
                @show-date-change="setShowDate"
                class="theme-default wrap-event-title-on-hover"
                
				@click-date="onClickDay"
				@click-event="onClickEvent"
            >
            </calendar-view>
            <calendar-view v-if="$auth.user().role_id === 2"
				:events="events"
                :show-date="showDate"
                :locale="calendar_locale"
				:starting-day-of-week="1"
				:display-period-uom="displayPeriodUom"
                eventContentHeight="2.4em"
                @show-date-change="setShowDate"
                class="theme-default wrap-event-title-on-hover"
            >
            </calendar-view>
            <v-alert :value="true" outline color="info" icon="info">
                <b class="blue--text"><v-icon>today</v-icon></b> - {{this.$t('legend_planned')}} <br/>
                <b class="green--text"><v-icon>done_all</v-icon></b> - {{this.$t('legend_done')}}  <br/>
                <b class="orange--text"><v-icon>clear</v-icon></b> - {{this.$t('legend_overdue')}}  <br/>
            </v-alert>
            <v-snackbar
                v-model = "snackbar"
                color = "info"
                :timeout = "3000"
                >
                {{ snackbar_text }}
                <v-btn
                    dark
                    flat
                    @click="snackbar = false"
                >
                    Close
                </v-btn>
            </v-snackbar>
        </v-card>
    </div>
</template>

<script>
    import CalendarView from "vue-simple-calendar"
    import CalendarMathMixin from "vue-simple-calendar/dist/calendar-math-mixin.js"
    import formValidationMixin from "../../mixins/formValidation"
    
	require("vue-simple-calendar/dist/static/css/default.css");

    export default {
        data () {
            return {
                dialog: false,
                picker: false,
                snackbar: false,
                loading: true,
                editedIndex: -1,
                editedItem: {
                    title: '',
                    date: '',
                    orig_title: ''
                },
                defaultItem: {
                    title: '',
                    date: '',
                    orig_title: ''
                },
                newItemChecklists: [],
                checklists: [],
                objects: [],
                users: [],
			    displayPeriodUom: "month",               
                showDate: new Date(),
                events: [],
                errors: [],
                snackbar_text: "Нельзя создавать аудиты в прошлом.",
                calendar_locale: "ru",
                langListener: ''
            }
        },
		components: {
			CalendarView
		},
	    mixins: [CalendarMathMixin, formValidationMixin],
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('new_item') : this.$t('edit_item')
            },
            filteredObjects() {
                return this.objects.filter(object => {
                    return moment(object.archive, 'YYYY-MM-DD').isAfter(moment(), 'day') || object.archive === null || object.id === this.editedItem.object_id
                })
            },
            periods(){
                return [{id: "month", title: this.$t('month')},{id: "week", title: this.$t('week')},{id: "year", title: this.$t('year')}]
            }
        },
        updated() {
            if (this.events.length > 0) {
                let regex = /(<([^>]+)>)/ig;
                let elms = document.getElementsByClassName('cv-event');
                for (let i = 0; i < elms.length; i++) {
                    if (elms.hasOwnProperty(i)) {
                        elms[i].setAttribute("title", elms[i].getAttribute("title").replace(regex, " "));
                    }
                }
            }
        },
        methods: {
            /*thisMonth(d, h, m) {
                const t = new Date();
                return new Date(t.getFullYear(), t.getMonth(), d, h || 0, m || 0)
            },*/

            today() {
                return moment().format('YYYY-MM-DD');
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
                            audit_tasks[i].orig_title = audit_tasks[i].title;
                            let audit_object = (audit_tasks[i].audit_object !== null) ? audit_tasks[i].audit_object : [];
                            let object_group = (audit_object.audit_object_group !== null) ? audit_object.audit_object_group : [];

                            // console.log(object_group);

                             audit_tasks[i].title = '<span class="cut-text">' + object_group.title +
                                                    '</span><br/><b>' + audit_object.title + '</b>';
                            // Запланированные в синий
                            let cssClass = 'new-item';
                            if (audit_tasks[i]['audit_result'].length > 0) {
                                // Если пройден во время, то красим в зеленый, иначе в желтый
                                cssClass = (moment(audit_tasks[i]['date_add']).add(1, 'days') > moment(audit_tasks[i]['audit_result'][0]['created_at']))
                                    ? 'done-item'
                                    : 'in-work-item';
                            } else if (moment(audit_tasks[i]['date_add']).diff(moment(),'days') < 0) {
                                // Если просрочен, то красим в желтый
                                cssClass = 'in-work-item';
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
                    this.newItemChecklists = [];
                }, 300)
            },
            save() {
                if (this.editedIndex > -1) {
                    let item_index = this.editedIndex;
                    let editedItem = this.editedItem;
                    let item = Object.assign({}, this.editedItem);
                    item['title'] = item['orig_title'];
                    item['comment'] = item['orig_title'];
                    delete item['audit_object'];
                    delete item['audit_result'];
                    delete item['checklist'];
                    delete item['user'];
                    delete item['startDate'];
                    delete item['end'];
                    delete item['cellIndex'];
                    delete item['isShow'];
                    delete item['orig_title'];
                    axios.put('/audits_update/' + item.id, item)
                        .then(response => {
                            if (response.data.id > -1) {
                                Object.defineProperty(editedItem, 'startDate', Object.getOwnPropertyDescriptor(editedItem, 'date'));
                                Object.assign(this.events[item_index], editedItem);
                                Object.assign(this.events[item_index], {title: '<span class="cut-text">' + response.data.audit_object.audit_object_group.title + '</span><br/><b>' + response.data.audit_object.title + '</b>'});
                            }
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                } else {
                    let new_item = this.editedItem;
                    new_item.checklist_id = this.newItemChecklists;
                    new_item.date_add = this.editedItem.date;
                    new_item['title'] = this.editedItem.orig_title;
                    new_item['comment'] = this.editedItem.orig_title;
                    axios.post(`/audits_add`, new_item)
                        .then(response => {
                            response.data.forEach(element => {
                                let addedItem = element;
                                addedItem.title = '<span class="cut-text">' + addedItem.audit_object.audit_object_group.title +
                                    '</span><br/><b>' + addedItem.audit_object.title + '</b>';
                                addedItem.orig_title = addedItem.comment;
                                Object.defineProperty(addedItem, 'startDate', Object.getOwnPropertyDescriptor(addedItem, 'date'));
                                this.events.push(addedItem);
                            });      
                        })
                        .catch(e => {
                            this.errors.push(e)
                        });
                }
                this.close()
            },
            onClickDay(day) {
                // Можно добавлять начиная с текущего дня
                if (moment().diff(day, 'days') <= 0) {
                    this.editedIndex = -1;
                    this.editedItem.date = moment(day).format('YYYY-MM-DD');
                    this.dialog = true;
                } else {
                    this.snackbar = true
                }
            },
            onClickEvent(event) {
                this.editedIndex = this.events.indexOf(event.originalEvent);
                this.editedItem = Object.assign({}, event.originalEvent);
                // Для проведенных открываем результаты
                if (this.editedItem.audit_result.length > 0) {
                    this.$router.push('/audit_results/' + this.editedItem.id);                    
                } else {
                    this.dialog = true;
                }
                
            },
            chg_btns(class_name, color, icon) {
                let d = document.getElementsByClassName(class_name);
                d[0].className += " btn btn--icon";
                d[0].innerHTML = '<div class="btn__content"><i aria-hidden="true" class="icon '+color+'--text material-icons">'+icon+'</i></div>';
            }
        },
        mounted () {
            this.chg_btns('previousPeriod', 'blue', 'keyboard_arrow_left');
            this.chg_btns('nextPeriod', 'blue', 'keyboard_arrow_right');
            this.chg_btns('currentPeriod', 'green', 'today');
            this.chg_btns('previousYear', 'orange', 'first_page');
            this.chg_btns('nextYear', 'orange', 'last_page') ;
            let d = document.getElementsByClassName('cv-header');
            let s = document.getElementById('selectPeriodUom');
            d[0].appendChild(s);
            this.getItems();
            this.calendar_locale = this.$i18n.locale();
            this.langListener = (e) => {
                this.calendar_locale = e.detail.lang
            }
            document.addEventListener('langChanged', this.langListener);
        },
        beforeDestroy() {
            document.removeEventListener('langChanged', this.langListener);
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
    .new-item {
        background-color: #90CAF9 !important;
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