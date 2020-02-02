require('./bootstrap');

import 'es6-promise/auto'
import axios from 'axios'
import Vue from 'vue'
import VueAuth from '@websanova/vue-auth'
import VueAxios from 'vue-axios'
import VueRouter from 'vue-router'
import Index from './Index'
import auth from './auth'
import router from './router'
import BootstrapVue from 'bootstrap-vue'
import { Datetime } from 'vue-datetime'
import 'vue-datetime/dist/vue-datetime.css'
import VueGtag from "vue-gtag";

// Set Vue globally
window.Vue = Vue;

// Set Vue router
Vue.router = router;
Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(Datetime);
Vue.use(require('vue-moment'));

Vue.use(VueGtag, {
  config: { id: "UA-133236218-1" }
});

Vue.component('datetime', Datetime);

// Set Vue authentication
Vue.use(VueAxios, axios);
// axios.defaults.baseURL = process.env.MIX_APP_URL;
axios.defaults.baseURL = `/api/v1`;
Vue.use(VueAuth, auth);

// Load Index
Vue.component('index', Index);
const app = new Vue({
  el: '#app',
  router
});