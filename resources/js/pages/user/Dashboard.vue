<template>
    <div class="container">
        <div class="card card-default">
            <div class="card-header">My Leagues</div>
            <div class="card-body">
                <b-alert show>If you clicked on an invite link and don't see the league now listed here, click the link again. You need to be logged in to accept invites.</b-alert>

                <b-table
                    id="league-table"
                    :items="items"
                    :fields="fields"
                    striped 
                    hover
                    @row-clicked="gotoLeague"
                ></b-table>
                <div>
                  <b-button block variant="primary" href="/league/create">Create A League</b-button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
  export default {
    data() {
      return {
        items: Array,
        fields: [
            {key: 'name', sortable: true},
        ]
      }
    },
    mounted() {
        axios.post('league/getUserLeagues', {
            userAuth: this.$auth.token()
        }).then(response => {
            this.items = response.data;
            console.log(response);
        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });
    },
    components: {
      //
    },
    methods: {
      gotoLeague(record, index) {
        this.$router.push('/league/view/'+record.id);
      }
    }
  }
</script>