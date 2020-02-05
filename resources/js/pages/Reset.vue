<template>
  <div class="container">
    <div class="row justify-content-md-center">
      <div class="col-10">
        <div class="card card-default">
          <div class="card-header">Login</div>
          <div class="card-body">
            <div class="alert alert-danger" v-if="has_error && !success">
              <p v-if="error == 'login_error'">Validation Errors.</p>
              <p v-else>Error, unable to connect with these credentials.</p>
            </div>
            <div class="alert alert-warning" v-if="!success">
              <p>Enter your email address and you will be sent a link with further instructions to reset your password</p>
            </div>
            <div class="alert" v-if="success">
              <p>The email has been sent. You should receive it within a few minutes. If you don't see it, make sure to check your spam folder.</p>
            </div>
            <form autocomplete="off" method="post" @submit.prevent="emailLink" >
              <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" class="form-control" placeholder="user@example.com" v-model="email" required>
              </div>
              
              <b-button class="btn btn-primary" @click="emailLink()">Send Link</b-button>
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
        error: ''
      }
    },
    mounted() {
      //
    },
    methods: {
      emailLink() {
        // get the redirect object
        var redirect = this.$auth.redirect()
        var app = this

        axios.post('user/sendEmailLink', {
            userAuth: this.$auth.token(),
            email: this.$data.email
        }).then(response => {
            this.items = response.data;
            this.success = true;
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