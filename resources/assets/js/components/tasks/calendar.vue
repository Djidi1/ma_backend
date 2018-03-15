<template>
    <div style="width: 100%">
        <full-calendar :events="fcEvents" locale="en"></full-calendar>
    </div>
</template>

<script>
    let demoEvents = [
        {
            title : 'Демонстрация MobileAudit',
            start : '2018-03-14',
            end : '2018-03-14'
        }
    ];
    export default {
        data () {
            return {
                fcEvents : demoEvents
            }
        },
        methods: {
            getItems() {
                axios.get('/audit_tasks_all')
                    .then(response => {
                        let audit_tasks = response.data;
                        let i;
                        console.log(audit_tasks);
                        for(i = 0; i < audit_tasks.length; i++){
                            if (audit_tasks.hasOwnProperty(i)) {
                                Object.defineProperty(audit_tasks[i], 'start', Object.getOwnPropertyDescriptor(audit_tasks[i], 'date'));
                            }
                        }
                        this.fcEvents = audit_tasks;
                    });
            },
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

<style scoped>

</style>