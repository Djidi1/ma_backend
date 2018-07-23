<template>
    <div style="width: 100%; height: 100%">
        <v-progress-linear class="ma-0" v-if="loading" :indeterminate="true"></v-progress-linear>
        <iframe 
            width="100%" 
            height="100%" 
            :src="power_bi_url" 
            frameborder="0" 
            allowFullScreen="true"
        >
        </iframe>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                loading: true,
                power_bi_url: ''

            }
        },
        methods: {
            get_power_bi_url(){
                axios.get('/get-settings')
                    .then(response => {
                        this.power_bi_url = response.data.settings.power_bi_url + '&rs:embed=true';
                        this.loading = false;
                    });
            }
        },
        mounted() {
            this.get_power_bi_url()
        }
    }
</script>

<style scoped>

</style>