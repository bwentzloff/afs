<template>
    <div class="container">
        <div>
            <b-card bg-variant="dark" text-variant="white" v-if="preDraft">
                <b-card-body>
                    <b-card-title>Your draft is in {{ draftTimeDays }} Days, {{ draftTimeHours }} Hours, {{ draftTimeMinutes }} Minutes, {{ draftTimeSeconds }} Seconds</b-card-title>
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
            <b-card bg-variant="dark" text-variant="white" v-if="postDraft && ((waivers.length > 0) || (trades.length > 0))" >
                
                <b-card-body>
                    <b-card-title>Pending Transactions</b-card-title>
                    <b-card-text>
                        <template>
                            <!--small>Your league's waivers process on {{ leagueInfo.waiver_day }} mornings</small-->
                            <table>
                            <tr v-for="waiver in waivers">
                                <td>Waiver request for {{ waiver.player_name }} (drop {{ waiver.drop_player_name }}) </td>
                                <td><b-button @click="cancelWaiver(waiver.id)">
                                    Cancel
                                </b-button></td>
                            </tr>
                            <tr v-for="trade in trades">
                                <td>Trade Proposal:<br />
                                    {{ getTeamNameFromId(trade.team1_id) }} gets: 
                                    <div v-for="player in trade.team1_selected">{{ getPlayerNameFromId(player) }}</div>
                                    {{ getTeamNameFromId(trade.team2_id) }} gets: 
                                    <div v-for="player in trade.team2_selected">{{ getPlayerNameFromId(player) }}</div>
                                    <hr />
                                </td>
                                <td>
                                <b-button @click="cancelTrade(trade.id)" v-if="myteam.id == trade.team1_id">
                                    Cancel
                                </b-button>
                                <b-button @click="acceptTrade(trade.id)" v-if="myteam.id == trade.team2_id">
                                    Accept
                                </b-button>
                                <b-button @click="cancelTrade(trade.id)" v-if="myteam.id == trade.team2_id">
                                    Reject
                                </b-button>
                                <hr /></td>
                            </tr>
                            </table>
                        </template>
                    </b-card-text>
                </b-card-body>
            </b-card>
            <b-card bg-variant="dark" text-variant="white" v-if="!preDraft && !postDraft">
                <b-card-body>
                    <b-card-title>Your draft is in progress</b-card-title>
                    <b-card-text>
                        <div>
                            Current Drafter: {{ currentDrafter }}<br />
                                {{ countdownHours }} hours, {{ countdownMinutes }} minutes, {{ countdownSeconds }} seconds
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
            <div  v-if="isCommish">
                <b-form-checkbox v-model="commishTools" size="sm">Show Commish Tools</b-form-checkbox>
            </div>
            <b-card no-body>
                <b-tabs card fill>
                    <b-tab title="League Home" active>
                        <b-card-text>
                            <h1>{{ leagueName }}</h1>
                            <div v-if="leagueInfo.league_type == 1">
                            <b-table
                                    id="standings-table"
                                    :items="teams"
                                    :fields="standingsFields"
                                    striped 
                                    hover
                                >
                            </b-table>
                            </div>
                            <div v-if="leagueInfo.league_type > 1">
                            <b-table
                                    id="standings-table"
                                    :items="teams"
                                    :fields="standingsFieldsTotalPoints"
                                    striped 
                                    hover
                                >
                            </b-table>
                            </div>



                            <br /><br /><br />
                            <!--form autocomplete="off" @submit.prevent="updateRoster" method="post">
                                <b-button @click="leaveLeague()">
                                    Leave League
                                </b-button><br /><br />
                            </form-->
                                                    <b-alert show>To invite teams to your league, send them this link: https://altfantasysports.com/league/invite/{{ inviteCode }}/</b-alert>

                            
                            <h2>Current Teams</h2>
                            <ul id="team-name-list">
                                <li v-for="(team,key) in teamNames" v-if="team.text != 'No Team'">
                                    {{ team.text }}
                                </li>
                            </ul>
                            <div v-if="myteam.id">
                                <h2>My Team Name</h2>
                                    <input type="text" id="myteam" class="form-control" placeholder="" v-model="myteam.name" required>
                                    <b-button variant="success" @click="updateName($event)">
                                                Update Name
                                            </b-button>
                            </div>
                            <br /><br />
                            <b-button v-b-modal.modal-5>
                            See Draft Board
                        </b-button>
                        <b-modal id="modal-5" title="Draft Board">
                            <b-table
                                    id="draftpicks-table-5"
                                    :items="draftPicks"
                                    :fields="draftBoardFields"
                                    striped 
                                    hover
                                >
                            </b-table>
                        </b-modal>
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
                                    <td>Superflex (QB/RB/WR/TE)</td><td>{{ superflex }}</td>
                                </tr>
                                <tr>
                                    <td>Kickers</td><td>{{ ks }}</td>
                                </tr>
                                <tr>
                                    <td>Defense / Special Teams</td><td>{{ def }}</td>
                                </tr>
                                <tr>
                                    <td>Bench</td><td>{{ bench }}</td>
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
                                    <td>Points per reception</td>
                                    <td>{{ rule26 }}</td>
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
                                <tr>
                                    <td>0 Points Allowed</td>
                                    <td>{{ rule27 }}</td>
                                </tr>
                                <tr>
                                    <td>1-6 Points Allowed</td>
                                    <td>{{ rule28 }}</td>
                                </tr>
                                <tr>
                                    <td>7-13 Points Allowed</td>
                                    <td>{{ rule29 }}</td>
                                </tr>
                                <tr>
                                    <td>14-20 Points Allowed</td>
                                    <td>{{ rule30 }}</td>
                                </tr>
                                <tr>
                                    <td>21-27 Points Allowed</td>
                                    <td>{{ rule31 }}</td>
                                </tr>
                                <tr>
                                    <td>28-34 Points Allowed</td>
                                    <td>{{ rule32 }}</td>
                                </tr>
                                <tr>
                                    <td>35-41 Points Allowed</td>
                                    <td>{{ rule33 }}</td>
                                </tr>
                                <tr>
                                    <td>42+ Points Allowed</td>
                                    <td>{{ rule34 }}</td>
                                </tr>
                            </table>

                            <b-button @click="deleteLeague()" v-if="commishTools">
                                    Delete League
                                </b-button><br /><br />
                        </b-card-text>
                    </b-tab>
                    <b-tab title="Transaction Log">
                        <b-card-text>
                            <b-table
                                    id="transaction-table"
                                    :items="leagueTransactions"
                                    :fields="leagueTransactionFields"
                                    striped 
                                    hover
                                >
                                <template v-slot:cell(team1_id)="data">
                                    <div v-if="data.item.type == 2 || data.item.type == 3">
                                        {{ getTeamNameFromId(data.item.team1_id) }}
                                    </div>
                                </template>
                                <template v-slot:cell(type)="data">
                                    <div v-if="data.item.type == 1">
                                        Trade
                                    </div>
                                    <div v-if="data.item.type == 2">
                                        Waiver
                                    </div>
                                    <div v-if="data.item.type == 3">
                                        Free Agent
                                    </div>
                                </template>
                                <template v-slot:cell(player_id)="data">
                                    <div v-if="data.item.player_id > 0">
                                    {{ getPlayerNameFromId(data.item.player_id) }}
                                    </div>
                                </template>
                                <template v-slot:cell(drop_player_id)="data">
                                    <div v-if="data.item.drop_player_id > 0">
                                    {{ getPlayerNameFromId(data.item.drop_player_id) }}
                                    </div>
                                </template>
                                <template v-slot:cell(team1_selected)="data">
                                    <div v-if="data.item.team1_selected">
                                        {{ getTeamNameFromId(data.item.team1_id) }} gets 
                                        
                                        <div v-for="(n, index) in data.item.team1_selected">
                                            {{ getPlayerNameFromId(data.item.team1_selected[index]) }}<br />
                                        </div>
                                        
                                        
                                    </div>
                                </template>
                                <template v-slot:cell(team2_selected)="data">
                                    <div v-if="data.item.team2_selected">
                                        {{ getTeamNameFromId(data.item.team2_id) }} gets 
                                        
                                        <div v-for="(n, index) in data.item.team2_selected">
                                            {{ getPlayerNameFromId(data.item.team2_selected[index]) }}<br />
                                        </div>
                                        
                                        
                                    </div>
                                </template>
                            </b-table>
                        </b-card-text>
                    </b-tab>
                    <b-tab title="Players">
                        <b-card-text>
                            <div class="overflow-auto">
                                <b-form-select v-model="positionFilter" size="sm" class="w-25" v-on:change="resetPagination">
                                    <option value="all">All Offensive Players</option>
                                    <option value="QB">Quarterbacks</option>
                                    <option value="RB">Running Backs</option>
                                    <option value="WR">Wide Receivers</option>
                                    <option value="TE">Tight Ends</option>
                                    <option value="K">Kickers</option>
                                    <option value="DEF">Defenses</option>
                                </b-form-select>
                                <b-form-select v-model="teamFilter" :options="teamNames" size="sm" class="w-25">
                                    <template v-slot:first>
                                        <b-form-select-option :value="-1">All Players</b-form-select-option>
                                    </template>
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
                                <template v-slot:cell(combinedInfo)="data">
                                    <b-link @click="showPlayerCard(data.item)">{{ data.item.name }}</b-link><br />
                                    {{ data.item.position }} - {{ data.item.team }}
                                    <div v-if="commishTools">
                                        <small>Change player eligibility {{ data.item.position }}</small>
                                        <select v-on:change="updatePlayerEligibility($event, data.item)" v-model="data.item.position">
                                            <option value="QB">QB</option>
                                            <option value="RB">RB</option>
                                            <option value="WR">WR</option>
                                            <option value="TE">TE</option>
                                            <option value="K">K</option>
                                            <option value="DEF">DEF</option>
                                        </select>
                                    </div>
                                </template>
                                
                                <template v-slot:cell(actions)="data">
                                    <div>
                                        {{ data.item.fantasyTeam }}
                                        <b-button variant="success" @click="createTrade($event, data.item)" v-if="postDraft && data.item.fantasyTeam && data.item.fantasyTeam != myteam.name">
                                            Propose Trade
                                        </b-button>
                                    </div>
                                    <div v-if="!postDraft && data.item.draftQueue == false && data.item.fantasyTeam == ''">
                                        <b-button variant="success" @click="addToQueue($event, data.item)">
                                            Add to Draft Queue
                                        </b-button>
                                    </div>
                                    <div v-if="!postDraft && data.item.draftQueue == true && data.item.fantasyTeam == ''">
                                        <em>In draft queue</em>
                                    </div>
                                    <div v-if="!postDraft && !preDraft && data.item.fantasyTeam == '' && leagueInfo.draft_current_drafter == myteam.id && !processing">
                                        <b-button variant="success" @click="draftPlayer($event, data.item)">
                                            Draft
                                        </b-button>
                                    </div>
                                    <div v-if="postDraft && data.item.fantasyTeam == '' && !data.item.waiverMade && leagueInfo.waiver_status == 0">
                                        <b-button variant="success" @click="createClaim($event, data.item)">
                                            Create Waiver Claim
                                        </b-button>
                                    </div>
                                    <div v-if="postDraft && data.item.fantasyTeam == '' && !data.item.waiverMade && leagueInfo.waiver_status == 1">
                                        <b-button variant="success" @click="addFreeAgent($event, data.item)">
                                            Add Free Agent
                                        </b-button>
                                    </div>
                                    <div v-if="postDraft && data.item.fantasyTeam == '' && data.item.waiverMade">
                                        <em>Waiver claim created</em>
                                    </div>
                                    
                                    <div v-if="(commishTools) && leagueInfo.draft_status == 2">
                                        Commish Tools -- Assign Team:
                                        <b-form-select v-model="data.item.fantasyTeamId" :options="teamNames"
                                            v-on:change="commishUpdatePlayerTeam($event, data.item)"
                                        ></b-form-select>
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
                                <template v-slot:cell(combinedInfo)="data">
                                    <b-link @click="showPlayerCard(data.item)">{{ data.item.name }}</b-link><br />
                                    {{ data.item.position }} - {{ data.item.team }}
                                </template>
                            <template v-slot:cell(actions)="data">
                                <div v-if="commishTools && leagueInfo.draft_status == 2">
                                        Commish Tools -- Assign Team:
                                        <b-form-select v-model="data.item.fantasyTeamId" :options="teamNames"
                                            v-on:change="commishUpdatePlayerTeam($event, data.item)"
                                        ></b-form-select>
                                        </div>
                                    <b-button variant="danger" @click="dropPlayer(data.item.id)" v-if="myteam.id == data.item.fantasyTeamId">
                                            Drop player
                                        </b-button>
                                </template>
                            </b-table>

                        </b-card-text>
                    </b-tab>
                    <b-tab title="Matchups" v-if="leagueInfo.league_type == 1">
                        
                        <b-card-text>
                            <b-alert show v-if="commishTools">
                                If your matchups look weird or some team didn't get added to the matchups or something like that, click this button to refresh your matchup list. Keep in mind this will erase any customizations you've made. THIS WILL RESET ALL OF YOUR MATCHUPS, EVEN WEEKS THAT HAVE ALREADY GONE BY. YOU CANNOT UNDO THIS.
                                <b-button variant="danger" @click="fixMatchups()">
                                            Fix matchups
                                        </b-button>
                            </b-alert>

                            
                            <b-table
                                    id="matchups-table"
                                    :items="matchups"
                                    :fields="matchupsFields"
                                    :sort-by="matchupsSort"
                                    striped 
                                    hover
                                >
                                <template v-slot:cell(home_name)="data">
                                    <div v-if="!data.item.home_name">
                                        Bye
                                    </div>
                                    <div>{{ data.item.home_name }}</div>
                                    <div v-if="commishTools">
                                        <small>Change home team to:</small>
                                        <b-form-select  :options="teamNames"
                                            v-on:change="updateMatchup($event, data.item, 'home')"
                                        ></b-form-select>
                                    </div>
                                </template>
                                <template v-slot:cell(away_name)="data">
                                    <div v-if="!data.item.away_name">
                                        Bye
                                    </div>
                                    <div>{{ data.item.away_name }}</div>
                                    <div v-if="commishTools">
                                        <small>Change away team to:</small>
                                        <b-form-select  :options="teamNames"
                                            v-on:change="updateMatchup($event, data.item, 'away')"
                                        ></b-form-select> 
                                    </div>
                                </template>
                                <template v-slot:cell(actions)="data">
                                    <div v-if="(data.item.home_id == myteam.id || data.item.away_id == myteam.id) && leagueInfo.week == data.item.week">
                                        <b-button variant="success" @click="showMatchup($event, data.item)">
                                            Set your lineup
                                        </b-button>
                                    </div>
                                    <div v-if="(data.item.home_id != myteam.id && data.item.away_id != myteam.id) || (leagueInfo.week != data.item.week)">
                                        <b-button @click="showMatchup($event, data.item)">
                                            View Matchup
                                        </b-button>
                                    </div>
                                </template>
                            </b-table>
                        </b-card-text>
                        
                    </b-tab>
                    
                    <b-tab title="Lineups" v-if="leagueInfo.league_type > 1">
                        <b-card-text>
                            <b-alert show v-if="commishTools">
                                If your matchups look weird or some team didn't get added to the matchups or something like that, click this button to refresh your matchup list. Keep in mind this will erase any customizations you've made.
                                <b-button variant="danger" @click="fixMatchups()">
                                            Fix matchups
                                        </b-button>
                            </b-alert>
                            <b-table
                                    id="lineups-table"
                                    :items="matchups"
                                    :fields="lineupsFields"
                                    :sort-by="lineupsSort"
                                    striped 
                                    hover
                                >
                                <template v-slot:cell(actions)="data">
                                    <div v-if="(data.item.home_id == myteam.id) && (leagueInfo.week == data.item.week)">
                                        <b-button variant="success" @click="showLineup($event, data.item, data.item.week)">
                                            Set your lineup
                                        </b-button>
                                    </div>
                                    <div v-if="(data.item.home_id != myteam.id) || (leagueInfo.week != data.item.week)">
                                        <b-button @click="showLineup($event, data.item, data.item.week)">
                                            View Matchup
                                        </b-button>
                                    </div>
                                </template>
                            </b-table>
                            
                        </b-card-text>
                        
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
                                        <b-button variant="danger" @click="removeFromQueue($event, data.item)">
                                            Remove
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
                    <b-tab title="Process Waivers" v-if="isCommish">
                        <b-card-text>
                            Current waiver period: <select v-model="leagueInfo.waiver_status" v-on:change="updateWaiverStatus">
                                <option :selected="leagueInfo.waiver_status == 0? 'true' : 'false'" value="0">Waivers</option>
                                <option :selected="leagueInfo.waiver_status == 1? 'true' : 'false'" value="1">Free Agency</option>
                            </select>
                            <b-table
                                    id="waiver-table"
                                    :items="leagueWaivers"
                                    :fields="leagueWaiverFields"
                                    striped 
                                    hover
                                >
                                <template v-slot:cell(actions)="data">
                                    <div>
                                        <b-button variant="success" @click="grantWaiver($event, data.item)">
                                            Grant
                                        </b-button>
                                        <b-button variant="danger" @click="denyWaiver($event, data.item)">
                                            Deny
                                        </b-button>
                                    </div>
                                </template>
                            </b-table>
                        </b-card-text>
                    </b-tab>
                    <b-tab title="Commish Tools" v-if="commishTools">
                        <b-card-text>
                            <h1>Draft Settings</h1>
                            <form autocomplete="off" @submit.prevent="updateDraft" method="post">
                                <b-alert variant="danger" show>Warning: Updating your draft settings will reset the draft. Any picks already made will be reverted. This cannot be undone. I don't recommend changing any settings during your draft.</b-alert>

                                <div class="form-group">
                                    <label for="drafttime">Update Draft Date/Time <small><em>Times shown are in your local timezone. Other members of your league will have the time converted to their local timezone.</em></small></label>
                                    <datetime v-model="updateDraftDatetime" type="datetime" zone="local" value-zone="UTC" :use12-hour=true title="Draft Time" name="draft_datetime"></datetime>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Draft Date/Time</button>
                            </form>
                            <h2>Draft Order</h2>
                            <b-table
                                    id="draftOrder-table"
                                    :items="teamDraftOrder"
                                    :fields="draftOrderFields"
                                    :sort-by="draftOrderSort"
                                    striped 
                                    hover
                                >
                                <template v-slot:cell(actions)="data">
                                    <div>
                                        <b-button variant="success" @click="moveUpDraftOrder($event, data.item)" v-if="data.item.draftOrder != 1">
                                            Move Up
                                        </b-button>
                                        <b-button variant="success" @click="moveDownDraftOrder($event, data.item)">
                                            Move Down
                                        </b-button>
                                        <b-button @click="removeTeam($event, data.item)">
                                            Remove Team
                                        </b-button>
                                    </div>
                                </template>
                            </b-table>
                            <h2>Other Settings</h2>
                            <div class="form-group">
                                <label for="draftpickTime">Time allowed for each draft pick</label>
                                <b-form-select v-model="leagueInfo.draftpick_time" :options="draftpickTime_options"></b-form-select>
                            </div>
                            <div class="form-group">
                                <label for="waiver_day">Day waivers are processed</label>
                                <b-form-select v-model="leagueInfo.waiver_day" :options="waiver_day_options">
                                </b-form-select>
                            </div>
                            <b-button @click="updateSettings($event)">
                                            Update Settings
                                        </b-button>
                            <h2>League Size</h2>
                            <select v-model="leagueInfo.maxSize">
                                <option :selected="leagueInfo.maxSize == 2? 'true' : 'false'">2</option>
                                <option :selected="leagueInfo.maxSize == 4? 'true' : 'false'">4</option>
                                <option :selected="leagueInfo.maxSize == 6? 'true' : 'false'">6</option>
                                <option :selected="leagueInfo.maxSize == 8? 'true' : 'false'">8</option>
                                <option :selected="leagueInfo.maxSize == 10? 'true' : 'false'">10</option>
                                <option :selected="leagueInfo.maxSize == 12? 'true' : 'false'">12</option>
                                <option :selected="leagueInfo.maxSize == 14? 'true' : 'false'">14</option>
                                <option :selected="leagueInfo.maxSize == 16? 'true' : 'false'">16</option>
                            </select>
                            <b-button @click="changeLeagueSize($event)">
                                            Update League Size
                                        </b-button>
                            <h2>League Type</h2>
                            <select v-model="leagueInfo.league_type">
                                <option :selected="leagueInfo.league_type == 1? 'true' : 'false'" value="1">Head to Head</option>
                                <option :selected="leagueInfo.league_type == 2? 'true' : 'false'" value="2">Total Points</option>
                                <option :selected="leagueInfo.league_type == 3? 'true' : 'false'" value="3">Guillotine</option>
                            </select>
                            <b-button @click="changeLeagueType($event)">
                                            Update League Type
                                        </b-button>
                            <h2># of Weeks of Playoffs</h2>
                            <select v-model="leagueInfo.playoff_length">
                                <option :selected="leagueInfo.playoff_length == 1? 'true' : 'false'">1</option>
                                <option :selected="leagueInfo.playoff_length == 2? 'true' : 'false'">2</option>
                                <option :selected="leagueInfo.playoff_length == 3? 'true' : 'false'">3</option>
                            </select>
                            <b-button @click="changePlayoffLength($event)">
                                            Update Playoffs Length
                                        </b-button>
                            <div class="form-group">
                                <h2>Rosters</h2>
                                <form autocomplete="off" @submit.prevent="updateRoster" method="post">
                                <label for="qbs">Quarterbacks</label>
                                <b-form-select v-model="qbs" :options="qb_options" size="sm"></b-form-select>
                                <b-form-checkbox v-model="teamQbs" size="sm">Team Quarterbacks</b-form-checkbox>
                                <br /><br /><br />
                                <label for="rbs">Running Backs</label>
                                <b-form-select size="sm" v-model="rbs" :options="rb_options"></b-form-select>
                                <br /><br /><br />
                                <label for="wrs">Wide Receivers</label>
                                <b-form-select size="sm" v-model="wrs" :options="wr_options"></b-form-select>
                                <br /><br /><br />
                                <label for="tes">Tight Ends</label>
                                <b-form-select size="sm" v-model="tes" :options="te_options"></b-form-select>
                                <br /><br /><br />
                                <label for="flex">Flex (RB/WR/TE)</label>
                                <b-form-select size="sm" v-model="flex" :options="flex_options"></b-form-select>
                                <br /><br /><br />
                                <label for="flex">Superflex (QB/RB/WR/TE)</label>
                                <b-form-select size="sm" v-model="superflex" :options="superflex_options"></b-form-select>
                                <br /><br /><br />
                                <label for="k">Kickers</label>
                                <b-form-select size="sm" v-model="ks" :options="k_options"></b-form-select>
                                <b-form-checkbox v-model="teamKs" size="sm">Team Kickers</b-form-checkbox>
                                <br /><br /><br />
                                <label for="def">Defenses</label>
                                <b-form-select size="sm" v-model="def" :options="def_options"></b-form-select>
                                <br /><br /><br />
                                <label for="bench">Bench</label>
                                <b-form-select size="sm" v-model="bench" :options="bench_options"></b-form-select>
                                <button type="submit" class="btn btn-primary">Update Roster Settings</button>
                                </form>
                            </div>
                        <h2>Rules</h2>
                        <h4>Quarterbacks, Running Backs, Wide Receivers, Tight Ends</h4>
                        <form autocomplete="off" @submit.prevent="updateRules" method="post">
                        <div class="form-group">
                            <label for="rule1">Points per rushing TD</label>
                            <input type="text" id="rule1" class="form-control" placeholder="" v-model="rule1" required>
                        </div>

                        <div class="form-group">
                            <label for="rule2">Points per receiving TD</label>
                            <input type="text" id="rule2" class="form-control" placeholder="" v-model="rule2" required>
                        </div>

                        <div class="form-group">
                            <label for="rule3">Points for player returning kick/punt for TD</label>
                            <input type="text" id="rule3" class="form-control" placeholder="" v-model="rule3" required>
                        </div>

                        <div class="form-group">
                            <label for="rule4">Points for player returning or recovering a fumble for TD</label>
                            <input type="text" id="rule4" class="form-control" placeholder="" v-model="rule4" required>
                        </div>

                        <div class="form-group">
                            <label for="rule5">Points per passing TD</label>
                            <input type="text" id="rule5" class="form-control" placeholder="" v-model="rule5" required>
                        </div>

                        <div class="form-group">
                            <label for="rule5">Points per reception</label>
                            <input type="text" id="rule26" class="form-control" placeholder="" v-model="rule26" required>
                        </div>

                        <div class="form-group">
                            <label for="rule6">Points per rushing or receiving 1pt conversion</label>
                            <input type="text" id="rule6" class="form-control" placeholder="" v-model="rule6" required>
                        </div>

                        <div class="form-group">
                            <label for="rule7">Points per rushing or receiving 2pt conversion</label>
                            <input type="text" id="rule7" class="form-control" placeholder="" v-model="rule7" required>
                        </div>

                        <div class="form-group">
                            <label for="rule8">Points per rushing or receiving 3pt conversion</label>
                            <input type="text" id="rule8" class="form-control" placeholder="" v-model="rule8" required>
                        </div>

                        <div class="form-group">
                            <label for="rule9">Points per passing 1pt conversion</label>
                            <input type="text" id="rule9" class="form-control" placeholder="" v-model="rule9" required>
                        </div>

                        <div class="form-group">
                            <label for="rule10">Points per passing 2pt conversion</label>
                            <input type="text" id="rule10" class="form-control" placeholder="" v-model="rule10" required>
                        </div>

                        <div class="form-group">
                            <label for="rule11">Points per passing 3pt conversion</label>
                            <input type="text" id="rule11" class="form-control" placeholder="" v-model="rule11" required>
                        </div>

                        <div class="form-group">
                            <label for="rule12">Points per 10 yards rushing</label>
                            <input type="text" id="rule12" class="form-control" placeholder="" v-model="rule12" required>
                        </div>

                        <div class="form-group">
                            <label for="rule13">Points per 10 yards receiving</label>
                            <input type="text" id="rule13" class="form-control" placeholder="" v-model="rule13" required>
                        </div>

                        <div class="form-group">
                            <label for="rule14">Points per 25 yards passing</label>
                            <input type="text" id="rule14" class="form-control" placeholder="" v-model="rule14" required>
                        </div>

                        <div class="form-group">
                            <label for="rule15">Points per intercepted pass</label>
                            <input type="text" id="rule15" class="form-control" placeholder="" v-model="rule15" required>
                        </div>

                        <div class="form-group">
                            <label for="rule16">Points per fumble</label>
                            <input type="text" id="rule16" class="form-control" placeholder="" v-model="rule16" required>
                        </div>
                        <h4>Kickers</h4>
                        <div class="form-group">
                            <label for="rule17">Points per 50+ yard FG made</label>
                            <input type="text" id="rule17" class="form-control" placeholder="" v-model="rule17" required>
                        </div>
                        <div class="form-group">
                            <label for="rule18">Points per 40-49 yard FG made</label>
                            <input type="text" id="rule18" class="form-control" placeholder="" v-model="rule18" required>
                        </div>
                        <div class="form-group">
                            <label for="rule19">Points per 1-39 yard FG made</label>
                            <input type="text" id="rule19" class="form-control" placeholder="" v-model="rule19" required>
                        </div>


                        <h4>Defense/Special Teams</h4>
                        <div class="form-group">
                            <label for="rule20">Points per defensive or special teams TD</label>
                            <input type="text" id="rule20" class="form-control" placeholder="" v-model="rule20" required>
                        </div>
                        <div class="form-group">
                            <label for="rule21">Points per interception</label>
                            <input type="text" id="rule21" class="form-control" placeholder="" v-model="rule21" required>
                        </div>
                        <div class="form-group">
                            <label for="rule22">Points per fumble recovery</label>
                            <input type="text" id="rule22" class="form-control" placeholder="" v-model="rule22" required>
                        </div>
                        <div class="form-group">
                            <label for="rule23">Points per blocked punt or field goal</label>
                            <input type="text" id="rule23" class="form-control" placeholder="" v-model="rule23" required>
                        </div>
                        <div class="form-group">
                            <label for="rule24">Points per safety</label>
                            <input type="text" id="rule24" class="form-control" placeholder="" v-model="rule24" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">Points per sack</label>
                            <input type="text" id="rule25" class="form-control" placeholder="" v-model="rule25" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">0 Points Allowed</label>
                            <input type="text" id="rule27" class="form-control" placeholder="" v-model="rule27" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">1-6 Points Allowed</label>
                            <input type="text" id="rule28" class="form-control" placeholder="" v-model="rule28" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">7-13 Points Allowed</label>
                            <input type="text" id="rule29" class="form-control" placeholder="" v-model="rule29" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">14-20 Points Allowed:</label>
                            <input type="text" id="rule30" class="form-control" placeholder="" v-model="rule30" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">21-27 Points Allowed</label>
                            <input type="text" id="rule31" class="form-control" placeholder="" v-model="rule31" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">28-34 Points Allowed</label>
                            <input type="text" id="rule32" class="form-control" placeholder="" v-model="rule32" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">35-41 Points Allowed</label>
                            <input type="text" id="rule33" class="form-control" placeholder="" v-model="rule33" required>
                        </div>
                        <div class="form-group">
                            <label for="rule25">42+ Points Allowed</label>
                            <input type="text" id="rule34" class="form-control" placeholder="" v-model="rule34" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Rules</button>
                        </form>
                        </b-card-text>
                    </b-tab>
                </b-tabs>
            </b-card>
        </div>
        <b-modal id="waiver-modal" hide-footer>
            <template v-slot:modal-title>
                If this waiver claim goes through, which player would you like to drop?
            </template>
            <b-table
                id="waiver-players-table"
                :items="rosters[myteam.id]"
                :fields="waiverTableFields"
                striped 
                hover
            >
                <template v-slot:cell(actions)="data">
                    <div>
                        <b-button @click="selectDropPlayer($event, data.item.player_id)">
                            Select
                        </b-button>
                    </div>
                </template>
            </b-table>
            <b-button class="mt-3" block @click="$bvModal.hide('waiver-modal')">Cancel</b-button>
            
        </b-modal>
        <b-modal id="freeagent-modal" hide-footer>
            <template v-slot:modal-title>
                Which player would you like to drop?
            </template>
            <b-table
                id="waiver-players-table"
                :items="rosters[myteam.id]"
                :fields="waiverTableFields"
                striped 
                hover
            >
                <template v-slot:cell(actions)="data">
                    <div>
                        <b-button @click="selectDropForFreeAgent($event, data.item.player_id)">
                            Select
                        </b-button>
                    </div>
                </template>
            </b-table>
            <b-button class="mt-3" block @click="$bvModal.hide('waiver-modal')">Cancel</b-button>
            
        </b-modal>
        <b-modal id="toomany-modal" hide-footer no-close-on-esc no-close-on-backdrop hide-header-close>
            <template v-slot:modal-title>
                You have too many players on your roster. Choose one to drop.
            </template>
            <b-table
                id="waiver-players-table"
                :items="rosters[myteam.id]"
                :fields="waiverTableFields"
                striped 
                hover
            >
                <template v-slot:cell(actions)="data">
                    <div>
                        <b-button @click="dropPlayer(data.item.player_id)">
                            Drop
                        </b-button>
                    </div>
                </template>
            </b-table>
            
            
        </b-modal>
        <b-modal id="trade-modal" hide-footer>
            <template v-slot:modal-title>
                Trade Proposal
            </template>
            <strong>{{ trade_team1name }} gets:</strong>
            
            <b-form-checkbox-group
                id="checkbox-group-1"
                v-model="trade_team1_selections"
                :options="trade_team1roster"
                text-field="player_name"
                value-field="player_id"
                name="flavour-1"
                stacked
            ></b-form-checkbox-group>

            
            <strong>{{ trade_team2name }} gets:</strong>
            <b-form-checkbox-group
                id="checkbox-group-2"
                v-model="trade_team2_selections"
                :options="trade_team2roster"
                text-field="player_name"
                value-field="player_id"
                name="flavour-2"
                stacked
            ></b-form-checkbox-group>
            <b-button class="mt-3" block @click="finalizeTrade()">Propose Trade</b-button>
            
        </b-modal>
        <b-modal id="player-card-modal" size="xl" hide-footer>
            <template v-slot:modal-title>
                {{playerCardItem.name}}</br>
                {{playerCardItem.position}} - {{playerCardItem.team}}
            </template>
            <table class="table b-table table-striped table-hover">
                <tr>
                    <th>Week</th>
                    <th>Points</th>
                    <template v-if="playerCardItem.position != 'K' && playerCardItem.position != 'DEF'">
                        <th>Passing Yards</th>
                        <th>Passing TD</th>
                        <th>Interceptions</th>
                        <th>Receptions</th>
                        <th>Rec Yards</th>
                        <th>Rec TD</th>
                        <th>Rush Yards</th>
                        <th>Rush TD</th>
                        <th>Fumbles</th>
                    </template>
                    <template v-if="playerCardItem.position == 'K'">
                        <th>1-39</th>
                        <th>40-49</th>
                        <th>50+</th>
                    </template>
                    <template v-if="playerCardItem.position == 'DEF'">
                        <th>TD</th>
                        <th>Interceptions</th>
                        <th>Fumbles Recovered</th>
                        <th>Blocked Punt/Field Goal</th>
                        <th>Safety</th>
                        <th>Sack</th>
                    </template>
                </tr>
                <tr v-for="week in playerCardItem.weeks">
                    <td>{{week.weeknum}}</td>
                    <td>{{ week.points }}</td>
                    <template v-if="playerCardItem.position != 'K' && playerCardItem.position != 'DEF'">
                        <td>{{ week.passingYards }}</td>
                        <td>{{ week.passingTD }}</td>
                        <td>{{ week.interceptions }}</td>
                        <td>{{ week.receptions }}</td>
                        <td>{{ week.recyards }}</td>
                        <td>{{ week.recTD }}</td>
                        <td>{{ week.rushyards}}</td>
                        <td>{{ week.rushTD }}</td>
                        <td>{{ week.fumbles }}</td>
                    </template>
                    <template v-if="playerCardItem.position == 'K'">
                        <td>{{ week.shortkick}}</td>
                        <td>{{ week.medkick }}</td>
                        <td>{{ week.longkick }}</td>
                    </template>
                    <template v-if="playerCardItem.position == 'DEF'">
                        <td>{{ week.dstTD }}</td>
                        <td>{{ week.dstint }}</td>
                        <td>{{ week.fumblesrec }}</td>
                        <td>{{ week.blockedkick }}</td>
                        <td>{{ week.safety }}</td>
                        <td>{{ week.sack }}</td>
                    </template>
                </tr>
            </table>
        </b-modal>
        <b-modal id="matchup-modal" hide-footer>
            <template v-slot:modal-title>
                {{ matchup_home_name }} vs. {{ matchup_away_name }}
            </template>
            
            <table class="table b-table table-striped table-hover">
                <tr>
                    <td colspan="2"><strong>{{ matchup_home_name }}</strong></td>
                    <td></td>
                    <td colspan="2"><strong>{{ matchup_away_name }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>{{ matchup_home_total.toFixed(2) }}</strong></td>
                    <td></td>
                    <td colspan="2"><strong>{{ matchup_away_total.toFixed(2) }}</strong></td>
                </tr>
                
                <tr v-for="(n, index) in qbs">
                    <td>{{ matchup_home_qb_starters[index]? matchup_home_qb_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_qb_starters[index]? matchup_home_qb_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_qb_starters[index]? matchup_home_qb_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_home_qb_starters[index]? matchup_home_qb_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_home_qb_starters[index]? matchup_home_qb_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_home_qb_starters[index]? matchup_home_qb_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_qb_starters[index]">
                            <div v-for="(n, index) in matchup_home_qb_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_qb_starters[index] && (!matchup_home_qb_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_qb_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_qb_starters[index]? matchup_home_qb_starters[index].score: "" }}</td>
                    <td><center>QB</center></td>
                    <td>{{ matchup_away_qb_starters[index]? matchup_away_qb_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_qb_starters[index]? matchup_away_qb_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_qb_starters[index]? matchup_away_qb_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_away_qb_starters[index]? matchup_away_qb_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_away_qb_starters[index]? matchup_away_qb_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_away_qb_starters[index]? matchup_away_qb_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_away_qb_starters[index]? matchup_away_qb_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_qb_starters[index]">

                            <div v-for="(n, index) in matchup_away_qb_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_qb_starters[index] && (!matchup_away_qb_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_qb_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr v-for="(n, index) in rbs">
                    <td>{{ matchup_home_rb_starters[index]? matchup_home_rb_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_rb_starters[index]? matchup_home_rb_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_rb_starters[index]? matchup_home_rb_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_home_rb_starters[index]? matchup_home_rb_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_home_rb_starters[index]? matchup_home_rb_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_home_rb_starters[index]? matchup_home_rb_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_rb_starters[index]">
                            <div v-for="(n, index) in matchup_home_rb_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_rb_starters[index] && (!matchup_home_rb_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_rb_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_rb_starters[index]? matchup_home_rb_starters[index].score: "" }}</td>
                    <td><center>RB</center></td>
                    <td>{{ matchup_away_rb_starters[index]? matchup_away_rb_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_rb_starters[index]? matchup_away_rb_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_rb_starters[index]? matchup_away_rb_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_away_rb_starters[index]? matchup_away_rb_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_away_rb_starters[index]? matchup_away_rb_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_away_rb_starters[index]? matchup_away_rb_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_away_rb_starters[index]? matchup_away_rb_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_rb_starters[index]">
                            <div v-for="(n, index) in matchup_away_rb_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_rb_starters[index] && (!matchup_away_rb_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_rb_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr v-for="(n, index) in wrs">
                    <td>{{ matchup_home_wr_starters[index]? matchup_home_wr_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_wr_starters[index]? matchup_home_wr_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_wr_starters[index]? matchup_home_wr_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_home_wr_starters[index]? matchup_home_wr_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_home_wr_starters[index]? matchup_home_wr_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_home_wr_starters[index]? matchup_home_wr_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_wr_starters[index]">
                            <div v-for="(n, index) in matchup_home_wr_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_wr_starters[index] && (!matchup_home_wr_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_wr_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_wr_starters[index]? matchup_home_wr_starters[index].score: "" }}</td>
                    <td><center>WR</center></td>
                    <td>{{ matchup_away_wr_starters[index]? matchup_away_wr_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_wr_starters[index]? matchup_away_wr_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_wr_starters[index]? matchup_away_wr_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_away_wr_starters[index]? matchup_away_wr_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_away_wr_starters[index]? matchup_away_wr_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_away_wr_starters[index]? matchup_away_wr_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_away_wr_starters[index]? matchup_away_wr_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_wr_starters[index]">
                            <div v-for="(n, index) in matchup_away_wr_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_wr_starters[index] && (!matchup_away_wr_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_wr_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr v-for="(n, index) in tes">
                    <td>{{ matchup_home_te_starters[index]? matchup_home_te_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_te_starters[index]? matchup_home_te_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_te_starters[index]? matchup_home_te_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_home_te_starters[index]? matchup_home_te_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_home_te_starters[index]? matchup_home_te_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_home_te_starters[index]? matchup_home_te_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_te_starters[index]">
                            <div v-for="(n, index) in matchup_home_te_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_te_starters[index] && (!matchup_home_te_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_te_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_te_starters[index]? matchup_home_te_starters[index].score: "" }}</td>
                    <td><center>TE</center></td>
                    <td>{{ matchup_away_te_starters[index]? matchup_away_te_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_te_starters[index]? matchup_away_te_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_te_starters[index]? matchup_away_te_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_away_te_starters[index]? matchup_away_te_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_away_te_starters[index]? matchup_away_te_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_away_te_starters[index]? matchup_away_te_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_away_te_starters[index]? matchup_away_te_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_te_starters[index]">
                            <div v-for="(n, index) in matchup_away_te_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_te_starters[index] && (!matchup_away_te_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_te_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr v-for="(n, index) in flex">
                    <td>{{ matchup_home_flex_starters[index]? matchup_home_flex_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_flex_starters[index]? matchup_home_flex_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_flex_starters[index]? matchup_home_flex_starters[index].week2_score: "0" }}</small>
                         <br /><small>Week 3: {{ matchup_home_flex_starters[index]? matchup_home_flex_starters[index].week3_score: "0" }}</small>
                         <br /><small>Week 4: {{ matchup_home_flex_starters[index]? matchup_home_flex_starters[index].week4_score: "0" }}</small>
                         <br /><small>Week 5: {{ matchup_home_flex_starters[index]? matchup_home_flex_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_flex_starters[index]">
                            <div v-for="(n, index) in matchup_home_flex_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_flex_starters[index] && (!matchup_home_flex_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_flex_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_flex_starters[index]? matchup_home_flex_starters[index].score: "" }}</td>
                    <td><center>WR/RB/TE</center></td>
                    <td>{{ matchup_away_flex_starters[index]? matchup_away_flex_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_flex_starters[index]? matchup_away_flex_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_flex_starters[index]? matchup_away_flex_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_away_flex_starters[index]? matchup_away_flex_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_away_flex_starters[index]? matchup_away_flex_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_away_flex_starters[index]? matchup_away_flex_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_away_flex_starters[index]? matchup_away_flex_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_flex_starters[index]">
                            <div v-for="(n, index) in matchup_away_flex_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_flex_starters[index] && (!matchup_away_flex_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_flex_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr v-for="(n, index) in superflex">
                    <td>{{ matchup_home_superflex_starters[index]? matchup_home_superflex_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_superflex_starters[index]? matchup_home_superflex_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_superflex_starters[index]? matchup_home_superflex_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_home_superflex_starters[index]? matchup_home_superflex_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_home_superflex_starters[index]? matchup_home_superflex_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_home_superflex_starters[index]? matchup_home_superflex_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_superflex_starters[index]">
                            <div v-for="(n, index) in matchup_home_superflex_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_superflex_starters[index] && (!matchup_home_superflex_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_superflex_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_superflex_starters[index]? matchup_home_superflex_starters[index].score: "" }}</td>
                    <td><center>QB/WR/RB/TE</center></td>
                    <td>{{ matchup_away_superflex_starters[index]? matchup_away_superflex_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_superflex_starters[index]? matchup_away_superflex_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_superflex_starters[index]? matchup_away_superflex_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_away_superflex_starters[index]? matchup_away_superflex_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_away_superflex_starters[index]? matchup_away_superflex_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_away_superflex_starters[index]? matchup_away_superflex_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_away_superflex_starters[index]? matchup_away_superflex_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_superflex_starters[index]">
                            <div v-for="(n, index) in matchup_away_superflex_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_superflex_starters[index] && (!matchup_away_superflex_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_superflex_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr v-for="(n, index) in ks">
                    <td>{{ matchup_home_k_starters[index]? matchup_home_k_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_k_starters[index]? matchup_home_k_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_k_starters[index]? matchup_home_k_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_home_k_starters[index]? matchup_home_k_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_home_k_starters[index]? matchup_home_k_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_home_k_starters[index]? matchup_home_k_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_k_starters[index]">
                            <div v-for="(n, index) in matchup_home_k_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_k_starters[index] && (!matchup_home_k_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_k_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_k_starters[index]? matchup_home_k_starters[index].score: "" }}</td>
                    <td><center>K</center></td>
                    <td>{{ matchup_away_k_starters[index]? matchup_away_k_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_k_starters[index]? matchup_away_k_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_k_starters[index]? matchup_away_k_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_away_k_starters[index]? matchup_away_k_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_away_k_starters[index]? matchup_away_k_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_away_k_starters[index]? matchup_away_k_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_away_k_starters[index]? matchup_away_k_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_k_starters[index]">
                            <div v-for="(n, index) in matchup_away_k_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_k_starters[index] && (!matchup_away_k_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_k_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr v-for="(n, index) in def">
                    <td>{{ matchup_home_def_starters[index]? matchup_home_def_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_home_def_starters[index]? matchup_home_def_starters[index].week1_score: "0" }}</small>
                        <br /><small>Week 2: {{ matchup_home_def_starters[index]? matchup_home_def_starters[index].week2_score: "0" }}</small>
                        <br /><small>Week 3: {{ matchup_home_def_starters[index]? matchup_home_def_starters[index].week3_score: "0" }}</small>
                        <br /><small>Week 4: {{ matchup_home_def_starters[index]? matchup_home_def_starters[index].week4_score: "0" }}</small>
                        <br /><small>Week 5: {{ matchup_home_def_starters[index]? matchup_home_def_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_home_def_starters[index]">
                            <div v-for="(n, index) in matchup_home_def_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_home_id == myteam.id) || commishTools) && matchup_home_def_starters[index] && (!matchup_home_def_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_home_def_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                    <td>{{ matchup_home_def_starters[index]? matchup_home_def_starters[index].score: "" }}</td>
                    <td><center>DST</center></td>
                    <td>{{ matchup_away_def_starters[index]? matchup_away_def_starters[index].score: "" }}</td>
                    <td>{{ matchup_away_def_starters[index]? matchup_away_def_starters[index].player_name: "empty" }}
                        <br /><small>Week 1: {{ matchup_away_def_starters[index]? matchup_away_def_starters[index].week1_score: "0" }}</small>
                         <br /><small>Week 2: {{ matchup_away_def_starters[index]? matchup_away_def_starters[index].week2_score: "0" }}</small>
                         <br /><small>Week 3: {{ matchup_away_def_starters[index]? matchup_away_def_starters[index].week3_score: "0" }}</small>
                         <br /><small>Week 4: {{ matchup_away_def_starters[index]? matchup_away_def_starters[index].week4_score: "0" }}</small>
                         <br /><small>Week 5: {{ matchup_away_def_starters[index]? matchup_away_def_starters[index].week5_score: "0" }}</small>

                        <div v-if="matchup_away_def_starters[index]">
                            <div v-for="(n, index) in matchup_away_def_starters[index].statline">
                                {{ n }}
                            </div>
                        </div>
                        <div v-if="((matchup_away_id == myteam.id) || commishTools) && matchup_away_def_starters[index] && (!matchup_away_def_starters[index].locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">        
                            <b-button @click="benchPlayer($event, matchup_away_def_starters[index].player_id)">
                                <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                Bench
                            </b-button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <center>Bench</center>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <ul style="list-style-type: none">
                            <li v-for="(n, index) in matchup_home_bench">
                                {{ n.player_name }}
                                <br /><small>Week 1: {{ n.week1_score }}</small>
                                <br /><small>Week 2: {{ n.week2_score }}</small>
                                <br /><small>Week 3: {{ n.week3_score }}</small>
                                <br /><small>Week 4: {{ n.week4_score }}</small>
                                <br /><small>Week 5: {{ n.week5_score }}</small>


                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'QB' && qbs && matchup_home_qb_starters.length < qbs && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'QB')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at QB
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'WR' && wrs && matchup_home_wr_starters.length < wrs && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'WR')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at WR
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'RB' && rbs && matchup_home_rb_starters.length < rbs && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'RB')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at RB
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'TE' && tes && matchup_home_te_starters.length < tes && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'TE')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at TE
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'K' && ks && matchup_home_k_starters.length < ks && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event,n.player_id, 'K')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at K
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'DEF' && def && matchup_home_def_starters.length < def && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'DEF')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at DEF
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="(n.position == 'RB' || n.position == 'WR' || n.position == 'TE' || n.position == 'QB') && superflex && (matchup_home_superflex_starters.length < superflex) && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'SUPERFLEX')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at Superflex
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_home_id == myteam.id) || commishTools">
                                    <div v-if="(n.position == 'RB' || n.position == 'WR' || n.position == 'TE') && flex && (matchup_home_flex_starters.length < flex) && (!n.locked || commishTools) && ((leagueInfo.week == matchup_week) || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'FLEX')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at Flex
                                        </b-button>
                                    </div>
                                </div>

                                <hr />
                            </li>
                        </ul>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <ul style="list-style-type: none">
                            <li v-for="(n, index) in matchup_away_bench">
                                {{ n.player_name }}
                                <br /><small>Week 1: {{ n.week1_score }}</small>
                                <br /><small>Week 2: {{ n.week2_score }}</small>
                                <br /><small>Week 3: {{ n.week3_score }}</small>
                                <br /><small>Week 4: {{ n.week4_score }}</small>
                                <br /><small>Week 5: {{ n.week5_score }}</small>


                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'QB' && qbs && matchup_away_qb_starters.length < qbs && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'QB')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at QB
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'WR' && wrs && matchup_away_wr_starters.length < wrs && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'WR')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at WR
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'RB' && rbs && matchup_away_rb_starters.length < rbs && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'RB')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at RB
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'TE' && tes && matchup_away_te_starters.length < tes && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'TE')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at TE
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'K' && ks && matchup_away_k_starters.length < ks && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'K')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at K
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="n.position == 'DEF' && def && matchup_away_def_starters.length < def && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'DEF')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at DEF
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="(n.position == 'RB' || n.position == 'WR' || n.position == 'TE' || n.position == 'QB') && superflex && matchup_away_superflex_starters.length < superflex && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'SUPERFLEX')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at Superflex
                                        </b-button>
                                    </div>
                                </div>
                                <div v-if="(matchup_away_id == myteam.id) || commishTools">
                                    <div v-if="(n.position == 'RB' || n.position == 'WR' || n.position == 'TE') && flex && matchup_away_flex_starters.length < flex && ((leagueInfo.week == matchup_week) || commishTools) && (!n.locked || commishTools)">
                                        <b-button @click="startPlayer($event, n.player_id, 'FLEX')">
                                            <div v-if="processingAction"><b-spinner small></b-spinner></div>
                                            Start at Flex
                                        </b-button>
                                    </div>
                                </div>
                                <hr />
                            </li>
                        </ul>
                    </td>
                </tr>
            </table>
            
        </b-modal>
    </div>
</template>
<script>
import moment from 'moment'
  export default {
    data() {
      return {
        updateDraftDatetime: '',
        draftTimeDays: '',
        draftTimeHours: '',
        draftTimeMinutes: '',
        draftTimeSeconds: '',
        countdownHours: '',
        countdownMinutes: '',
        countdownSeconds: '',
        currentDrafter: '',
        queuesortfield: 'id',
        leagueId: '',
        leagueInfo: '',
        positionFilter: 'all',
        availabilityFilter: 'all',
        nameFilter: '',
        teamFilter: -1,
        perPage: 10,
        currentPage: 1,
        playerList: [],
        queueItems: [],
        processing: false,
        queueSort: 'queueOrder',
        matchupsSort: 'week',
        lineupsSort: 'name',
        draftOrderSort: 'draftOrder',
        draftpickTime_options: [
            { value: 1, text: '1 minute' },
          { value: 2, text: '2 minutes' },
          { value: 5, text: '5 minutes' },
          { value: 10, text: '10 minutes' },
          { value: 15, text: '15 minutes' },
          { value: 30, text: '30 minutes' },
          { value: 60, text: '1 hour' },
          { value: 120, text: '2 hours' },
          { value: 240, text: '4 hours' },
          { value: 480, text: '8 hours' },
          { value: 960, text: '16 hours' },
          { value: 1440, text: '1 day'},
        ],
        waiver_day_options: [
            { value: "Tuesday", text: "Tuesday"},
            { value: "Wednesday", text: "Wednesday"},
            { value: "Thursday", text: "Thursday"},
            { value: "Friday", text: "Friday"},
            { value: "Saturday", text: "Saturday"},
        ],
        fields: [
            {key: 'actions'},
            {key: 'combinedInfo'},
            {key: 'total_points', label: 'Total Points', sortable: true},
            {key: 'last_3', label: 'Last 3 Weeks Total', sortable: true},
            {key: 'last_week', label: 'Last Week', sortable: true},
            
        ],
        queueFields: [
            {key: 'name'},
            {key: 'position'},
            {key: 'team' },
            {key: 'percent', label: '% Drafted', sortable: true},
            {key: 'adp', label: 'ADP', sortable: true},
            {key: 'actions'}
        ],
        matchupsFields: [
            {key: 'actions'},
            {key: 'home_name'},
            {key: 'away_name'},
            {key: 'home_score'},
            {key: 'away_score'},
            {key: 'week'},
            
        ],
        standingsFields: [
            {key: 'name'},
            {key: 'wins'},
            {key: 'losses'},
            {key: 'ties'},
            {key: 'pf'},
            {key: 'pa'}
            
        ],
        standingsFieldsTotalPoints: [
            {key: 'name'},
            {key: 'pf'},
            
        ],
        lineupsFields: [
            {key: 'actions'},
            {key: 'home_name'},
            {key: 'home_score', label: 'Score'},
            {key: 'week'}
            
        ],
        draftBoardFields: [
            {key: 'team_id'},
            {key: 'pick_order'},
            {key: 'playerName'}
        ],
        draftOrderFields: [
            {key: 'name'},
            {key: 'draftOrder'},
            {key: 'actions'}
        ],
        waiverTableFields: [
            {key: 'player_name'},
            {key: 'actions'}
        ],
        tradeTableFields: [
            {key: 'player_name'},
            {key: 'actions'}
        ],
        leagueWaiverFields: [
            {key: 'player_name', label: 'Pickup'},
            {key: 'drop_player_name', label: 'Drop'},
            {key: 'team_name', label: 'Team'},
            {key: 'updated_at', label: 'Time filed'},
            {key: 'actions'}
        ],
        leagueTransactionFields: [
            {key: 'type'},
            {key: 'team1_id', label: 'Team'},
            {key: 'player_id', label: 'Added'},
            {key: 'drop_player_id', label: 'Dropped'},
            {key: 'team1_selected', label: ''},
            {key: 'team2_selected', label: ''},
            {key: 'created_at', label: 'Timestamp'},
        ],
        matchups: [],
        inviteCode: '',
        preDraft: false,
        postDraft: false,
        qbs: '',
        rbs: '',
        wrs: '',
        tes: '',
        flex: '',
        superflex: '',
        ks: '',
        def: '',
        bench: '',
        teamQbs: false,
        teamKs: false,
        qb_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        rb_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        wr_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        te_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        flex_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        superflex_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        k_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        def_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        bench_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' },
          { value: 6, text: '6' },
          { value: 7, text: '7' },
          { value: 8, text: '8' },
          { value: 9, text: '9' },
          { value: 10, text: '10' },
        ],
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
        rule26: '',
        rule27: '',
        rule28: '',
        rule29: '',
        rule30: '',
        rule31: '',
        rule32: '',
        rule33: '',
        rule34: '',
        teams: [],
        draftPicks: [],
        myteam: '',
        teamNames: [],
        teamDraftOrder: [],
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
        currentClaimId: 0,
        waivers: [],
        trade_team1roster: [],
        trade_team2roster: [],
        trade_team1name: '',
        trade_team2name: '',
        trade_team1_selections: [],
        trade_team2_selections: [],
        trade_team1id: '',
        trade_team2id: '',
        trades: [],
        matchup_home_name: '',
        matchup_away_name: '',
        matchup_home_id: '',
        matchup_away_id: '',
        matchup_week: '',
        matchup_home_bench: [],
        matchup_away_bench: [],
        matchup_home_qb_starters: [],
        matchup_home_rb_starters: [],
        matchup_home_wr_starters: [],
        matchup_home_te_starters: [],
        matchup_home_flex_starters: [],
        matchup_home_superflex_starters: [],
        matchup_home_k_starters: [],
        matchup_home_def_starters: [],
        matchup_home_total: 0,
        matchup_away_qb_starters: [],
        matchup_away_rb_starters: [],
        matchup_away_wr_starters: [],
        matchup_away_te_starters: [],
        matchup_away_flex_starters: [],
        matchup_away_superflex_starters: [],
        matchup_away_k_starters: [],
        matchup_away_def_starters: [],
        matchup_away_total: 0,
        tempItem: [],
        currentFreeAgentId: 0,
        matchup_player_stats: [],
        previousStats: [],
        leagueWaivers: [],
        leagueTransactions: [],
        isCommish: false,
        processingAction: false,
        playerCardItem: {
            name: "",
            position: "",
            team: "",
            weeks: []
        }
      }
    },
    mounted() {
        this.leagueId = this.$route.params.id;
        // getLastUpdate will by default call getLeagueInfo as this.lastUpdate will be undefined here.
        this.getLastUpdate();
        

        
    },
    computed: {
      rows() {
        return this.playerList.length
      },
      lineups() {

          return this.teams
      },
      playersFiltered() {
          var filtered = this.playerList.filter((el) => {
            if (this.teamFilter != -1) { // we've got a filter set
                if (el.fantasyTeam != '' && this.teamFilter == 0) { // the filter is "no team", but this player has a team.
                    return false;
                }
                if (this.teamFilter > 0 && el.fantasyTeamId != this.teamFilter) { // we have a team ID selected and want to match it to the player's fantasy team
                    return false;
                }
            }
            if (this.leagueInfo.teamQbs == true && el.position == "QB") {
                if (el.name == "Dallas" || el.name == "DC" || el.name == "Houston" || el.name == "LA" || el.name == "New York" || el.name == "St Louis" || el.name == "Seattle" || el.name == "Tampa Bay") {
                    //return true;
                } else {
                    return false;
                }
            }
            if (this.leagueInfo.teamQbs == false && el.position == "QB") {
                if (el.name == "Dallas" || el.name == "DC" || el.name == "Houston" || el.name == "LA" || el.name == "New York" || el.name == "St Louis" || el.name == "Seattle" || el.name == "Tampa Bay") {
                    return false;
                }
            }

            if (this.leagueInfo.teamKs == true && el.position == "K") {
                if (el.name == "Dallas" || el.name == "DC" || el.name == "Houston" || el.name == "LA" || el.name == "New York" || el.name == "St Louis" || el.name == "Seattle" || el.name == "Tampa Bay") {
                    //return true;
                } else {
                    return false;
                }
            }
            if (this.leagueInfo.teamKs == false && el.position == "K") {
                if (el.name == "Dallas" || el.name == "DC" || el.name == "Houston" || el.name == "LA" || el.name == "New York" || el.name == "St Louis" || el.name == "Seattle" || el.name == "Tampa Bay") {
                    return false;
                }
            }

            if (this.availabilityFilter == "free") {
                if (el.fantasyTeam != "") {
                    return false;
                }
            }

            if (this.positionFilter == "all") {
                if (el.position == "QB" || el.position == "RB" || el.position == "WR" || el.position == "TE" || el.position == "K") {
                    if (this.nameFilter == '') {
                            return true;
                    } else {
                        var addComma = this.nameFilter.toLowerCase().split(" ");
                        if (addComma.length > 1) {
                            return el.name.toLowerCase().includes(addComma[0]) && el.name.toLowerCase().includes(addComma[1])
                        } else {
                            return el.name.toLowerCase().includes(this.nameFilter.toLowerCase());
                        }
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
          //if (this.positionFilter != "all") this.currentPage = 1
          return filtered;
      },
      itemsFilteredByTeam() {
          var teamName = '';
          for (var teamNames = 0; teamNames < this.teams.length; teamNames++) {
                if (this.teams[teamNames].id == this.team_dropdown) {
                    teamName = this.$data.teams[teamNames].name;
                }
            }

            var filtered = this.playerList.filter((el) => {
                return (teamName == el.fantasyTeam) 
            });
            return filtered;
      }
    },
    filters: {
        useMoment: function (date) {
            return moment.utc(date).fromNow();
        }
    },

    methods: {
        calculatePlayerScore(stats) {
            return +((this.leagueInfo.rule1 * stats.rule1 +
                this.leagueInfo.rule2 * stats.rule2 +
                this.leagueInfo.rule3 * stats.rule3 +
                this.leagueInfo.rule4 * stats.rule4 +
                this.leagueInfo.rule5 * stats.rule5 +
                this.leagueInfo.rule6 * stats.rule6 +
                this.leagueInfo.rule7 * stats.rule7 +
                this.leagueInfo.rule8 * stats.rule8 +
                this.leagueInfo.rule9 * stats.rule9 +
                this.leagueInfo.rule10 * stats.rule10 +
                this.leagueInfo.rule11 * stats.rule11 +
                this.leagueInfo.rule12 * stats.rule12 +
                this.leagueInfo.rule13 * stats.rule13 +
                this.leagueInfo.rule14 * stats.rule14 +
                this.leagueInfo.rule15 * stats.rule15 +
                this.leagueInfo.rule16 * stats.rule16 +
                this.leagueInfo.rule17 * stats.rule17 +
                this.leagueInfo.rule18 * stats.rule18 +
                this.leagueInfo.rule19 * stats.rule19 +
                this.leagueInfo.rule20 * stats.rule20 +
                this.leagueInfo.rule21 * stats.rule21 +
                this.leagueInfo.rule22 * stats.rule22 +
                this.leagueInfo.rule23 * stats.rule23 +
                this.leagueInfo.rule24 * stats.rule24 +
                this.leagueInfo.rule25 * stats.rule25 +
                this.leagueInfo.rule26 * stats.rule26 +
                this.leagueInfo.rule27 * stats.rule27 +
                this.leagueInfo.rule28 * stats.rule28 +
                this.leagueInfo.rule29 * stats.rule29 +
                this.leagueInfo.rule30 * stats.rule30 +
                this.leagueInfo.rule31 * stats.rule31 +
                this.leagueInfo.rule32 * stats.rule32 +
                this.leagueInfo.rule33 * stats.rule33 +
                this.leagueInfo.rule34 * stats.rule34).toFixed(2))
        },
        getPlayerStatline(stats) {
            var statline = []
            if (stats.rule1 && stats.rule1 != 0) {
                statline.push(stats.rule1 + " rushing TD")
            }
            if (stats.rule2 && stats.rule2 != 0) {
                statline.push(stats.rule2 + " receiving TD")
            }
            if (stats.rule3 && stats.rule3 != 0) {
                statline.push(stats.rule3 + " returning kick/punt for TD")
            }
            if (stats.rule4 && stats.rule4 != 0) {
                statline.push(stats.rule4 + " returning or recovering a fumble for TD")
            }
            if (stats.rule5 && stats.rule5 != 0) {
                statline.push(stats.rule5 + " passing TD")
            }
            if (stats.rule6 && stats.rule6 != 0) {
                statline.push(stats.rule6 + " rushing or receiving 1pt conversion")
            }
            if (stats.rule7 && stats.rule7 != 0) {
                statline.push(stats.rule7 + " rushing or receiving 2pt conversion")
            }
            if (stats.rule8 && stats.rule8 != 0) {
                statline.push(stats.rule8 + " rushing or receiving 3pt conversion")
            }
            if (stats.rule9 && stats.rule9 != 0) {
                statline.push(stats.rule9 + " passing 1pt conversion")
            }
            if (stats.rule10 && stats.rule10 != 0) {
                statline.push(stats.rule10 + " passing 2pt conversion")
            }
            if (stats.rule11 && stats.rule11 != 0) {
                statline.push(stats.rule11 + " passing 3pt conversion")
            }
            if (stats.rule12 && stats.rule12 != 0) {
                statline.push(+((stats.rule12*10).toFixed(2)) + " yards rushing")
            }
            if (stats.rule13 && stats.rule13 != 0) {
                statline.push(+((stats.rule13*10).toFixed(2)) + " yards receiving")
            }
            if (stats.rule14 && stats.rule14 != 0) {
                statline.push(+((stats.rule14*25).toFixed(2)) + " yards passing")
            }
            if (stats.rule15 && stats.rule15 != 0) {
                statline.push(stats.rule15 + " intercepted pass")
            }
            if (stats.rule16 && stats.rule16 != 0) {
                statline.push(stats.rule16 + " fumble")
            }
            if (stats.rule17 && stats.rule17 != 0) {
                statline.push(stats.rule17 + " 50+ yard FG made")
            }
            if (stats.rule18 && stats.rule18 != 0) {
                statline.push(stats.rule18 + " 40-49 yard FG made")
            }
            if (stats.rule19 && stats.rule19 != 0) {
                statline.push(stats.rule19 + " 1-39 yard FG made")
            }
            if (stats.rule20 && stats.rule20 != 0) {
                statline.push(stats.rule20 + " defensive or special teams TD")
            }
            if (stats.rule21 && stats.rule21 != 0) {
                statline.push(stats.rule21 + " interception")
            }
            if (stats.rule22 && stats.rule22 != 0) {
                statline.push(stats.rule22 + " fumble recovery")
            }
            if (stats.rule23 && stats.rule23 != 0) {
                statline.push(stats.rule23 + " blocked punt or field goal")
            }
            if (stats.rule24 && stats.rule24 != 0) {
                statline.push(stats.rule24 + " safety")
            }
            if (stats.rule25 && stats.rule25 != 0) {
                statline.push(stats.rule25 + " sack")
            }
            if (stats.rule26 && stats.rule26 != 0) {
                statline.push(stats.rule26 + " reception")
            }
            if (stats.rule27 && stats.rule27 != 0) {
                statline.push(stats.rule27 + " 0 Points Allowed")
            }
            if (stats.rule28 && stats.rule28 != 0) {
                statline.push(stats.rule28 + " 1-6 Points Allowed")
            }
            if (stats.rule29 && stats.rule29 != 0) {
                statline.push(stats.rule29 + " 7-13 Points Allowed")
            }
            if (stats.rule30 && stats.rule30 != 0) {
                statline.push(stats.rule30 + " 14-20 Points Allowed")
            }
            if (stats.rule31 && stats.rule31 != 0) {
                statline.push(stats.rule31 + " 21-27 Points Allowed")
            }
            if (stats.rule32 && stats.rule32 != 0) {
                statline.push(stats.rule32 + " 28-34 Points Allowed")
            }
            if (stats.rule33 && stats.rule33 != 0) {
                statline.push(stats.rule33 + " 35-41 Points Allowed")
            }
            if (stats.rule34 && stats.rule34 != 0) {
                statline.push(stats.rule34 + " 42+ Points Allowed")
            }

            return statline
        },
        
        getWeeklyStats(week) {
            axios.get('players/getWeeklyStats/'+week).then(response => {
                this.matchup_player_stats = response.data;
                this.calculateMatchupScores();
            });
        },
        getPreviousStats() {
            var statsFn = week => axios.get('players/getWeeklyStats/'+week).then(response => {
                    this.previousStats[week] = response.data;
                });
            axios.all([statsFn(1), statsFn(2), statsFn(3), statsFn(4), statsFn(5)]).then(() => {
                    this.playerList.forEach((item) => {
                        item.week1_points = this.getPreviousPlayerScoreFromId(item.id, 1);
                        item.week2_points = this.getPreviousPlayerScoreFromId(item.id, 2);
                        item.week3_points = this.getPreviousPlayerScoreFromId(item.id, 3);
                        item.week4_points = this.getPreviousPlayerScoreFromId(item.id, 4);
                        item.week5_points = this.getPreviousPlayerScoreFromId(item.id, 5);
                        item.total_points = ((item.week1_points || 0) + (item.week2_points || 0) + (item.week3_points || 0) + (item.week4_points || 0) + (item.week5_points || 0)).toFixed(2);
                        item.last_3 = ((item.week3_points || 0) + (item.week4_points || 0) + (item.week5_points || 0)).toFixed(2);
                        item.last_week = ((item.week5_points || 0)).toFixed(2);
                    });
                    this.$forceUpdate(); // setting the week scores like this doesn't notify Vue that there are updates, so we'll force it.
                }                                
            )
        },
        
        updatePlayerEligibility(event, item) {
            if (event.target.value != 0) {
                axios.post('league/updatePlayerEligibility', {
                    leagueId: this.leagueId,
                    player_id: item.id,
                    position: event.target.value
                }).then(response => {
                    this.refreshPlayerList();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        dropPlayer(playerId) {
            this.$bvModal.hide("toomany-modal");
            axios.post('league/dropPlayer', {
                leagueId: this.leagueId,
                player_id: playerId
            }).then(response => {
                this.refreshPlayerList();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        fixMatchups() {
            if(confirm("Are you sure you want to reset the matchups? This can't be undone.")) {
                axios.post('league/fixMatchups', {
                    leagueId: this.leagueId,
                }).then(response => {
                    this.getMatchups();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        cancelTrade(tradeId) {
            axios.post('league/cancelTrade', {
                trade_id: tradeId,
                leagueId: this.leagueId,
            }).then(response => {
                this.refreshTrades();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        
        calculateMatchupScoreForPosition(players) {
            var total = 0;
            for (var lineup = 0; lineup < players.length; lineup++) {
                players[lineup].statline = []

                for (var player_score = 0; player_score < this.matchup_player_stats.length; player_score++) {
                    if (players[lineup].player_id == this.matchup_player_stats[player_score].player_id) {
                        players[lineup].score = parseFloat(this.calculatePlayerScore(this.matchup_player_stats[player_score]))
                        players[lineup].statline = this.getPlayerStatline(this.matchup_player_stats[player_score])
                    }
                }
                if (!isNaN(players[lineup].score)) {
                    total += parseFloat(players[lineup].score);
                }

            }
            return Number(total);
        },
        calculateMatchupScores() {
            this.matchup_home_total = 0;
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_qb_starters);
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_rb_starters);
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_wr_starters);
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_te_starters);
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_flex_starters);
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_superflex_starters);
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_k_starters);
            this.matchup_home_total += this.calculateMatchupScoreForPosition(this.matchup_home_def_starters);

            this.matchup_away_total = 0;
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_qb_starters);
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_rb_starters);
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_wr_starters);
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_te_starters);
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_flex_starters);
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_superflex_starters);
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_k_starters);
            this.matchup_away_total += this.calculateMatchupScoreForPosition(this.matchup_away_def_starters);
        },
        
        dropPlayer(playerId) {
            this.$bvModal.hide("toomany-modal");
            axios.post('league/dropPlayer', {
                leagueId: this.leagueId,
                player_id: playerId
            }).then(response => {
                this.refreshPlayerList();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        cancelTrade(tradeId) {
            axios.post('league/cancelTrade', {
                trade_id: tradeId,
                leagueId: this.leagueId,
            }).then(response => {
                this.refreshTrades();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        acceptTrade(tradeId) {
            axios.post('league/acceptTrade', {
                trade_id: tradeId,
                leagueId: this.leagueId,
            }).then(response => {
                this.refreshTrades();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        cancelWaiver(waiverId) {
            axios.post('league/cancelWaiver', {
                waiver_id: waiverId,
            }).then(response => {
                this.refreshWaivers();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        updateMatchup(event, item, homeOrAway) {
            axios.post('league/updateMatchup', {
                leagueId: this.leagueId,
                matchupId: item.id,
                targetTeam: event,
                homeOrAway: homeOrAway
            }).then(response => {
                this.getMatchups();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        resetPagination() {
            if (this.positionFilter != "all") this.currentPage = 1
        },
        createClaim(event, item) {
            this.currentClaimId = item.id;

            var maxTeamSize = this.leagueInfo.qbs + this.leagueInfo.rbs + this.leagueInfo.wrs + this.leagueInfo.tes + this.leagueInfo.flex + this.leagueInfo.superflex + this.leagueInfo.ks + this.leagueInfo.def + this.leagueInfo.bench;

            var myTeamSize = this.rosters[this.myteam.id].length + this.waivers.length

            if (myTeamSize >= maxTeamSize) {
                this.$bvModal.show("waiver-modal");
            } else {
                axios.post('league/createClaim', {
                    leagueId: this.leagueId,
                    player_id: this.currentClaimId,
                    drop_player_id: 0
                }).then(response => {
                    this.refreshPlayerList();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
            /**/
        },
        addFreeAgent(event, item) {
            this.currentFreeAgentId = item.id;

            var maxTeamSize = this.leagueInfo.qbs + this.leagueInfo.rbs + this.leagueInfo.wrs + this.leagueInfo.tes + this.leagueInfo.flex + this.leagueInfo.superflex + this.leagueInfo.ks + this.leagueInfo.def + this.leagueInfo.bench;

            var myTeamSize = this.rosters[this.myteam.id].length

            if (myTeamSize >= maxTeamSize) {
                this.$bvModal.show("freeagent-modal");
            } else {
                axios.post('league/addFreeAgent', {
                    leagueId: this.leagueId,
                    player_id: this.currentFreeAgentId,
                    
                }).then(response => {
                    this.refreshPlayerList();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
            /**/
        },
        selectDropForFreeAgent(event, item) {
            this.$bvModal.hide('freeagent-modal');
            axios.post('league/addFreeAgent', {
                leagueId: this.leagueId,
                player_id: this.currentFreeAgentId,
                drop_player_id: item
            }).then(response => {
                this.refreshPlayerList();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        finalizeTrade() {
            axios.post('league/createTrade', {
                leagueId: this.leagueId,
                team1: this.trade_team1id,
                team2: this.trade_team2id,
                team1_players: this.trade_team1_selections,
                team2_players: this.trade_team2_selections,
            }).then(response => {
                this.refreshTrades();
                this.$bvModal.hide('trade-modal');
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        createTrade(event, item) {
            this.$bvModal.show("trade-modal");
            this.trade_team1name = this.myteam.name
            this.trade_team2name = item.fantasyTeam
            this.trade_team1id = this.myteam.id
            this.trade_team2id = item.fantasyTeamId
            this.trade_team1_selections = []
            this.trade_team2_selections = []
            this.trade_team1_selections.push(item.id);
            for (var team in this.rosters) {
                if (item.fantasyTeamId == team) {
                    // Load this team into my list
                    this.trade_team1roster = this.rosters[team]
                }
                if (this.myteam.id == team) {
                    this.trade_team2roster = this.rosters[team]
                }
            }
            for (var j = 0; j < this.trade_team1roster.length; j++) {
                this.trade_team1roster[j].player_name = this.getPlayerNameFromId(this.trade_team1roster[j].player_id)
            }
            for (var j = 0; j < this.trade_team2roster.length; j++) {
                this.trade_team2roster[j].player_name = this.getPlayerNameFromId(this.trade_team2roster[j].player_id)
            }

/**/
        },
        startPlayer(event, player_id, position) {
            this.processingAction = true
            axios.post('league/startPlayer', {
                leagueId: this.leagueId,
                team_id: this.getPlayerTeamIdFromId(player_id),
                week: this.tempItem.week,
                player_id: player_id,
                position: position
            }).then(response => {
                this.processingAction = false
                if (this.tempItem.week < this.leagueInfo.week) {
                    this.getLeagueInfo();
                } else {
                    this.getMatchups();
                }
                if (this.leagueInfo.league_type == 1) {
                    this.showMatchup(event, this.tempItem)
                } else {
                    this.showLineup(event, this.tempItem, this.matchup_week)
                }
            }).catch(error => {
                this.processingAction = false
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        benchPlayer(event, player_id) {
            this.processingAction = true
            axios.post('league/benchPlayer', {
                leagueId: this.leagueId,
                team_id: this.getPlayerTeamIdFromId(player_id),
                week: this.tempItem.week,
                player_id: player_id,
            }).then(response => {
                this.processingAction = false
                if (this.tempItem.week < this.leagueInfo.week) {
                    this.getLeagueInfo();
                } else {
                    this.getMatchups();
                }
                if (this.leagueInfo.league_type == 1) {
                    this.showMatchup(event, this.tempItem)
                } else {
                    this.showLineup(event, this.tempItem,this.matchup_week)
                }
            }).catch(error => {
                this.processingAction = false
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        showPlayerCard(item) {
            this.playerCardItem.name  = item.name;
            this.playerCardItem.position = item.position;
            this.playerCardItem.team = item.team;
            this.playerCardItem.weeks = [];
            for(var i = 1; i < this.previousStats.length; i++) {
                var stats = this.previousStats[i].filter(player => player.player_id == item.id)[0];
                if(stats !== undefined && stats != null) {
                    var score = this.calculatePlayerScore(stats);
                    var week = {
                        weeknum: i,
                        points: score,
                        passingYards: stats.rule14 * 25, // stored in the DB as computed value
                        passingTD: stats.rule5,
                        interceptions: stats.rule21,
                        receptions: stats.rule26,
                        recyards: stats.rule13 * 10, // stored in the DB as computed value
                        recTD: stats.rule2, 
                        rushyards: stats.rule12 * 10, // stored in the DB as computed value
                        rushTD: stats.rule1,
                        fumbles: stats.rule16,
                        shortkick: stats.rule19,
                        medkick: stats.rule18,
                        longkick: stats.rule17,
                        dstTD: stats.rule20,
                        dstint: stats.rule15,
                        fumblesrec: stats.rule22,
                        safety: stats.rule24,
                        sack: stats.rule25
                    };
                    this.playerCardItem.weeks.push(week);
                }
                else {
                    var week = {
                        weeknum: i,
                        points: 0,
                        passingYards: 0,
                        passingTD: 0,
                        interceptions: 0,
                        receptions: 0,
                        recyards: 0,
                        recTD: 0,
                        rushyards: 0,
                        rushTD: 0,
                        fumbles: 0,
                        shortkick: 0,
                        medkick: 0,
                        longkick: 0,
                        dstTD: 0,
                        dstint: 0,
                        fumblesrec: 0,
                        safety: 0,
                        sack: 0
                    };
                    this.playerCardItem.weeks.push(week);
                }
            }
            this.$bvModal.show("player-card-modal");
        },
        showMatchup(event, item) {
            
            this.tempItem = item
            
            for (var j = 0; j < this.$data.rosters[item.home_id].length; j++) {
                this.$data.rosters[item.home_id][j].player_name = this.getPlayerNameFromId(this.$data.rosters[item.home_id][j].player_id);
            }
            for (var j = 0; j < this.$data.rosters[item.away_id].length; j++) {
                this.$data.rosters[item.away_id][j].player_name = this.getPlayerNameFromId(this.$data.rosters[item.away_id][j].player_id);
            }
            //this.matchup_home_bench = this.$data.rosters[item.home_id]
            //this.matchup_away_bench = this.$data.rosters[item.away_id]
            for (var j = 0; j < this.$data.rosters[item.home_id].length; j++) {
                this.$data.rosters[item.home_id][j].position = this.getPlayerPositionFromId(this.$data.rosters[item.home_id][j].player_id);
            }
            for (var j = 0; j < this.$data.rosters[item.away_id].length; j++) {
                this.$data.rosters[item.away_id][j].position = this.getPlayerPositionFromId(this.$data.rosters[item.away_id][j].player_id);
            }
            this.matchup_home_bench = []
            this.matchup_away_bench = []
            this.matchup_home_qb_starters = []
            this.matchup_home_rb_starters = []
            this.matchup_home_wr_starters = []
            this.matchup_home_te_starters = []
            this.matchup_home_flex_starters = []
            this.matchup_home_superflex_starters = []
            this.matchup_home_k_starters = []
            this.matchup_home_defense_starters = []
            this.matchup_away_qb_starters = []
            this.matchup_away_rb_starters = []
            this.matchup_away_wr_starters = []
            this.matchup_away_te_starters = []
            this.matchup_away_flex_starters = []
            this.matchup_away_superflex_starters = []
            this.matchup_away_k_starters = []
            this.matchup_away_defense_starters = []
            var that = this;
            
            function getHomeLineup() { 
                return axios.post('league/getLineup', {
                    leagueId: that.leagueId,
                    team_id: item.home_id,
                    week: that.tempItem.week
                }).then(response => {
                    console.log('get lineup');
                    console.log(response.data)
                    that.matchup_home_bench = []
                    
                    that.matchup_home_qb_starters = []
                    that.matchup_home_rb_starters = []
                    that.matchup_home_wr_starters = []
                    that.matchup_home_te_starters = []
                    that.matchup_home_flex_starters = []
                    that.matchup_home_superflex_starters = []
                    that.matchup_home_k_starters = []
                    that.matchup_home_def_starters = []
                    for (var i = 0; i < response.data.length; i++) {
                        if (response.data[i].position == "BENCH") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            response.data[i].position = that.getPlayerPositionFromId(response.data[i].player_id);
                            try {
                                if (response.data[i]) that.matchup_home_bench.push(response.data[i])
                            } catch(err) {
                                console.log(err)
                            }
                        } else if (response.data[i].position == "QB") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_qb_starters.push(response.data[i])
                        } else if (response.data[i].position == "RB") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_rb_starters.push(response.data[i])
                        } else if (response.data[i].position == "WR") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_wr_starters.push(response.data[i])
                        } else if (response.data[i].position == "TE") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_te_starters.push(response.data[i])
                        } else if (response.data[i].position == "FLEX") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_flex_starters.push(response.data[i])
                        } else if (response.data[i].position == "SUPERFLEX") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_superflex_starters.push(response.data[i])
                        } else if (response.data[i].position == "K") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_k_starters.push(response.data[i])
                        } else if (response.data[i].position == "DEF") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_home_def_starters.push(response.data[i])
                        }
                    }
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        that.errors = error.response.data.errors || {};
                    }
                })
            };
            function getAwayLineup() { 
                return axios.post('league/getLineup', {
                    leagueId: that.leagueId,
                    team_id: item.away_id,
                    week: that.tempItem.week
                }).then(response => {
                    console.log('get lineup');
                    console.log(response.data)
                    that.matchup_away_bench = []
                    
                    that.matchup_away_qb_starters = []
                    that.matchup_away_rb_starters = []
                    that.matchup_away_wr_starters = []
                    that.matchup_away_te_starters = []
                    that.matchup_away_flex_starters = []
                    that.matchup_away_superflex_starters = []
                    that.matchup_away_k_starters = []
                    that.matchup_away_def_starters = []
                    for (var i = 0; i < response.data.length; i++) {
                        if (response.data[i].position == "BENCH") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            response.data[i].position = that.getPlayerPositionFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_bench.push(response.data[i])
                        } else if (response.data[i].position == "QB") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_qb_starters.push(response.data[i])
                        } else if (response.data[i].position == "RB") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_rb_starters.push(response.data[i])
                        } else if (response.data[i].position == "WR") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_wr_starters.push(response.data[i])
                        } else if (response.data[i].position == "TE") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_te_starters.push(response.data[i])
                        } else if (response.data[i].position == "FLEX") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_flex_starters.push(response.data[i])
                        } else if (response.data[i].position == "SUPERFLEX") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_superflex_starters.push(response.data[i])
                        } else if (response.data[i].position == "K") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_k_starters.push(response.data[i])
                        } else if (response.data[i].position == "DEF") {
                            response.data[i].player_name = that.getPlayerNameFromId(response.data[i].player_id);
                            if (response.data[i]) that.matchup_away_def_starters.push(response.data[i])
                        }
                    }
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        that.errors = error.response.data.errors || {};
                    }
                })
            };
            axios.all([getHomeLineup(), getAwayLineup()]).then(function() {
                that.getWeeklyStats(that.tempItem.week);
            });

            this.$bvModal.show("matchup-modal");
            this.matchup_home_name = item.home_name
            this.matchup_away_name = item.away_name
            this.matchup_home_id = item.home_id
            this.matchup_away_id = item.away_id
            this.matchup_week = item.week
            console.log(this.rosters)
        },
        showLineup(event, item, week) {
            this.tempItem = item
            this.matchup_home_id = item.home_id
            this.matchup_home_name = item.home_name
            this.matchup_week = week
            for (var j = 0; j < this.$data.rosters[item.home_id].length; j++) {
                this.$data.rosters[item.home_id][j].player_name = this.getPlayerNameFromId(this.$data.rosters[item.home_id][j].player_id);
            }
            for (var j = 0; j < this.$data.rosters[item.home_id].length; j++) {
                this.$data.rosters[item.home_id][j].position = this.getPlayerPositionFromId(this.$data.rosters[item.home_id][j].player_id);
            }

            this.matchup_home_bench = []
            this.matchup_away_bench = []
            this.matchup_home_qb_starters = []
            this.matchup_home_rb_starters = []
            this.matchup_home_wr_starters = []
            this.matchup_home_te_starters = []
            this.matchup_home_flex_starters = []
            this.matchup_home_superflex_starters = []
            this.matchup_home_k_starters = []
            this.matchup_home_defense_starters = []
            this.matchup_away_qb_starters = []
            this.matchup_away_rb_starters = []
            this.matchup_away_wr_starters = []
            this.matchup_away_te_starters = []
            this.matchup_away_flex_starters = []
            this.matchup_away_superflex_starters = []
            this.matchup_away_k_starters = []
            this.matchup_away_defense_starters = []
            axios.post('league/getLineup', {
                leagueId: this.leagueId,
                team_id: item.home_id,
                week: this.matchup_week
            }).then(response => {
                this.matchup_home_bench = []
                
                this.matchup_home_qb_starters = []
                this.matchup_home_rb_starters = []
                this.matchup_home_wr_starters = []
                this.matchup_home_te_starters = []
                this.matchup_home_flex_starters = []
                this.matchup_home_superflex_starters = []
                this.matchup_home_k_starters = []
                this.matchup_home_def_starters = []
                for (var i = 0; i < response.data.length; i++) {
                    if (response.data[i].position == "BENCH") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        response.data[i].position = this.getPlayerPositionFromId(response.data[i].player_id);
                        this.matchup_home_bench.push(response.data[i])
                    } else if (response.data[i].position == "QB") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_qb_starters.push(response.data[i])
                    } else if (response.data[i].position == "RB") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_rb_starters.push(response.data[i])
                    } else if (response.data[i].position == "WR") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_wr_starters.push(response.data[i])
                    } else if (response.data[i].position == "TE") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_te_starters.push(response.data[i])
                    } else if (response.data[i].position == "FLEX") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_flex_starters.push(response.data[i])
                    } else if (response.data[i].position == "SUPERFLEX") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_superflex_starters.push(response.data[i])
                    } else if (response.data[i].position == "K") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_k_starters.push(response.data[i])
                    } else if (response.data[i].position == "DEF") {
                        response.data[i].player_name = this.getPlayerNameFromId(response.data[i].player_id);
                        this.matchup_home_def_starters.push(response.data[i])
                    }
                }
                this.getWeeklyStats(this.matchup_week)
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
            
            

            this.$bvModal.show("matchup-modal");
            
        },
        selectDropPlayer(event, item) {
            this.$bvModal.hide('waiver-modal');
            axios.post('league/createClaim', {
                leagueId: this.leagueId,
                player_id: this.currentClaimId,
                drop_player_id: item
            }).then(response => {
                this.refreshPlayerList();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        getMatchups() {
            axios.post('league/getMatchups', {
                leagueId: this.leagueId,
            }).then(response => {
                this.matchups = response.data;
                for (var i = 0; i < this.matchups.length; i++) {
                    this.matchups[i].home_name = this.getTeamNameFromId(this.matchups[i].home_id);
                    this.matchups[i].away_name = this.getTeamNameFromId(this.matchups[i].away_id);
                }
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        commishUpdatePlayerTeam(event, item) {
            axios.post('league/assignPlayer', {
                leagueId: this.leagueId,
                team_id: event,
                player_id: item.id
            }).then(response => {
                //this.$router.push('/dashboard');
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        leaveLeague() {
            if(confirm("Are you sure you want to leave this league?")) {
                axios.post('league/remove', {
                    leagueId: this.leagueId,
                    team: this.myteam.id
                }).then(response => {
                    this.$router.push('/dashboard');
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        deleteLeague() {
            if(confirm("Are you sure you want to delete this league? This CANNOT be undone.")) {
                axios.post('league/delete', {
                    leagueId: this.leagueId,
                }).then(response => {
                    this.$router.push('/dashboard');
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        removeTeam(event, targetTeam) {
            if(confirm("Are you sure you want to remove this team?")) {
                console.log('confirmed');
                axios.post('league/remove', {
                    leagueId: this.leagueId,
                    team: targetTeam.id
                }).then(response => {
                    //this.$router.push('/dashboard');
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        updateSettings(event) {
            
                axios.post('league/updateSettings', {
                    leagueId: this.leagueId,
                    waiver_day: this.leagueInfo.waiver_day,
                    draftpick_time: this.leagueInfo.draftpick_time,
                }).then(response => {
                    //this.$router.push('/dashboard');
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
        },
        changeLeagueSize() {
                axios.post('league/updateSize', {
                    leagueId: this.leagueId,
                    maxSize: this.leagueInfo.maxSize
                }).then(response => {
                    //this.$router.push('/dashboard');
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
        },
        changeLeagueType() {
                axios.post('league/updateType', {
                    leagueId: this.leagueId,
                    league_type: this.leagueInfo.league_type
                }).then(response => {
                    //this.$router.push('/dashboard');
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
        },
        changePlayoffLength() {
            axios.post('league/updatePlayoffLength', {
                    leagueId: this.leagueId,
                    playoff_length: this.leagueInfo.playoff_length
                }).then(response => {
                    //this.getLeagueInfo();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
        },
        updateDraft() {
            if (confirm("Warning: updating the draft time will revert any picks already made. Are you sure you want to do this?")) {
                this.errors = {};
                axios.post('league/updateDraft', {
                    leagueId: this.leagueId,
                    datetime: this.updateDraftDatetime
                }).then(response => {
                    //
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        updateRoster() {
            this.errors = {};
            axios.post('league/updateRoster', {
                leagueId: this.leagueId,
                qbs: this.qbs,
                rbs: this.rbs,
                wrs: this.wrs,
                tes: this.tes,
                flex: this.flex,
                superflex: this.superflex,
                ks: this.ks,
                def: this.def,
                bench: this.bench,
                teamQbs: this.teamQbs,
                teamKs: this.teamKs
            }).then(response => {
                //
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        updateName() {
            axios.post('league/updateName', {
                leagueId: this.leagueId,
                newName: this.myteam.name,
            }).then(response => {
                this.getLeagueInfo();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        updateRules() {
            this.errors = {};
            axios.post('league/updateRules', {
                leagueId: this.leagueId,
                rule1: this.rule1,
                rule2: this.rule2,
                rule3: this.rule3,
                rule4: this.rule4,
                rule5: this.rule5,
                rule6: this.rule6,
                rule7: this.rule7,
                rule8: this.rule8,
                rule9: this.rule9,
                rule10: this.rule10,
                rule11: this.rule11,
                rule12: this.rule12,
                rule13: this.rule13,
                rule14: this.rule14,
                rule15: this.rule15,
                rule16: this.rule16,
                rule17: this.rule17,
                rule18: this.rule18,
                rule19: this.rule19,
                rule20: this.rule20,
                rule21: this.rule21,
                rule22: this.rule22,
                rule23: this.rule23,
                rule24: this.rule24,
                rule25: this.rule25,
                rule26: this.rule26,
                rule27: this.rule27,
                rule28: this.rule28,
                rule29: this.rule29,
                rule30: this.rule30,
                rule31: this.rule31,
                rule32: this.rule32,
                rule33: this.rule33,
                rule34: this.rule34,
            }).then(response => {
                //
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        updateDraftBoard() {
            if(this.postDraft) { 
                return;
            }
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
                    if (this.teams[i]) {
                        return this.teams[i].name;
                    }
                }
            }
        },
        getPlayerTeamIdFromId(player_id) {
            let keys = Object.keys(this.$data.rosters)
            for (var teamRoster = 0; teamRoster < keys.length; teamRoster++) {
                for (var rosterPlayer = 0; rosterPlayer < this.$data.rosters[keys[teamRoster]].length; rosterPlayer++) {
                    if (this.$data.rosters[keys[teamRoster]][rosterPlayer].player_id == player_id) {
                        return keys[teamRoster];
                    }
                }
            }
        },
        getPlayerPositionFromId(player_id) {
            return this.playerList[player_id].position;
        },
        getPlayerNameFromId(player_id) {
            return this.playerList[player_id].name + " (" + this.getPlayerPositionFromId(player_id) + " - " +this.playerList[player_id].team+ ")"
        },
        getPreviousPlayerScoreFromId(player_id, week) {
            if(this.previousStats[week] === undefined) {
                return "";
            }
            for (var prevStat = 0; prevStat < this.previousStats[week].length; prevStat++) {
                if (this.previousStats[week][prevStat].player_id == player_id) {
                    return this.calculatePlayerScore(this.previousStats[week][prevStat])
                }
            }
        },
        getLeagueInfo() {
            // get league info
            axios.get('league/info/'+this.leagueId).then(response => {
                this.inviteCode = response.data.invite_code;
                this.leagueInfo = response.data;
                // get team info
                axios.get('league/teams/'+this.leagueId).then(response => {
                    this.teams = response.data;

                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
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
                this.refreshPlayerList();
                
                this.getMatchups();

        
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
            if (this.draftTimeSeconds < 0) {
                this.draftTimeSeconds = 0;
            }

            var countdownDiff = moment.utc(this.leagueInfo.draft_nextpick).diff(moment());
            diffDuration = moment.duration(countdownDiff);
            this.countdownHours = diffDuration.hours();
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
            this.superflex = this.leagueInfo.superflex;
            this.ks = this.leagueInfo.ks;
            this.def = this.leagueInfo.def;
            this.bench = this.leagueInfo.bench;
            if (this.leagueInfo.teamQbs == 1) {
                this.teamQbs = true;
            }
            if (this.leagueInfo.teamKs == 1) {
                this.teamKs = true;
            }
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
            this.rule26 = this.leagueInfo.rule26;
            this.rule27 = this.leagueInfo.rule27;
            this.rule28 = this.leagueInfo.rule28;
            this.rule29 = this.leagueInfo.rule29;
            this.rule30 = this.leagueInfo.rule30;
            this.rule31 = this.leagueInfo.rule31;
            this.rule32 = this.leagueInfo.rule32;
            this.rule33 = this.leagueInfo.rule33;
            this.rule34 = this.leagueInfo.rule34;
            this.teamNames = [];
            this.teamNames.push({
                value: 0,
                text: "No Team"
            });
            for (var i = 0; i < this.leagueInfo.teams.length; i++) {
                this.teamNames.push({
                    value: this.leagueInfo.teams[i].id,
                    text: this.leagueInfo.teams[i].name
                })
                
            }
            this.teamDraftOrder = [];
            for (var i = 0; i < this.leagueInfo.teams.length; i++) {
                this.teamDraftOrder.push({
                    id: this.leagueInfo.teams[i].id,
                    name: this.leagueInfo.teams[i].name,
                    draftOrder: this.leagueInfo.teams[i].draft_order
                })
                
            }
            this.$data.processing = false;
                axios.get('league/myuserid/').then(response => {

                    if (response.data == this.leagueInfo.commish_id) {
                        this.isCommish = true;
                    }


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
                        this.isCommish = true;
                    }


                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
                
        }).catch(error => {
            console.log(error);
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            }
        });
        },
        updateTimer() {
            if(this.postDraft){
                return;
            }
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
                this.countdownHours = diffDuration.hours();
                this.countdownMinutes = diffDuration.minutes();
                this.countdownSeconds = diffDuration.seconds();
                if (this.countdownSeconds < 0) {
                    this.countdownSeconds = 0;
                    //setTimeout(() => { this.getLeagueInfo(); }, 10000);
                }
                var that = this;
            }
            setTimeout(() => { this.updateTimer(); }, 1000);
        },
        getLastUpdate() {
            if(this.lastUpdate === 0) { // on first call just call getLeagueInfo to populate the page quickly
                this.getLeagueInfo();
            }
            axios.get('league/getLastUpdate/'+this.$data.leagueId).then(response => {
                if (this.$data.lastUpdate != response.data) {
                    if(this.lastUpdate !== 0) { // if it's the first call skip getLeageInfo - we just did it.
                        this.getLeagueInfo();
                    }
                    this.$data.lastUpdate = response.data;
                }
                if (this.leagueInfo.draft_status < 2) {
                    setTimeout(() => { this.getLastUpdate(); }, 10000);
                } else {
                    setTimeout(() => { this.getLastUpdate(); }, 60000);
                }
            }).catch(error => {
                console.log(error);
                /*if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }*/
                setTimeout(() => { this.getLastUpdate(); }, 10000);
                //this.$router.push('/login');
            });
            
        },
        addToQueue(event, player) {
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
                this.$data.processing = false;
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
        moveUpDraftOrder(event, player) {
            axios.post('league/moveUpDraftOrder', {
                team_id: player.id,
                leagueId: this.$data.leagueId,
            }).then(response => {
                
                this.getLeagueInfo();
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
            });
        },
        moveDownDraftOrder(event, player) {
            axios.post('league/moveDownDraftOrder', {
                team_id: player.id,
                leagueId: this.$data.leagueId,
            }).then(response => {
                
                this.getLeagueInfo();
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
        removeFromQueue(event, player) {
            
            axios.post('player/removeFromQueue', {
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
        grantWaiver(event, waiver) {
            if (this.isCommish) {
                axios.post('league/processWaiver', {
                    leagueId: this.$data.leagueId,
                    waiver_id: waiver.id
                }).then(response => {
                    this.getLeagueInfo();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.$data.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        denyWaiver(event, waiver) {
            if (this.isCommish) {
                axios.post('league/denyWaiver', {
                    leagueId: this.$data.leagueId,
                    waiver_id: waiver.id
                }).then(response => {
                    this.getLeagueInfo();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.$data.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        updateWaiverStatus() {
            if (this.isCommish) {
                axios.post('league/updateWaiverStatus', {
                    leagueId: this.$data.leagueId,
                    status: this.leagueInfo.waiver_status
                }).then(response => {
                    this.getLeagueInfo();
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.$data.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        getTransactions() {
            axios.post('league/getTransactions', {
                leagueId: this.$data.leagueId,
            }).then(response => {
                this.leagueTransactions = response.data;
                
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });
        },
        getCommishWaivers() {
            if (this.isCommish) {
                axios.post('league/getWaivers', {
                    leagueId: this.$data.leagueId,
                }).then(response => {
                    this.leagueWaivers = response.data;
                    for (var j = 0; j < this.leagueWaivers.length; j++) {
                        this.leagueWaivers[j].player_name = this.getPlayerNameFromId(this.leagueWaivers[j].player_id);
                        if (this.leagueWaivers[j].drop_player_id) {
                            this.leagueWaivers[j].drop_player_name = this.getPlayerNameFromId(this.leagueWaivers[j].drop_player_id);
                        }
                        if (this.leagueWaivers[j].team_id) {
                            this.leagueWaivers[j].team_name = this.getTeamNameFromId(this.leagueWaivers[j].team_id);
                        }
                    }
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.$data.errors = error.response.data.errors || {};
                    }
                });
            }
        },
        refreshWaivers() {
            axios.post('player/getwaivers', {
                leagueId: this.$data.leagueId,
            }).then(response => {
                this.$data.waivers = response.data;
                for (var j = 0; j < this.$data.waivers.length; j++) {
                    this.$data.waivers[j].player_name = this.getPlayerNameFromId(this.$data.waivers[j].player_id);
                    this.$data.waivers[j].drop_player_name = this.getPlayerNameFromId(this.$data.waivers[j].drop_player_id);
                }
                for (var i = 0; i < response.data.length; i++) {
                    this.playerList[response.data[i].player_id].waiverMade = true;
                }
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });
        },
        refreshTrades() {
            axios.post('league/getTrades', {
                leagueId: this.$data.leagueId,
            }).then(response => {
                this.trades = response.data;
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });
        },
        refreshQueueItems() {
            axios.post('player/getqueue', {
                leagueId: this.$data.leagueId,
                userAuth: this.$auth.token()
            }).then(response => {
                this.$data.queueItems = []
                for(var i = 0; i < response.data.length; i++) {
                    this.playerList[response.data[i].player_id].draftQueue = true;
                    this.playerList[response.data[i].player_id].queueOrder = response.data[i].queue_order;
                    this.queueItems.push(this.playerList[response.data[i]]);
                }
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });
        },
        assignEligibilities() {
            axios.post('league/getEligibilities', {
                leagueId: this.$data.leagueId,
            }).then(response => {
                response.data.forEach((elig) => {
                    if (this.playerList[elig.player_id]) {
                        this.playerList[elig.player_id].position = elig.position;
                    }
                });
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });
        },
        refreshPlayerList() {
            // get player list
            var getPlayers = () => axios.get('players/xfl').then(response => {
                var items = []
                response.data.forEach((item) => {
                    if (!item.draftQueue) item.draftQueue = false
                    item.fantasyTeam = ''
                    items[item.id] = item;
                    this.playerList = items;
                })
                this.assignEligibilities();
            }).catch(error => {
                console.log(error);
                /*if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                }*/
            });

            var getRosters = () => axios.post('league/getrosters', {
                leagueId: this.$data.leagueId,
            }).then(response => {
                this.$data.rosters = response.data;
            }).catch(error => {
                console.log(error);
                if (error.response.status === 422) {
                    this.$data.errors = error.response.data.errors || {};
                }
            });

            axios.all([getPlayers(), getRosters()]).then(() => {
                this.assignTeams();
                this.refreshWaivers();
                this.getCommishWaivers();
                this.getTransactions();
                this.refreshTrades();
                this.getPreviousStats();
                if(!this.postDraft) {
                    this.updateDraftBoard()
                    this.refreshQueueItems();
                    this.$data.draftedQBs = 0;
                    this.$data.draftedWRs = 0;
                    this.$data.draftedRBs = 0;
                    this.$data.draftedTEs = 0;
                    this.$data.draftedFlex = 0;
                    this.$data.draftedKs = 0;
                    this.$data.draftedDef = 0;
                    this.$data.draftedBench = 0;

                    var maxTeamSize = this.leagueInfo.qbs + this.leagueInfo.rbs + this.leagueInfo.wrs + this.leagueInfo.tes + this.leagueInfo.flex + this.leagueInfo.superflex + this.leagueInfo.ks + this.leagueInfo.def + this.leagueInfo.bench;

                    var myTeamSize = this.rosters[this.myteam.id].length

                    if (myTeamSize > maxTeamSize) {
                        this.$bvModal.show("toomany-modal");
                    }
                    
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
                }
            });
        },
        assignTeams() {
            let keys = Object.keys(this.$data.rosters)
            console.log(this.$data.teams)
            for (var teamRoster = 0; teamRoster < keys.length; teamRoster++) {
                for (var rosterPlayer = 0; rosterPlayer < this.$data.rosters[keys[teamRoster]].length; rosterPlayer++) {
                    for (var teamNames = 0; teamNames < this.$data.teams.length; teamNames++) {
                        if (this.$data.teams[teamNames].id == this.$data.rosters[keys[teamRoster]][rosterPlayer].team_id) {
                            if (this.playerList[this.$data.rosters[keys[teamRoster]][rosterPlayer].player_id]) {
                                this.playerList[this.$data.rosters[keys[teamRoster]][rosterPlayer].player_id].fantasyTeam = this.$data.teams[teamNames].name;
                                this.playerList[this.$data.rosters[keys[teamRoster]][rosterPlayer].player_id].fantasyTeamId = this.$data.teams[teamNames].id;
                            }
                        }
                    }
                }
            }
        }
    }
  }
</script>