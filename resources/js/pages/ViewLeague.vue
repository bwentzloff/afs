<template>
    <div class="container">
        <div>
            <b-card no-body>
                <b-tabs card fill>
                    <b-tab title="League Home" active>
                        <b-card-text>
                            <strong>Invite link: </strong>https://altfantasysports.com/league/invite/{{ inviteCode }}/
                            
                            --Rosters
                            
                            --Rules
                        </b-card-text>
                    </b-tab>
                    <b-tab title="Players">
                        <b-card-text>
                            <div class="overflow-auto">
                                <b-pagination
                                    v-model="currentPage"
                                    :total-rows="rows"
                                    :per-page="perPage"
                                    aria-controls="player-table"
                                ></b-pagination>

                                <p class="mt-3">Current Page: {{ currentPage }}</p>

                                <b-table
                                    id="player-table"
                                    :items="items"
                                    :per-page="perPage"
                                    :current-page="currentPage"
                                    :fields="fields"
                                    striped 
                                    hover
                                ></b-table>


                            </div>
                        </b-card-text>
                    </b-tab>
                    <b-tab title="Rosters">
                        <b-card-text>View rosters</b-card-text>
                    </b-tab>
                    <b-tab title="Matchups">
                        <b-card-text>Matchups will be auto-generated one week before the first kickoff.</b-card-text>
                    </b-tab>
                    <b-tab title="Draft Queue">
                        <b-card-text>View draft queue</b-card-text>
                    </b-tab>
                </b-tabs>
            </b-card>
        </div>
    </div>
</template>
<script>
  export default {
    data() {
      return {
        leagueId: '',
        perPage: 10,
        currentPage: 1,
        items: Array,
        fields: [
            {key: 'name', sortable: true},
            {key: 'position', sortable: true},
            {key: 'team', sortable: true},
            {key: 'extrainfo', sortable: true},
        ],
        inviteCode: ''
      }
    },
    mounted() {
        this.leagueId = this.$route.params.id;

        // get league info
        axios.get('league/info/'+this.leagueId).then(response => {
            console.log(response);
            this.inviteCode = response.data.invite_code;
        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });

        // get player list
        axios.get('players/xfl').then(response => {
            this.items = response.data;
        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });
    },
    computed: {
      rows() {
        return this.items.length
      }
    }
  }
</script>