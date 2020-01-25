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
                            <form autocomplete="off" @submit.prevent="updateRoster" method="post">
                                <b-button @click="leaveLeague()">
                                    Leave League
                                </b-button>
                            </form>
                            <strong>Invite link: </strong>https://altfantasysports.com/league/invite/{{ inviteCode }}/
                            
                            <h2>Current Teams</h2>
                            <ul id="team-name-list">
                                <li v-for="(team,key) in teamNames">
                                    {{ team.text }}
                                </li>
                            </ul>

                            <h2>My Team Name</h2>
                                <input type="text" id="myteam" class="form-control" placeholder="" v-model="myteam.name" required>
                                <b-button variant="success" @click="updateName($event)">
                                            Update Name
                                        </b-button>

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
                                    <div v-if="!postDraft && data.item.draftQueue == true && data.item.fantasyTeam == ''">
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
                    <b-tab title="Commish Tools" v-if="commishTools">
                        <b-card-text>
                            <h1>Draft Settings</h1>
                            <form autocomplete="off" @submit.prevent="updateDraft" method="post">
                                <b-alert variant="danger" show>Warning: Updating your draft date and/or time will reset the draft. Any picks already made will be reverted. This cannot be undone.</b-alert>

                                <div class="form-group">
                                    <label for="drafttime">Update Draft Date/Time <small><em>Times shown are in your local timezone. Other members of your league will have the time converted to their local timezone.</em></small></label>
                                    <datetime v-model="updateDraftDatetime" type="datetime" zone="local" value-zone="UTC" :use12-hour=true title="Draft Time" name="draft_datetime"></datetime>
                                </div>
                                <button type="submit" class="btn btn-primary">Update</button>
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
                            <div class="form-group">
                                <h2>Rosters</h2>
                                <form autocomplete="off" @submit.prevent="updateRoster" method="post">
                                <label for="qbs">Quarterbacks</label>
                                <b-form-select v-model="qbs" :options="qb_options" size="sm"></b-form-select>
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
                                <label for="k">Kickers</label>
                                <b-form-select size="sm" v-model="ks" :options="k_options"></b-form-select>
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
                        <button type="submit" class="btn btn-primary">Update Rules</button>
                        </form>
                        </b-card-text>
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
        updateDraftDatetime: '',
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
        draftOrderSort: 'draftOrder',
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
        draftOrderFields: [
            {key: 'name'},
            {key: 'draftOrder'},
            {key: 'actions'}
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
          if (this.positionFilter != "all") this.currentPage = 1
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
        leaveLeague() {
            if(confirm("Are you sure you want to leave this league?")) {
                console.log("confirmed "+this.myteam.id);
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
                ks: this.ks,
                def: this.def,
                bench: this.bench
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
                //
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
            this.rule26 = this.leagueInfo.rule26;
            this.teamNames = [];
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
                    if (!item.draftQueue) item.draftQueue = false
                    item.fantasyTeam = ''
                    this.$data.items.push(item)
                })
                // get queue items
                
                this.assignTeams();
                this.updateDraftBoard()
                this.refreshQueueItems();
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