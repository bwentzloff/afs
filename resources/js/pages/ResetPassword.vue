<template>
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-10">
        <div class="card card-default">
          <div class="card-header">Reset</div>
          <div class="card-body">
            <div class="alert alert-danger" v-if="has_error && !success">
              <p v-if="error == 'login_error'">Validation Errors.</p>
              <p v-else>Error, unable to connect with these credentials.</p>
            </div>
            <div class="alert alert-danger" v-if="dontMatch">
              <p v-if="error == 'login_error'">Validation Errors.</p>
              <p v-else>Error, your passwords don't match in the password and password confirmation fields.</p>
            </div>
            <form autocomplete="off" method="post" @submit.prevent="resetPassword" >
                <div class="form-group" v-bind:class="{ 'has-error': has_error && errors.password }">
                    <label for="password">Password</label>
                    <input type="password" id="password" class="form-control" v-model="password">
                    <span class="help-block" v-if="has_error && errors.password">{{ errors.password }}</span>
                </div>
                <div class="form-group" v-bind:class="{ 'has-error': has_error && errors.password }">
                    <label for="password_confirmation">Password confirmation</label>
                    <input type="password" id="password_confirmation" class="form-control" v-model="password_confirmation">
                </div>

              
              <b-button class="btn btn-primary" @click="resetPassword()">Reset Password</b-button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  export default {
    data() {
      return {
        email: null,
        success: false,
        has_error: false,
        error: '',
        password: '',
        password_confirmation: '',
        dontMatch: false
      }
    },
    mounted() {
      //
    },
    methods: {
      resetPassword() {
          if (this.password != this.password_confirmation) {
              this.dontMatch = true
          } else {
              this.dontMatch = false
                // get the redirect object
                var redirect = this.$auth.redirect()
                var app = this

                axios.post('user/resetPassword', {
                    password: this.password,
                    email: this.$route.query.email,
                    token: this.$route.params.token
                }).then(response => {
                    this.items = response.data;
                    this.success = true;
                    this.$router.push('/login')
                }).catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
            });
          }  
      }
    }
  }
</script>