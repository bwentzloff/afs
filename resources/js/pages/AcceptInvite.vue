<template>
    <div class="container">
        <div class="card card-default">
            <div class="card-body">
                <form autocomplete="off" @submit.prevent="acceptInvite" method="post">
                <h3>You've been invited to join the league {{ name }}</h3>
                <label for="teamname">Your Team Name:</label>
                <input type="text" id="text" class="form-control" placeholder="" v-model="teamname" required>
                <button type="submit" class="btn btn-primary">Accept Invite</button>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
  export default {
    data() {
      return {
          name: '',
          teamname: '',
          leagueId: '',
      }
    },
    mounted() {
        var code = this.$route.params.code;

        axios.post('league/fromInvite/'+code, {
            
        }).then(response => {
            console.log(response);
            this.name = response.data.name;
            this.leagueId = response.data.id;
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
            teamname: this.teamname,
          }).then(response => {
            console.log(response);
            this.$router.push('/league/view/'+this.leagueId);
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