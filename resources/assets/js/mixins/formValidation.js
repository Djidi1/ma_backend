export default {
    data () {
        return {
            rules: {                    
                required: (key, v, ignore) => {
                    ignore = ignore || false
                    this.setFormErr(!(!!v), key, ignore)
                    return (!!v) ? true : this.$t('field_required')
                },
                email: (key, v) => {
                    let res = /.+@.+\..+/.test(v)
                    this.setFormErr(!res, key)
                    return (res) ? true : this.$t('email_not_valid')
                },
                
            },
            validFormErrors: []
        }
    },
    computed: {
        validForm() {
            return (this.validFormErrors.length === 0)
        },
    },
    methods: {
        setFormErr(isError, key, ignore) {
            ignore = ignore || false
            if (isError){
                let index = this.validFormErrors.indexOf(key)
                if (index === -1 && !ignore) this.validFormErrors.push(key)
            } else {
                let index = this.validFormErrors.indexOf(key)
                if (index > -1) this.validFormErrors.splice(index, 1)                    
            }
        }
    }
}
