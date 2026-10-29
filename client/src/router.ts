import { createRouter, createWebHistory } from 'vue-router';
import MainLayout from './layouts/MainLayout.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: MainLayout,
      children: [
        { path: '', name: 'home', component: () => import('./pages/HomePage.vue') },
        { path: 'rooms', name: 'rooms', component: () => import('./pages/RoomsPage.vue') },
        { path: 'rooms/:id', name: 'room', component: () => import('./pages/RoomDetailPage.vue') },
        { path: 'rooms/:id/live', name: 'live', component: () => import('./pages/LivePage.vue') },
        { path: 'rooms/:id/result', name: 'result', component: () => import('./pages/ResultPage.vue') },
        { path: 'host', name: 'host', component: () => import('./pages/HostPage.vue') },
        { path: 'admin', name: 'admin', component: () => import('./pages/AdminPage.vue') },
        { path: 'login', name: 'login', component: () => import('./pages/LoginPage.vue') },
        { path: 'register', name: 'register', component: () => import('./pages/RegisterPage.vue') },
        { path: 'profile', name: 'profile', component: () => import('./pages/ProfilePage.vue') },
        { path: 'how-to-play', name: 'howto', component: () => import('./pages/HowToPlayPage.vue') },
        { path: 'leaderboard', name: 'leaderboard', component: () => import('./pages/LeaderboardPage.vue') },
      ],
    },
  ],
  scrollBehavior: () => ({ top: 0 }),
});

export default router;
