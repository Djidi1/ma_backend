<template>
    <v-flex style="width: 100%; height: 100%">
        <v-container fill-height v-if="fullscreen_loader">
            <v-layout row wrap align-center>
                <v-flex class="text-xs-center">
                    <v-progress-circular indeterminate :size="100" color="primary"></v-progress-circular>
                </v-flex>
            </v-layout>
        </v-container>
        <div style="width: 100%; height: 100%" v-else>
            <v-dialog
                    v-model="dialog"
                    fullscreen
                    hide-overlay
                    transition="dialog-bottom-transition"
                    scrollable
                    persistent>
                <v-card tile>
                    <v-toolbar card dark color="primary">
                        <v-btn icon dark @click.native="dialog = false">
                            <v-icon>arrow_back</v-icon>
                        </v-btn>
                        <v-toolbar-title style="line-height: 1;">
                            {{ editedItem.result.audit.audit_object.title }}
                            <!--<br />-->
                            <!--<span class="caption">{{ editedItem.result.requirement.title }}</span>-->
                        </v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-toolbar-items>
                            <v-btn dark flat @click.native="save">{{ $t('save') }}</v-btn>
                        </v-toolbar-items>
                    </v-toolbar>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-layout wrap>
                                <v-flex xs6>
                                    <v-list two-line>
                                        <v-list-tile avatar>
                                            <v-list-tile-avatar>
                                                <v-icon color="info">contact_mail</v-icon>
                                            </v-list-tile-avatar>
                                            <v-list-tile-content>
                                                <v-list-tile-title>{{ editedItem.responsible_user.name }}</v-list-tile-title>
                                                <v-list-tile-sub-title>{{ editedItem.responsible_user.email }}</v-list-tile-sub-title>
                                            </v-list-tile-content>
                                        </v-list-tile>
                                    </v-list>
                                    <v-alert :value="true" outline color="info" icon="info">{{ editedItem.result.requirement.title }}</v-alert>
                                </v-flex>
                                <v-flex xs6>
                                    <v-dialog
                                            ref="picker_start"
                                            persistent
                                            v-model="picker_start"
                                            lazy
                                            full-width
                                            width="290px"
                                            :return-value.sync="editedItem.start"
                                    >
                                        <v-text-field
                                                slot="activator"
                                                :label="$t('date_start')"
                                                v-model="editedItem.start"
                                                prepend-icon="event"
                                                readonly
                                        ></v-text-field>
                                        <v-date-picker
                                                v-model="editedItem.start"
                                                first-day-of-week="1"
                                                scrollable
                                        >
                                            <v-spacer></v-spacer>
                                            <v-btn flat color="primary" @click="picker_start = false">{{$t('cancel')}}</v-btn>
                                            <v-btn flat color="primary" @click="$refs.picker_start.save(editedItem.start)">OK</v-btn>
                                        </v-date-picker>
                                    </v-dialog>
                                    <v-dialog
                                            ref="picker_end"
                                            persistent
                                            v-model="picker_end"
                                            lazy
                                            full-width
                                            width="290px"
                                            :return-value.sync="editedItem.end"
                                    >
                                        <v-text-field
                                                slot="activator"
                                                :label="$t('date_end')"
                                                v-model="editedItem.end"
                                                prepend-icon="event"
                                                readonly
                                        ></v-text-field>
                                        <v-date-picker
                                                v-model="editedItem.end"
                                                first-day-of-week="1"
                                                scrollable
                                        >
                                            <v-spacer></v-spacer>
                                            <v-btn flat color="primary" @click="picker_end = false">{{$t('cancel')}}</v-btn>
                                            <v-btn flat color="primary" @click="$refs.picker_end.save(editedItem.end)">OK</v-btn>
                                        </v-date-picker>
                                    </v-dialog>
                                    <v-select
                                            prepend-icon="restore"
                                            :items="statuses"
                                            item-text="title"
                                            item-value="id"
                                            v-model="editedItem.task_status_id"
                                            :label="$t('status')"
                                            required
                                    ></v-select>
                                </v-flex>
                                <v-alert
                                        v-if="editedItem.comment !== '' && editedItem.comment !== '-'"
                                        :value="true"
                                        outline
                                        color="warning"
                                        icon="priority_high"
                                        style="width: 100%"
                                >{{ editedItem.comment }}</v-alert>
                                <v-card width="100%" tile v-if="editedItem.id > 0">
                                    <v-card-title primary-title>
                                        <h3>{{ $t('comments') }}</h3>
                                    </v-card-title>
                                    <v-divider></v-divider>
                                    <v-progress-linear class="ma-0" v-if="comments_loading" :indeterminate="true"></v-progress-linear>
                                    <v-card class="elevation-0" v-for="(item, index) in commentsItem" :key="'comment_'+index">
                                        <v-card-title>
                                            <div class="grey--text">{{ item.user.name }}</div>
                                            <v-spacer></v-spacer>
                                            <div class="grey--text">{{ item.created_at }}</div>
                                        </v-card-title>
                                        <v-card-text class="py-0">
                                            {{ item.message }}
                                        </v-card-text>
                                        <v-card-actions>
                                            <v-spacer></v-spacer>
                                            <v-btn flat icon
                                                color="orange"
                                                @click="result_attaches(item.task_comment_attache)"
                                                v-if="item.task_comment_attache.length > 0"
                                                class="pointer"
                                            > 
                                                <v-icon>photo</v-icon>                                               
                                            </v-btn>                                            
                                        </v-card-actions>
                                        <v-divider v-if="index + 1 < commentsItem.length" :key="'div_'+index"></v-divider>
                                    </v-card>

                                    <v-divider></v-divider>
                                    <v-flex xs12>
                                        <v-text-field
                                                v-model="comment_message"
                                                :label="$t('comment_text')"
                                                multi-line
                                                :rules="[(v) => v.length <= 500 || 'Max 500 characters']"
                                                :counter="500"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-divider></v-divider>
                                    <v-card-actions>
                                        <ul>
                                            <li v-for="(file, index) in files" :key="index" class="tn-images">
                                                <img :src="file.blob" style="height:50px" :title="file.name + ' (' + file.size + ')'">
                                            </li>
                                        </ul>
                                        <file-upload v-if="comment_message !== ''"
                                                     ref="upload"
                                                     v-model="files"
                                                     post-action="/api/send_task_comment_attache"
                                                     :headers="{'X-Requested-With': 'XMLHttpRequest',
                                                        'Authorization': authToken,
                                                        'X-CSRF-TOKEN': csrfToken,
                                                        'X-XSRF-TOKEN': xsrfToken}"
                                                     :multiple="true"
                                                     @input-filter="inputFilter"
                                                     class="btn btn--icon pointer"
                                        >
                                            <div class="btn__content">
                                                <v-icon>attach_file</v-icon>
                                            </div>
                                        </file-upload>
                                        <i v-else class="grey--text">
                                            Вложения доступны после ввода текста комментария
                                        </i>
                                        <v-spacer></v-spacer>
                                        <v-btn
                                                color="info"
                                                @click="send_comment()"
                                                :disabled="comment_message === ''"
                                        >{{$t('send')}}
                                            <v-icon right dark>send</v-icon>
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                                <v-alert v-else :value="true" outline color="info" icon="info">
                                    Укажите дату начала, для возможности оставлять комментарии.
                                </v-alert>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                </v-card>
            </v-dialog>
            <v-dialog v-model="dialog_photo" max-width="800px">
                <v-card>
                    <v-carousel :cycle="false" :lazy="true" v-if="carousel">
                        <v-carousel-item v-for="(item,i) in attaches_array" :src="item.file_path" :key="i"></v-carousel-item>
                    </v-carousel>
                </v-card>
            </v-dialog>
            <v-card fluid fill-height fill-width style="height: 100%">
            <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
            <v-card-actions>
                <v-select
                        :items="object_groups"
                        v-model="object_select"
                        :label="$t('object_groups')"
                        item-text="title"
                        item-value="id"
                        autocomplete
                ></v-select>
                <v-spacer></v-spacer>
                <v-btn-toggle v-model="toggle_multiple" multiple>
                    <v-btn flat class="mx-0" :value="3">
                        <v-icon color="teal">done</v-icon>
                    </v-btn>
                    <v-btn flat class="mx-0" :value="2">
                        <v-icon color="blue">schedule</v-icon>
                    </v-btn>
                    <v-btn flat class="mx-0" :value="1">
                        <v-icon color="red">clear</v-icon>
                    </v-btn>
                    <v-btn flat class="mx-0" :value="0">{{$t('all')}}</v-btn>
                </v-btn-toggle>
            </v-card-actions>
            <ag-grid-vue style="width: 100%;"
                         class="ag-theme-balham"
                         :gridOptions="gridOptions"
                         :columnDefs="columnDefs"
                         :rowData="filteredItems"
                         :enableColResize="true"
            >
            </ag-grid-vue>
            <v-alert :value="true" color="info" icon="info">
                <v-icon color="teal">done</v-icon>
                - {{$t('legend_done')}}<br/>
                <v-icon color="blue">schedule</v-icon>
                - {{$t('legend_inprogress')}}<br/>
                <v-icon color="red">clear</v-icon>
                - {{$t('legend_overdue')}}<br/>
            </v-alert>
            <resize-observer @notify="handleResize" />
        </v-card>
        </div>
    </v-flex>
