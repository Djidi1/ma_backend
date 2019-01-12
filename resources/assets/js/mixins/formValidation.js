export default {
    data () {
        return {
            rules: {                    
                required: (key, v, ignore) => {
                    ignore = ignore || false
                    let res = false
                    if (Array.isArray(v)) {
                        res = (v.length > 0)
                    } else {
                        res = !!v
                    }
                    this.setFormErr(!res, key, ignore)
                    return (res) ? true : this.$t('field_required')
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
