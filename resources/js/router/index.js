import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: () => import('../components/Login.vue') },
    { path: '/dashboard', component: () => import('../components/Dashboard.vue') }
  ]
})

export default router