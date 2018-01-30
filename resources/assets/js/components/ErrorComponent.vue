<script>
    export default {
    	props: ['detail'],
		render() {
    		return '';
		},
        mounted() {
			toastr.error(this.detail);
        }
    }
</script>
