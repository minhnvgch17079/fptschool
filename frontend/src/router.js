
import Vue from 'vue'
import Router from 'vue-router'
import auth from "@/auth/authService";

import firebase from 'firebase/app'
import 'firebase/auth'

Vue.use(Router)

const router = new Router({
    mode: 'history',
    base: process.env.BASE_URL,
    scrollBehavior () {
        return { x: 0, y: 0 }
    },
    routes: [
        {
          path: '/login',
          name: 'Login',
          component: () => import('@/views/pages/login/Login.vue'),
          meta: {
            rule: 'editor',
          }
        },
        {
          path: '/logout',
          name: 'Logout',
          component: () => import('@/views/pages/login/Login.vue'),
          meta: {
            rule: 'editor',
          }
        },
        {
            path: '',
            component: () => import('./layouts/main/Main.vue'),
            children: [
              {
                path: '/',
                redirect: '/dashboard/analytics'
              },
              {
                path: '/dashboard/analytics',
                name: 'dashboard-analytics',
                component: () => import('./views/DashboardAnalytics.vue'),
                meta: {
                  rule: 'editor',
                }
              },
            ]
        },
        {
            path: '',
            component: () => import('@/layouts/full-page/FullPage.vue'),
            children: [
                {
                    path: '/pages/error-404',
                    name: 'page-error-404',
                    component: () => import('@/views/pages/Error404.vue'),
                    meta: {
                        rule: 'editor'
                    }
                },
                {
                    path: '/pages/error-500',
                    name: 'page-error-500',
                    component: () => import('@/views/pages/Error500.vue'),
                    meta: {
                        rule: 'editor'
                    }
                }
            ]
        },
        {
            path: '*',
            redirect: '/pages/error-404'
        }
    ],
})

router.afterEach(() => {
  // Remove initial loading
  const appLoading = document.getElementById('loading-bg')
    if (appLoading) {
        appLoading.style.display = "none";
    }
})

router.beforeEach((to, from, next) => {
    firebase.auth().onAuthStateChanged(() => {

        // get firebase current user
        const firebaseCurrentUser = firebase.auth().currentUser

        // If auth required, check login. If login fails redirect to login page
        if(to.meta.authRequired) {
          if (!(auth.isAuthenticated() || firebaseCurrentUser)) {
            router.push({ path: '/pages/login', query: { to: to.path } })
          }
        }

        return next()
    });

});

export default router
