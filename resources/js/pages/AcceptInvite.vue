<template>
    <div class="container">
        <div class="card card-default">
            <div class="card-body">
                <h3>You've been invited to join the league {{ name }}</h3>
                <a href="#" @click="acceptInvite">Accept</a>
            </div>
        </div>
    </div>
</template>
<script>
  export default {
    data() {
      return {
          name: ''
      }
    },
    mounted() {
        var code = this.$route.params.code;

        axios.post('league/fromInvite/'+code, {
            
        }).then(response => {
            console.log(response);
            this.name = response.data.name;
        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });
        console.log(code);
    },
    components: {
      //
    },
    methods: {
      acceptInvite() {
          var code = this.$route.params.code;

          axios.post('league/join/'+code, {
            
          }).then(response => {
            console.log(response);
          }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });
      }
    }
  }
</script>