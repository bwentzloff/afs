<template>
    <div class="container">
        <div class="card card-default">
            <div class="card-header">Create League</div>
            <div class="card-body">
                <form autocomplete="off" @submit.prevent="create" method="post">
                    <div class="form-group">
                        <label for="name">League Name</label>
                        <input type="text" id="text" class="form-control" placeholder="" v-model="name" required>
                    </div>
                    <div class="form-group">
                        <label for="draftpickTime">League Type</label>
                        <b-form-select v-model="league_type" :options="league_type_options"></b-form-select>
                    </div>
                    <div class="form-group">
                        <label for="name">My Team's Name</label>
                        <input type="text" id="text" class="form-control" placeholder="" v-model="teamname" required>
                    </div>
                    <div class="form-group">
                        <label for="maxSize">Maximum Size</label>
                        <select v-model="maxSize">
                            <option>2</option>
                            <option>4</option>
                            <option>6</option>
                            <option>8</option>
                            <option>10</option>
                            <option>12</option>
                            <option>14</option>
                            <option>16</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="waiver_day">Day waivers are processed</label>
                        <select v-model="waiver_day">
                            <option>Tuesday</option>
                            <option>Wednesday</option>
                            <option>Thursday</option>
                            <option>Friday</option>
                            <option>Saturday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="drafttime">Draft Date/Time</label>
                        <datetime v-model="date" type="datetime" zone="local" value-zone="UTC" :use12-hour=true title="Draft Time" name="draft_datetime"></datetime>
                        <b-alert show>Protip: A lot of people last year chose Super Bowl Sunday as their AAF draft day. It was really fun. Draft spring football in the morning. Watch the end of fall football in the evening. Good times.</b-alert>
                    </div>
                    <div class="form-group">
                        <label for="draftpickTime">Time allowed for each draft pick</label>
                        <b-form-select v-model="draftpickTime" :options="draftpickTime_options"></b-form-select>
                    </div>
                    <div class="form-group">
                        <label for="draftpickTime">Weeks of playoffs</label>
                        <b-form-select v-model="playoff_length" :options="playoff_length_options"></b-form-select>
                    </div>
                    <div class="form-group">
                        <h2>Rosters</h2>
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
                    </div>
                    <div class="form-group">
                        <h2>Rules</h2>
                        <h4>Quarterbacks, Running Backs, Wide Receivers, Tight Ends</h4>
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
                    </div>
                    <button type="submit" class="btn btn-primary">Create League</button>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
import { Datetime } from 'vue-datetime';

  export default {
    data() {
      return {
        name: null,
        teamname: '',
        maxSize: 8,
        date: null,
        waiver_day: "Wednesday",
        playoff_length: 2,
        playoff_length_options: [
          { value: 1, text: '1 week' },
          { value: 2, text: '2 weeks' },
          { value: 3, text: '3 weeks' },
        ],
        league_type: 1,
        league_type_options: [
          { value: 1, text: 'Head to Head' },
          { value: 2, text: 'Total Points' },
        ],
        draftpickTime: 5,
        draftpickTime_options: [
          { value: 2, text: '2 minutes' },
          { value: 5, text: '5 minutes' },
          { value: 10, text: '10 minutes' },
          { value: 15, text: '15 minutes' },
          { value: 30, text: '30 minutes' },
          { value: 60, text: '1 hour' },
          { value: 120, text: '2 hours' },
          { value: 240, text: '4 hours' },
        ],
        qbs: 1,
        qb_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        rbs: 2,
        rb_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        wrs: 2,
        wr_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        tes: 1,
        te_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        flex: 1,
        flex_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        ks: 1,
        k_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        def: 1,
        def_options: [
          { value: 0, text: '0' },
          { value: 1, text: '1' },
          { value: 2, text: '2' },
          { value: 3, text: '3' },
          { value: 4, text: '4' },
          { value: 5, text: '5' }
        ],
        bench: 4,
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
        rule1: 6,
        rule2: 6,
        rule3: 6,
        rule4: 6,
        rule5: 4,
        rule6: 1,
        rule7: 2,
        rule8: 3,
        rule9: 1,
        rule10: 2,
        rule11: 3,
        rule12: 1,
        rule13: 1,
        rule14: 1,
        rule15: -2,
        rule16: -2,
        rule17: 5,
        rule18: 4,
        rule19: 3,
        rule20: 3,
        rule21: 2,
        rule22: 2,
        rule23: 2,
        rule24: 2,
        rule25: 1,
      }
    },
    mounted() {
        //
    },
    components: {
      datetime: Datetime
    },
    methods: {
      create() {
        this.errors = {};
        axios.post('league/create', {
            name: this.name,
            teamname: this.teamname,
            maxSize: this.maxSize,
            draft_datetime: this.date,
            waiver_day: this.waiver_day,
            playoff_length: this.playoff_length,
            league_type: this.league_type,
            qbs: this.qbs,
            rbs: this.rbs,
            wrs: this.wrs,
            tes: this.tes,
            flex: this.flex,
            ks: this.ks,
            def: this.def,
            bench: this.bench,
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
            draftpickTime: this.draftpickTime,
            userAuth: this.$auth.token()
        }).then(response => {
            this.$router.push('/dashboard');
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