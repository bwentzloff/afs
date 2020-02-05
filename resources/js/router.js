import VueRouter from 'vue-router'
// Pages
import Home from './pages/Home'
import About from './pages/About'
import Register from './pages/Register'
import Login from './pages/Login'
import Reset from './pages/Reset'
import Dashboard from './pages/user/Dashboard'
import AdminDashboard from './pages/admin/Dashboard'
import CreateLeague from './pages/CreateLeague'
import AcceptInvite from './pages/AcceptInvite'
import ViewLeague from './pages/ViewLeague'
import ResetPassword from './pages/ResetPassword'

// Routes
const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: {
      auth: undefined
    }
  },
  {
    path: '/about',
    name: 'about',
    component: About,
    meta: {
      auth: undefined
    }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: {
      auth: false
    }
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: {
      auth: false
    }
  },
  {
    path: '/reset',
    name: 'reset',
    component: Reset,
    meta: {
      auth: false
    }
  },
  {
    path: '/password/reset/:token',
    name: 'resetPassword',
    component: ResetPassword,
    meta: {
      auth: false
    }
  },
  // USER ROUTES
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      auth: true
    }
  },
  // LEAGUE ROUTES
  {
    path: '/league/create',
    name: 'createLeague',
    component: CreateLeague,
    meta: {
      auth: true
    }
  },
  {
    path: '/league/invite/:code',
    name: 'acceptInvite',
    component: AcceptInvite,
    meta: {
      auth: true
    }
  },
  {
    path: '/league/view/:id',
    name: 'viewLeague',
    component: ViewLeague,
    meta: {
      auth: true
    }
  },
  // ADMIN ROUTES
  {
    path: '/admin',
    name: 'admin.dashboard',
    component: AdminDashboard,
    meta: {
      auth: {
        roles: -1, 
        redirect: {
          name: 'login'
        }, 
        forbiddenRedirect: '/403'
      }
    }
  },
];

const router = new VueRouter({
  history: true,
  mode: 'history',
  base: process.env.MIX_APP_URL,
  routes,
});
export default router