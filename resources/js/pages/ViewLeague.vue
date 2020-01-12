<template>
    <div class="container">
        <div>
            <b-card bg-variant="dark" text-variant="white" v-if="preDraft">
                <b-card-body>
                    <b-card-title>Your draft is in {{ draftTimeDays }} Days, {{ draftTimeHours }} Hours, {{ draftTimeMinutes }} Minutes, {{ draftTimeSeconds }} Seconds</b-card-title>
                    <b-card-text>
                    </b-card-text>
                </b-card-body>
            </b-card>
            <b-card bg-variant="dark" text-variant="white" v-if="postDraft">
                <b-card-body>
                    <b-card-title>Your draft is complete. Waivers, trades, and lineups will start one week before the season opening.</b-card-title>
                    <b-card-text>
                        <b-button v-b-modal.modal-1>
                            See Draft Board
                        </b-button>
                        <b-modal id="modal-1" title="Draft Board">
                            <b-table
                                    id="draftpicks-table"
                                    :items="draftPicks"
                                    :fields="draftBoardFields"
                                    striped 
                                    hover
                                >
                            </b-table>
                        </b-modal>
                    </b-card-text>
                </b-card-body>
            </b-card>
            <b-card bg-variant="dark" text-variant="white" v-if="!preDraft && !postDraft">
                <b-card-body>
                    <b-card-title>Your draft is in progress</b-card-title>
                    <b-card-text>
                        <div>
                            Current Drafter: {{ currentDrafter }}<br />
                                {{ countdownMinutes }} minutes, {{ countdownSeconds }} seconds
                            </div>
                        <b-button v-b-modal.modal-1>
                            See Draft Board
                        </b-button>
                        <b-modal id="modal-1" title="Draft Board">
                            <b-table
                                    id="draftpicks-table"
                                    :items="draftPicks"
                                    :fields="draftBoardFields"
                                    striped 
                                    hover
                                >
                            </b-table>
                        </b-modal>
                        <div>
                            Your Team: {{ draftedQBs }} / {{ qbs }} QBs, {{ draftedRBs }} / {{ rbs }} RBs, {{ draftedWRs }} / {{ wrs }} WRs, {{ draftedTEs }} / {{ tes }} TEs, {{ draftedFlex }} / {{ flex }} FLEX, {{ draftedKs }} / {{ ks }} Ks, {{ draftedDef }} / {{ def }} DEFs, {{ draftedBench }} / {{ bench }} bench
                        </div>
                    </b-card-text>
                </b-card-body>
            </b-card>
            <b-card no-body>
                <b-tabs card fill>
                    <b-tab title="League Home" active>
                        <b-card-text>
                            <h1>{{ leagueName }}</h1>
                            <strong>Invite link: </strong>https://altfantasysports.com/league/invite/{{ inviteCode }}/
                            
                            <h2>Rosters</h2>
                            <table>
                                <tr>
                                    <td>Quarterbacks</td><td>{{ qbs }}</td>
                                </tr>
                                <tr>
                                    <td>Running Backs</td><td>{{ rbs }}</td>
                                </tr>
                                <tr>
                                    <td>Wide Receivers</td><td>{{ wrs }}</td>
                                </tr>
                                <tr>
                                    <td>Tight Ends</td><td>{{ tes }}</td>
                                </tr>
                                <tr>
                                    <td>Flex (RB/WR/TE)</td><td>{{ flex }}</td>
                                </tr>
                                <tr>
                                    <td>Kickers</td><td>{{ ks }}</td>
                                </tr>
                                <tr>
                                    <td>Defense / Special Teams</td><td>{{ def }}</td>
                                </tr>
                            </table>
                            
                            <h2>Rules</h2>
                            <table>
                                <tr>
                                    <td>Points per rushing TD</td>
                                    <td>{{ rule1 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per receiving TD</td>
                                    <td>{{ rule2 }}</td>
                                </tr>
                                <tr>
                                    <td>Points for player returning kick/punt for TD</td>
                                    <td>{{ rule3 }}</td>
                                </tr>
                                <tr>
                                    <td>Points for player returning or recovering a fumble for TD</td>
                                    <td>{{ rule4 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per passing TD</td>
                                    <td>{{ rule5 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per rushing or receiving 1pt conversion</td>
                                    <td>{{ rule6 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per rushing or receiving 2pt conversion</td>
                                    <td>{{ rule7 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per rushing or receiving 3pt conversion</td>
                                    <td>{{ rule8 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per passing 1pt conversion</td>
                                    <td>{{ rule9 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per passing 2pt conversion</td>
                                    <td>{{ rule10 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per passing 3pt conversion</td>
                                    <td>{{ rule11 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per 10 yards rushing</td>
                                    <td>{{ rule12 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per 10 yards receiving</td>
                                    <td>{{ rule13 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per 25 yards passing</td>
                                    <td>{{ rule14 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per intercepted pass</td>
                                    <td>{{ rule15 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per fumble</td>
                                    <td>{{ rule16 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per 50+ yard FG made</td>
                                    <td>{{ rule17 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per 40-49 yard FG made</td>
                                    <td>{{ rule18 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per 1-39 yard FG made</td>
                                    <td>{{ rule19 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per defensive or special teams TD</td>
                                    <td>{{ rule20 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per interception</td>
                                    <td>{{ rule21 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per fumble recovery</td>
                                    <td>{{ rule22 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per blocked punt or field goal</td>
                                    <td>{{ rule23 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per safety</td>
                                    <td>{{ rule24 }}</td>
                                </tr>
                                <tr>
                                    <td>Points per sack</td>
                                    <td>{{ rule25 }}</td>
                                </tr>
                            </table>
                        </b-card-text>
                    </b-tab>
                    <b-tab title="Players">
                        <b-card-text>
                            <div class="overflow-auto">
                                <b-form-select v-model="positionFilter" size="sm" class="w-25">
                                    <option value="all">All Offensive Players</option>
                                    <option value="QB">Quarterbacks</option>
                                    <option value="RB">Running Backs</option>
                                    <option value="WR">Wide Receivers</option>
                                    <option value="TE">Tight Ends</option>
                                    <option value="K">Kickers</option>
                                    <option value="DEF">Defenses</option>
                                </b-form-select>
                                <b-form-input
                                    v-model="nameFilter"
                                        type="search"
                                        placeholder="Search by Name"
                                ></b-form-input>
                                <b-pagination
                                    v-model="currentPage"
                                    :total-rows="rows"
                                    :per-page="perPage"
                                    aria-controls="player-table"
                                ></b-pagination>

                                <b-table
                                    id="player-table"
                                    :items="playersFiltered"
                                    :per-page="perPage"
                                    :current-page="currentPage"
                                    :fields="fields"
                                    striped 
                                    hover
                                >
                                <template v-slot:cell(actions)="data">
                                    <div>
                                        {{ data.item.fantasyTeam }}
                                    </div>
                                    <div v-if="!postDraft && data.item.draftQueue == false && data.item.fantasyTeam == ''">
                                        <b-button variant="success" @click="addToQueue($event, data.item)">
                                            Add to Draft Queue
                                        </b-button>
                                    </div>
                                    <div v-if="!postDraft && preDraft && data.item.draftQueue == true">
                                        <em>In draft queue</em>
                                    </div>
                                    <div v-if="!postDraft && !preDraft && data.item.fantasyTeam == '' && leagueInfo.draft_current_drafter == myteam.id && !processing">
                                        <b-button variant="success" @click="draftPlayer($event, data.item)">
                                            Draft
                                        </b-button>
                                    </div>
                                </template>
                                </b-table>

                                <b-pagination
                                    v-model="currentPage"
                                    :total-rows="rows"
                                    :per-page="perPage"
                                    aria-controls="player-table"
                                ></b-pagination>


                            </div>
                        </b-card-text>
                    </b-tab>
                    <b-tab title="Rosters">
                        <b-card-text>
                            <div class="form-group">
                                <label for="draftpickTime">Team</label>
                                <b-form-select v-model="team_dropdown" :options="teamNames"></b-form-select>
                            </div>
                            <b-table
                                    id="roster-table"
                                    :items="itemsFilteredByTeam"
                                    :fields="fields"
                                    striped 
                                    hover
                                >
                            </b-table>

                        </b-card-text>
                    </b-tab>
                    <b-tab title="Matchups">
                        <b-card-text>Matchups will be auto-generated one week before the first kickoff.</b-card-text>
                    </b-tab>
                    <b-tab title="Draft Queue" v-if="!postDraft">
                        <b-card-text>
                            <b-table
                                    id="queue-table"
                                    :items="queueItems"
                                    :fields="queueFields"
                                    :sort-by="queueSort"
                                    striped 
                                    hover
                                >
                                <template v-slot:cell(actions)="data">
                                    <div>
                                        <b-button variant="success" @click="moveUpQueue($event, data.item)">
                                            Move Up
                                        </b-button>
                                        <b-button variant="success" @click="moveDownQueue($event, data.item)">
                                            Move Down
                                        </b-button>
                                    </div>
                                    <div v-if="!postDraft && !preDraft && data.item.fantasyTeam == '' && leagueInfo.draft_current_drafter == myteam.id && !processing">
                                        <b-button variant="success" @click="draftPlayer($event, data.item)">
                                            Draft
                                        </b-button>
                                    </div>
                                </template>
                                </b-table>

                        </b-card-text>
                    </b-tab>
                    <b-tab title="Commish Tools" v-if="commishTools">
                        <b-card-text>Commish Tools</b-card-text>
                    </b-tab>
                </b-tabs>
            </b-card>
        </div>
    </div>
</template>
<script>
import moment from 'moment'
  export default {
    data() {
      return {
        draftTimeDays: '',
        draftTimeHours: '',
        draftTimeMinutes: '',
        draftTimeSeconds: '',
        countdownMinutes: '',
        countdownSeconds: '',
        currentDrafter: '',
        queuesortfield: 'id',
        leagueId: '',
        leagueInfo: '',
        positionFilter: 'all',
        nameFilter: '',
        perPage: 10,
        currentPage: 1,
        items: [],
        queueItems: [],
        processing: false,
        queueSort: 'queueOrder',
        fields: [
            {key: 'name', sortable: true},
            {key: 'position', sortable: true},
            {key: 'team', sortable: true},
            {key: 'extrainfo', sortable: true, class:"d-none d-lg-table-cell"},
            {key: 'actions'}
        ],
        queueFields: [
            {key: 'name'},
            {key: 'position'},
            {key: 'team' },
            {key: 'actions'}
        ],
        draftBoardFields: [
            {key: 'team_id'},
            {key: 'pick_order'},
            {key: 'playerName'}
        ],
        inviteCode: '',
        preDraft: false,
        postDraft: false,
        qbs: '',
        rbs: '',
        wrs: '',
        tes: '',
        flex: '',
        ks: '',
        def: '',
        bench: '',
        leagueName: '',
        rule1: '',
        rule2: '',
        rule3: '',
        rule4: '',
        rule5: '',
        rule6: '',
        rule7: '',
        rule8: '',
        rule9: '',
        rule10: '',
        rule11: '',
        rule12: '',
        rule13: '',
        rule14: '',
        rule15: '',
        rule16: '',
        rule17: '',
        rule18: '',
        rule19: '',
        rule20: '',
        rule21: '',
        rule22: '',
        rule23: '',
        rule24: '',
        rule25: '',
        teams: [],
        draftPicks: [],
        myteam: '',
        teamNames: [],
        commishTools: false,
        rosters: [],
        team_dropdown: '',
        lastUpdate: 0,
        draftedQBs: 0,
        draftedRBs: 0,
        draftedWRs: 0,
        draftedTEs: 0,
        draftedFlex: 0,
        draftedKs: 0,
        draftedDef: 0,
        draftedBench: 0,
      }
    },
    mounted() {
        this.leagueId = this.$route.params.id;

        this.getLeagueInfo();

        // get team info
        axios.get('league/teams/'+this.leagueId).then(response => {
            console.log(response);
            this.teams = response.data;

        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });

        

        // get my team
        axios.get('league/myteam/'+this.leagueId).then(response => {
            this.myteam = response.data;
            this.team_dropdown = this.myteam.id
            console.log(response.data);

            if (this.myteam.user_id == this.leagueInfo.commish_id) {
                this.commishTools = true;
            }


        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });

        this.refreshPlayerList();
        this.getLastUpdate()
    },
    computed: {
      rows() {
        return this.items.length
      },
      playersFiltered() {
          var filtered = this.items.filter((el) => {
            if (this.positionFilter == "all") {
                if (el.position == "QB" || el.position == "RB" || el.position == "WR" || el.position == "TE" || el.position == "K") {
                    if (this.nameFilter == '') {
                            return true;
                    } else {
                        return el.name.toLowerCase().includes(this.nameFilter.toLowerCase());
                    }
                } else {
                    return false;
                }
            } else {
                if (this.nameFilter == '') {
                    return (this.positionFilter == el.position)
                } else {
                    return (this.positionFilter == el.position) && el.name.toLowerCase().includes(this.nameFilter.toLowerCase());
                }
            }
              
          });
          this.currentPage = 1
          return filtered;
      },
      itemsFilteredByTeam() {
          var teamName = '';
          for (var teamNames = 0; teamNames < this.teams.length; teamNames++) {
                if (this.teams[teamNames].id == this.team_dropdown) {
                    teamName = this.$data.teams[teamNames].name;
                }
            }

            var filtered = this.items.filter((el) => {
                return (teamName == el.fantasyTeam) 
            });
            return filtered;
      }
    },
    filters: {
        useMoment: function (date) {
            console.log('here');
            return moment.utc(date).fromNow();
        }
    },

    methods: {
        updateDraftBoard() {
            // get draftOrder info
            axios.get('league/draftOrder/'+this.leagueId).then(response => {
                this.draftPicks = response.data;
                for (var i = 0; i < this.draftPicks.length; i++) {
                        this.draftPicks[i].team_id = this.getTeamNameFromId(this.draftPicks[i].team_id);
                }
                for (var i = 0; i < this.draftPicks.length; i++) {
                        this.draftPicks[i].playerName = this.getPlayerNameFromId(this.draftPicks[i].player_id);
                }
                

            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        getTeamNameFromId(teamId) {
            for (var i=0; i < this.teams.length; i++) {
                if (this.teams[i].id == teamId) {
                    return this.teams[i].name;
                }
            }
        },
        getPlayerPositionFromId(player_id) {
            for (var i = 0; i < this.items.length; i++) {
                if (this.items[i].id == player_id) {
                    return this.items[i].position
                }
            }
        },
        getPlayerNameFromId(player_id) {
            for (var i = 0; i < this.items.length; i++) {
                if (this.items[i].id == player_id) {
                    
                    return this.items[i].name + " (" + this.items[i].position + ")"
                }
            }
        },
        getLeagueInfo() {
            // get league info
            axios.get('league/info/'+this.leagueId).then(response => {
            console.log(response);
            this.inviteCode = response.data.invite_code;
            this.leagueInfo = response.data;
            console.log(this.leagueInfo);
            if (this.leagueInfo.draft_status == 0) {
                this.preDraft = true;
                this.postDraft = false;
            } else if (this.leagueInfo.draft_status == 1) {
                this.preDraft = false;
                this.postDraft = false;
            } else {
                this.postDraft = true;
                this.preDraft = false;
            }
            /*moment.relativeTimeThreshold('h', 24*26);
            var timeToGo = moment(this.leagueInfo.draft_datetime).fromNow();
            console.log(timeToGo);
            this.draftTimeDays = timeToGo.days();
            this.draftTimeHours = timeToGo.hours();
            this.draftTimeMinutes = timeToGo.minutes();*/
            var diff = moment.utc(this.leagueInfo.draft_datetime).diff(moment());
            var diffDuration = moment.duration(diff);
            this.draftTimeDays = diffDuration.days();
            this.draftTimeHours = diffDuration.hours();
            this.draftTimeMinutes = diffDuration.minutes();
            this.draftTimeSeconds = diffDuration.seconds();

            var countdownDiff = moment.utc(this.leagueInfo.draft_nextpick).diff(moment());
            diffDuration = moment.duration(countdownDiff);
            this.countdownMinutes = diffDuration.minutes();
            this.countdownSeconds = diffDuration.seconds();

            this.currentDrafter = this.getTeamNameFromId(this.leagueInfo.draft_current_drafter);

            this.updateTimer();
            
            this.leagueName = this.leagueInfo.name;
            this.qbs = this.leagueInfo.qbs;
            this.rbs = this.leagueInfo.rbs;
            this.wrs = this.leagueInfo.wrs;
            this.tes = this.leagueInfo.tes;
            this.flex = this.leagueInfo.flex;
            this.ks = this.leagueInfo.ks;
            this.def = this.leagueInfo.def;
            this.bench = this.leagueInfo.bench;
            this.rule1 = this.leagueInfo.rule1;
            this.rule2 = this.leagueInfo.rule2;
            this.rule3 = this.leagueInfo.rule3;
            this.rule4 = this.leagueInfo.rule4;
            this.rule5 = this.leagueInfo.rule5;
            this.rule6 = this.leagueInfo.rule6;
            this.rule7 = this.leagueInfo.rule7;
            this.rule8 = this.leagueInfo.rule8;
            this.rule9 = this.leagueInfo.rule9;
            this.rule10 = this.leagueInfo.rule10;
            this.rule11 = this.leagueInfo.rule11;
            this.rule12 = this.leagueInfo.rule12;
            this.rule13 = this.leagueInfo.rule13;
            this.rule14 = this.leagueInfo.rule14;
            this.rule15 = this.leagueInfo.rule15;
            this.rule16 = this.leagueInfo.rule16;
            this.rule17 = this.leagueInfo.rule17;
            this.rule18 = this.leagueInfo.rule18;
            this.rule19 = this.leagueInfo.rule19;
            this.rule20 = this.leagueInfo.rule20;
            this.rule21 = this.leagueInfo.rule21;
            this.rule22 = this.leagueInfo.rule22;
            this.rule23 = this.leagueInfo.rule23;
            this.rule24 = this.leagueInfo.rule24;
            this.rule25 = this.leagueInfo.rule25;
            this.teamNames = [];
            for (var i = 0; i < this.leagueInfo.teams.length; i++) {
                this.teamNames.push({
                    value: this.leagueInfo.teams[i].id,
                    text: this.leagueInfo.teams[i].name
                })
                
            }
            this.$data.processing = false;
        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });
        },
        updateTimer() {
            if (this.preDraft) {
                var diff = moment.utc(this.leagueInfo.draft_datetime).diff(moment());
                const diffDuration = moment.duration(diff);
                this.draftTimeDays = diffDuration.days();
                this.draftTimeHours = diffDuration.hours();
                this.draftTimeMinutes = diffDuration.minutes();
                this.draftTimeSeconds = diffDuration.seconds();
                var that = this;
            } else {
                var diff = moment.utc(this.leagueInfo.draft_nextpick).diff(moment());
                const diffDuration = moment.duration(diff);
                this.countdownMinutes = diffDuration.minutes();
                this.countdownSeconds = diffDuration.seconds();
                var that = this;
            }
            setTimeout(() => { this.updateTimer(); }, 1000);
        },
        getLastUpdate() {
            axios.get('league/getLastUpdate/'+this.$data.leagueId).then(response => {
                if (this.$data.lastUpdate != response.data) {
                    this.getLeagueInfo();
                    this.refreshPlayerList();
                    this.$data.lastUpdate = response.data;
                    console.log(response.data);
                }
            }).catch(error => {
                console.log(error);
                /*if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }*/
            });
            setTimeout(() => { this.getLastUpdate(); }, 2000);
        },
        addToQueue(event, player) {
            console.log(player)
            axios.post('player/queue', {
                player_id: player.id,
                leagueId: this.$data.leagueId,
                userAuth: this.$auth.token()
            }).then(response => {
                // refresh draft queue
                // refresh player list
                this.refreshPlayerList();
                //this.$router.push('/dashboard');
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        draftPlayer(event, player) {
            this.$data.processing = true;
            console.log(player)
            axios.post('player/draft', {
                player_id: player.id,
                leagueId: this.$data.leagueId,
            }).then(response => {
                // refresh draft queue
                // refresh player list
                this.refreshPlayerList();
                //this.$router.push('/dashboard');
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        moveUpQueue(event, player) {
            axios.post('player/moveUpQueue', {
                player_id: player.id,
                leagueId: this.$data.leagueId,
            }).then(response => {
                
                this.refreshQueueItems();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });

        },
        moveDownQueue(event, player) {
            
            axios.post('player/moveDownQueue', {
                player_id: player.id,
                leagueId: this.$data.leagueId,
            }).then(response => {
                this.refreshQueueItems();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        refreshQueueItems() {
            axios.post('player/getqueue', {
                leagueId: this.$data.leagueId,
                userAuth: this.$auth.token()
            }).then(response => {
                this.$data.queueItems = []
                this.$data.items.forEach((player_item) => {
                    for (var i = 0; i < response.data.length; i++) {
                        if (response.data[i].player_id == player_item.id) {
                            player_item.draftQueue = true;
                            player_item.queueOrder = response.data[i].id;
                            this.$data.queueItems.push(player_item);
                        }
                    }
                })
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });
        },
        refreshPlayerList() {
            // get player list
            axios.get('players/xfl').then(response => {
                this.$data.items = []
                response.data.forEach((item) => {
                    item.draftQueue = false
                    item.fantasyTeam = ''
                    this.$data.items.push(item)
                })
                // get queue items
                this.refreshQueueItems();
                this.assignTeams();
                this.updateDraftBoard()
                //this.items = response.data;
            }).catch(error => {
                console.log(error);
                /*if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }*/
            });

            

            // get rosters
            axios.post('league/getrosters', {
                leagueId: this.$data.leagueId,
            }).then(response => {
                this.$data.rosters = response.data;
                this.assignTeams();
                this.updateDraftBoard()

                this.$data.draftedQBs = 0;
                this.$data.draftedWRs = 0;
                this.$data.draftedRBs = 0;
                this.$data.draftedTEs = 0;
                this.$data.draftedFlex = 0;
                this.$data.draftedKs = 0;
                this.$data.draftedDef = 0;
                this.$data.draftedBench = 0;
                
                // calculate drafted player totals
                for (var i = 0; i < this.$data.rosters[this.$data.myteam.id].length; i++) {
                    var tempPos = this.getPlayerPositionFromId(this.$data.rosters[this.$data.myteam.id][i].player_id);
                    if (tempPos == "QB") {
                        if (this.$data.draftedQBs >= this.$data.qbs) {
                            this.$data.draftedBench = this.$data.draftedBench + 1;
                        } else {
                            this.$data.draftedQBs = this.$data.draftedQBs + 1;
                        }
                    } else if (tempPos == "RB") {
                        if (this.$data.draftedRBs >= this.$data.rbs) {
                            if (this.$data.draftedFlex >= this.$data.flex) {
                                this.$data.draftedBench = this.$data.draftedBench + 1;
                            } else {
                                this.$data.draftedFlex = this.$data.draftedFlex + 1;
                            }
                        } else {
                            this.$data.draftedRBs = this.$data.draftedRBs + 1;
                        }
                    } else if (tempPos == "WR") {
                        if (this.$data.draftedWRs >= this.$data.wrs) {
                            if (this.$data.draftedFlex >= this.$data.flex) {
                                this.$data.draftedBench = this.$data.draftedBench + 1;
                            } else {
                                this.$data.draftedFlex = this.$data.draftedFlex + 1;
                            }
                        } else {
                            this.$data.draftedWRs = this.$data.draftedWRs + 1;
                        }
                    } else if (tempPos == "TE") {
                        if (this.$data.draftedTEs >= this.$data.tes) {
                            if (this.$data.draftedFlex >= this.$data.flex) {
                                this.$data.draftedBench = this.$data.draftedBench + 1;
                            } else {
                                this.$data.draftedFlex = this.$data.draftedFlex + 1;
                            }
                        } else {
                            this.$data.draftedTEs = this.$data.draftedTEs + 1;
                        }
                    } else if (tempPos == "K") {
                        if (this.$data.draftedKs >= this.$data.ks) {
                            this.$data.draftedBench = this.$data.draftedBench + 1;
                        } else {
                            this.$data.draftedKs = this.$data.draftedKs + 1;
                        }
                    } else if (tempPos == "DEF") {
                        if (this.$data.draftedDef >= this.$data.def) {
                            this.$data.draftedBench = this.$data.draftedBench + 1;
                        } else {
                            this.$data.draftedDef = this.$data.draftedDef + 1;
                        }
                    }
                }
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });


        },
        assignTeams() {
            console.log('assignTeams');
            let keys = Object.keys(this.$data.rosters)
            console.log(this.$data.teams)
            for (var teamRoster = 0; teamRoster < keys.length; teamRoster++) {
                for (var rosterPlayer = 0; rosterPlayer < this.$data.rosters[keys[teamRoster]].length; rosterPlayer++) {
                    for (var players = 0; players < this.$data.items.length; players++) {
                        if (this.$data.items[players].id == this.$data.rosters[keys[teamRoster]][rosterPlayer].player_id) {
                            for (var teamNames = 0; teamNames < this.$data.teams.length; teamNames++) {
                                if (this.$data.teams[teamNames].id == this.$data.rosters[keys[teamRoster]][rosterPlayer].team_id) {
                                    this.$data.items[players].fantasyTeam = this.$data.teams[teamNames].name;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
  }
</script>