</template>

<script>
    import Vue from "vue";
    import {AgGridVue} from "ag-grid-vue";
    import FileUpload from 'vue-upload-component';

    const ActionButtons = Vue.extend({
        template: `<span><v-btn small icon class="mx-0 my-0" @click="editItem"><v-icon color="blue">work</v-icon></v-btn></span>`,
        methods: {
            editItem() {
                this.params.context.componentParent.editItem(this.params.data);
            }
        }
    });

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


    /*

     */
    export default {
        data() {
            return {
                dialog: false,
                dialog_photo: false,
                carousel: false,
                picker_start: false,
                picker_end: false,
                date: '',
                loading: true,
                fullscreen_loader: false,
                comments_loading: true,
                search: '',
                title: '',
                comment_message: '',
                items: [],
                requirements: [],
                object_groups: [],
                responsible: [],
                object_select: 0,
                object_selected: 0,
                users: [],
                statuses: [],
                editedIndex: -1,
                commentsItem: [],
                attaches_array: [],
                defaultCommentsItem: [],
                editedItem: {task_status_id: 1, id: 0, responsible_user: {}, result: {audit: {audit_object: ''}, requirement: ''}},
                defaultItem: {task_status_id: 1, id: 0, responsible_user: {}, result: {audit: {audit_object: ''}, requirement: ''}},
                valid: false,
                errors: [],
                gridOptions: {},
                columnDefs: null,
                rowData: null,
                params: null,
                toggle_multiple: [0],
                files: [],
                langListener: ''
            }
        },
        components: {
            'ag-grid-vue': AgGridVue,
            FileUpload
        },
        computed: {
            csrfToken() {
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            },
            xsrfToken() {
                let value = "; " + document.cookie;
                let parts = value.split("; XSRF-TOKEN=");
                let xsrf = (parts.length === 2) ? decodeURI(parts.pop().split(";").shift()) : '';
                return decodeURIComponent(xsrf);
            },
            authToken() {
                return 'Bearer ' + localStorage.getItem('default_auth_token');
            },
            filteredItems() {
                let self = this;
                return this.items.filter(item => {
                    // фильтр по статусу
                    let filter_status = true;
                    if (this.toggle_multiple.length === 1 && this.toggle_multiple.indexOf(0) === 0) {
                        filter_status = true;
                    } else {
                        filter_status = this.toggle_multiple.indexOf(item.task_status_id) > -1;
                    }
                    // фильтр по выбранной группе
                    let filter_group;
                    // Проверка, что аудит существует
                    if (item.result.audit !== null) {
                        filter_group = (item.result.audit.audit_object.audit_object_group_id === this.object_selected || this.object_selected === 0);
                    }
                    // фильтр по ответственному
                    let filter_responsible = false;
                    // Ограничение списка для ответственных
                    if (self.$store.state.user.role_id === 1 || item.responsible_id === self.$auth.user().id) {
                        filter_responsible = true;
                    }else{
                        // Ищем ответственных за требование
                        for (let index in self.responsible) {
                            if (self.responsible.hasOwnProperty(index)) {
                                let attr = self.responsible[index];
                                // Проверка на наличие парметра
                                let object_group = (attr.user.object_group_id !== null) ? attr.user.object_group_id : [];
                                // Проверка на наличие аудита
                                let audit_object = (item.result.audit !== null) ? item.result.audit.audit_object : [];
                                if (attr.requirement_id.indexOf(item.result.requirement_id) > -1
                                    && object_group.indexOf(audit_object.audit_object_group_id) > -1) {
                                    if (self.responsible[index].user.id === self.$auth.user().id) {
                                        filter_responsible = true;
                                    }
                                }
                            }
                        }
                        // Если нет за требование, то ищем за объект
                        if (!filter_responsible) {
                            for (let index in self.responsible) {
                                if (self.responsible.hasOwnProperty(index)) {
                                    let attr = self.responsible[index];
                                    // Проверка на наличие аудита
                                    let audit = (item.result.audit !== null) ? item.result.audit : [];
                                    if (attr.object_id.indexOf(audit.object_id) > -1) {
                                        if (self.responsible[index].user.id === self.$auth.user().id) {
                                            filter_responsible = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    return filter_status && filter_group && filter_responsible;
                })
            },
        },
        watch: {
            dialog(val) {
                val || this.close()
            },
            object_select: function (newVal) {
                this.object_selected = newVal;
            },
            toggle_multiple: function (new_value, old_value) {
                // Если новое значение не равно старому и не установлено одно значение
                if (new_value !== old_value && this.toggle_multiple.length !== 1) {
                    if (new_value.length === 0) {
                        this.toggle_multiple = [0]
                    } else {
                        if (old_value.length === 1 && old_value.indexOf(0) === 0 && new_value.length > 1) {
                            let index = this.toggle_multiple.indexOf(0);
                            if (index !== -1) this.toggle_multiple.splice(index, 1);
                        } else if (new_value.length > 1 && new_value.indexOf(0) > -1) {
                            this.toggle_multiple = [0]
                        }
                    }
                }
            }
        },
        methods: {
            handleResize () {
                setTimeout(() => {
                    this.gridOptions.api.sizeColumnsToFit();
                }, 500)
            },
            inputFilter: function (newFile, oldFile, prevent) {
                if (newFile && !oldFile) {
                    // Filter non-image file
                    if (!/\.(jpeg|jpe|jpg|gif|png|webp)$/i.test(newFile.name)) {
                        return prevent()
                    }
                }

                // Create a blob field
                newFile.blob = '';
                let URL = window.URL || window.webkitURL;
                if (URL && URL.createObjectURL) {
                    newFile.blob = URL.createObjectURL(newFile.file)
                }
                let reader = new FileReader();
                reader.readAsDataURL(newFile.file);
                reader.onloadend = function () {
                    newFile.base64data = reader.result;
                }
            },
            openResult(id) {
                this.$router.push({path: '/audit_results/' + id});
            },
            frontEndDateFormat: function (date) {
                return moment(date, 'YYYY-MM-DD').format('DD.MM.YYYY');
            },
            backEndDateFormat: function (date) {
                return moment(date, 'DD.MM.YYYY').format('YYYY-MM-DD');
            },
            getComments(id) {
                this.comments_loading = true;
                axios.get('/responsible_tasks_comments/' + id)
                    .then(response => {
                        this.commentsItem = response.data;
                        this.comments_loading = false;
                    });
            },
            getItems(task_id) {
                let self = this;
                this.gridOptions.api.showLoadingOverlay();
                axios.get('/tasks')
                    .then(response => {
                        self.items = response.data.tasks;
                        self.object_selected = 0;
                        self.object_select = this.object_selected;
                        self.requirements = response.data.requirements;
                        self.responsible = response.data.responsible;
                        self.object_groups = [{id: 0, title: this.$t('all')}].concat(response.data.object_groups);
                        self.users = response.data.users;
                        self.statuses = response.data.statuses;
                        // this.gridOptions.api.sizeColumnsToFit();
                        if (self.gridOptions.api !== null) {
                            self.gridOptions.api.hideOverlay();
                        }

                        // Если указан номер Задания, то сразу открываем его
                        if (task_id !== ':id' && task_id > 0) {
                            let item = self.items.find(itm => { return itm.id === parseInt(task_id)});
                            if (typeof item !== 'undefined') {
                                self.editItem(item);
                            }
                            self.fullscreen_loader = false;
                        }

                        self.loading = false;
                    });
                this.columnDefs = [
                    {
                        headerName: 'ID', align: 'left', minWidth: 30, field: 'result.audit.audit_object.id',
                        cellStyle: {'white-space': 'normal','line-height': 'normal'},
                    },
                    {
                        headerName: this.$t('object'), minWidth: 120, align: 'left', field: 'result.audit.audit_object.title',
                        cellStyle: {'white-space': 'normal','line-height': 'normal'},
                        cellRenderer: function (params) {
                            return '<span title="' + params.value + '">' + params.value + '</span>';
                        }
                    },
                    {
                        headerName: this.$t('requirement'), minWidth: 150, align: 'left', field: 'result.requirement.title',
                        cellStyle: {'white-space': 'normal','line-height': 'normal'},
                        cellRenderer: function (params) {
                            return '<span title="' + params.value + '">' + params.value + '</span>';
                        }
                    },
                    {
                        headerName: this.$t('date'), minWidth: 90, align: 'left', field: 'result.created_at',
                        comparator: (d1, d2) => moment(d1, 'DD.MM.YYYY').isAfter(moment(d2, 'DD.MM.YYYY'), 'day') ? -1 : 1,
                        valueGetter: function (params) {
                            return moment(params.data.result.created_at, 'YYYY-MM-DD').format('DD.MM.YYYY');
                        }
                    },
                    {
                        headerName: this.$t('responsible'), minWidth: 100, field: 'result.requirement_id',
                        cellStyle: {'white-space': 'normal','line-height': 'normal'},
                        valueGetter: function (params) {
                            if (params.data.responsible_id > 0) {
                                return self.users.find(x => x.id === params.data.responsible_id).name;
                            } else {
                                let responsible_names = [];
                                // Ищем ответственных за требование
                                for (let index in self.responsible) {
                                    if (self.responsible.hasOwnProperty(index)) {
                                        let attr = self.responsible[index];
                                        let object_group = (attr.user.object_group_id !== null) ? attr.user.object_group_id : [];
                                        if (attr.requirement_id.indexOf(params.data.result.requirement_id) > -1
                                            && object_group.indexOf(params.data.result.audit.audit_object.audit_object_group_id) > -1) {
                                            responsible_names.push(self.responsible[index].user.name);
                                        }
                                    }
                                }
                                // Если нет за требование, то ищем за объект
                                if (responsible_names.length === 0) {
                                    for (let index in self.responsible) {
                                        if (self.responsible.hasOwnProperty(index)) {
                                            let attr = self.responsible[index];
                                            if (attr.object_id.indexOf(params.data.result.audit.object_id) > -1) {
                                                responsible_names.push(self.responsible[index].user.name);
                                            }
                                        }
                                    }
                                }
                                return [...new Set(responsible_names)].join(', ') || '-';
                            }
                        },
                        cellRenderer: function (params) {
                            let tag_type = 'b';
                            // Ищем ответственных за требование
                            for (let index in self.responsible) {
                                if (self.responsible.hasOwnProperty(index)) {
                                    let attr = self.responsible[index];
                                    if (attr.requirement_id.indexOf(params.data.result.requirement_id) > -1) {
                                        tag_type = 'span';
                                    }
                                }
                            }
                            return '<' + tag_type + ' title="' + params.value + '">' + params.value + '</' + tag_type + '>';
                        }
                    },
                    {
                        headerName: this.$t('date_start'), minWidth: 90, align: 'left', field: 'start',
                        comparator: (d1, d2) => moment(d1, 'DD.MM.YYYY').isAfter(moment(d2, 'DD.MM.YYYY'), 'day') ? -1 : 1,
                        valueGetter: function (params) {
                            return moment(params.data.start).format('DD.MM.YYYY');
                        }
                    },
                    {
                        headerName: this.$t('date_end'), minWidth: 90, align: 'left', field: 'end',
                        comparator: (d1, d2) => moment(d1, 'DD.MM.YYYY').isAfter(moment(d2, 'DD.MM.YYYY'), 'day') ? -1 : 1,
                        valueGetter: function (params) {
                            return moment(params.data.end).format('DD.MM.YYYY');
                        }
                    },
                    {
                        headerName: this.$t('comment'), minWidth: 120, align: 'left', field: 'comment',
                        cellStyle: {'white-space': 'normal','line-height': 'normal'},
                        cellRenderer: function (params) {
                            return '<span title="' + params.value + '">' + params.value + '</span>';
                        }
                    },
                    {
                        headerName: this.$t('photo'), minWidth: 40, field: 'id',
                        cellStyle: {textAlign: "center"},
                        suppressFilter: true,
                        suppressSorting: true,
                        cellRendererFramework: Attaches,
                        colId: "params",
                        suppressCellSelection: false
                    },
                    {
                        headerName: this.$t('result'), minWidth: 40, cellStyle: {textAlign: "center"}, field: 'result',
                        suppressFilter: true,
                        suppressSorting: true,
                        cellRenderer: function (params) {
                            return self.result_icon(params.value, params.data.task_status_id);
                        }
                    },
                    {
                        headerName: this.$t('actions'), minWidth: 40, field: 'id',
                        suppressFilter: true,
                        suppressSorting: true,
                        cellStyle: {textAlign: "center"},
                        cellRendererFramework: ActionButtons,
                        colId: "params",
                        suppressCellSelection: true
                    }
                ];
                this.columnDefs.forEach(element => {
                    element.filterParams = {newRowsAction: 'keep'};
                });
            },
            result_icon(result, task_status_id) {
                let icon;
                // Если работы по устранению замечаний завершены
                if (task_status_id === 3) {
                    icon = '<i class="icon teal--text material-icons">done</i>';
                } else if (task_status_id === 2) {
                    icon = '<i class="icon blue--text material-icons">schedule</i>';
                } else {
                    icon = '<i class="icon grey--text material-icons">remove</i>';
                    if (result === -1) {
                        icon = '<i class="icon pink--text material-icons">clear</i>';
                    } else if (result === 1) {
                        icon = '<i class="icon teal--text material-icons">done</i>';
                    }
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
                let self = this;
                this.editedIndex = this.items.indexOf(item);
                window.location.href = '/#/tasks_list/' + item.id;
                if (item.responsible_id > 0) {
                    item['responsible_user'] =  self.users.find(x => x.id === item.responsible_id);
                } else {
                    item['responsible_user'] = this.responsible_user(item.result.audit.object_id, item.result.requirement_id);
                }
                this.editedItem = Object.assign({}, item);
                this.getComments(item.id);
                this.comment_message = '';
                this.dialog = true
            },

            deleteItem(item) {
                let self = this;
                const index = this.items.indexOf(item);
                this.$confirm(this.$t('sure_delete_item')).then(res => {
                    if (res) {
                        axios.delete('/audits_delete/' + item.id);
                        this.items.splice(index, 1);
                        self.gridOptions.api.refreshCells({force: true});
                    }
                });
            },

            close() {
                this.dialog = false;
                setTimeout(() => {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.commentsItem = this.defaultCommentsItem;
                    this.editedIndex = -1;
                    window.location.href = '/#/tasks_list/:id';
                }, 300)
            },
            send_comment() {
                let self = this;
                axios.post(`/send_task_comment`, {task_id: this.editedItem.id, comment_message: self.comment_message, files: self.files})
                    .then(response => {
                        if (response.data.hasOwnProperty(0)) {
                            self.commentsItem.push(response.data[0]);
                            self.comment_message = '';
                            self.files = [];
                        }
                    })
                    .catch(e => {
                        this.errors.push(e)
                    });
            },
            save() {
                let self = this;
                let item_index = this.editedIndex;
                let editedItem = this.editedItem;
                let item = this.editedItem;
                item.done_percent = 0;
                item.comment = this.editedItem.comment || '-';
                axios.put('/task_update/' + item.id, item)
                    .then(response => {
                        if (response.data === 1) {
                            Object.assign(self.items[item_index], editedItem);
                            self.gridOptions.api.refreshCells({force: true});
                        }
                    })
                    .catch(e => {
                        this.errors.push(e)
                    });
                this.close()
            },
            responsible_user(object_id, requirement_id) {
                let self = this;
                let responsible = {name: null};
                // Ищем ответственных за требование
                for (let index in self.responsible) {
                    if (self.responsible.hasOwnProperty(index)) {
                        let attr = self.responsible[index];
                        if (attr.requirement_id.indexOf(requirement_id) > -1) {
                            responsible = self.responsible[index].user;
                        }
                    }
                }
                // Если нет за требование, то ищем за объект
                if (responsible.name === null) {
                    for (let index in self.responsible) {
                        if (self.responsible.hasOwnProperty(index)) {
                            let attr = self.responsible[index];
                            if (attr.object_id.indexOf(object_id) > -1) {
                                responsible = self.responsible[index].user;
                            }
                        }
                    }
                }
                return responsible;
            }
        },
        beforeMount() {
            this.gridOptions = {
                floatingFilter: true,
                enableSorting: true,
                domLayout: 'autoHeight',
                enableColResize: true,
                suppressPropertyNamesCheck: true,
                getRowHeight: function(params) {
                    let l = params.data.result.requirement.title.length || 10;
                    return 32 * (Math.floor(l / 45) + 1);
                },
                onGridReady: function(params) {
                    params.api.sizeColumnsToFit();
                },
                context: {
                    componentParent: this
                }
            };
        },
        mounted() {
            let task_id = this.$route.params.id;
            // Если указан номер Задания, то сразу открываем его
            if (task_id !== ':id' && task_id > 0) {
                this.fullscreen_loader = true;
            }
            this.getItems(task_id);
            this.langListener = (e) => {
                if (this.object_groups.length > 0) {
                    this.object_groups[0].title = this.$t('all');
                }
            }
            document.addEventListener('langChanged', this.langListener);
        },
        beforeDestroy() {
            document.removeEventListener('langChanged', this.langListener);
        }
    }
</script>
<style>
    img.jumbotron__image {
        width: 100%;
    }

    .alert.info {
        background-color: #fff !important;
        color: #2196f3;
        border: 1px solid #2196f3 !important;
    }

    li.tn-images {
        float: left;
        list-style: none;
        margin: 0 2px;
    }

    li.tn-images img {
        overflow: hidden;
        border-radius: 2px;
    }

    .pointer {
        cursor: pointer;
    }
</style>