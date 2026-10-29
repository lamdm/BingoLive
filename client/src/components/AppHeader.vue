<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

async function logout() {
  await auth.logout();
  router.push('/');
}
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-violet-100/80 bg-cream-50/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
      <RouterLink to="/" class="font-display text-xl font-bold text-party-ink">
        Bingo<span class="text-party-purple">Live</span>
      </RouterLink>
      <nav class="ml-4 hidden items-center gap-4 text-sm font-semibold text-party-muted md:flex">
        <RouterLink class="hover:text-party-purple" to="/">Trang chủ</RouterLink>
        <RouterLink class="hover:text-party-purple" to="/rooms">Phòng chơi</RouterLink>
        <RouterLink class="hover:text-party-purple" to="/leaderboard">Xếp hạng</RouterLink>
        <RouterLink class="hover:text-party-purple" to="/how-to-play">Hướng dẫn</RouterLink>
        <RouterLink v-if="auth.isHost" class="hover:text-party-purple" to="/host">Host</RouterLink>
        <RouterLink v-if="auth.isAdmin" class="hover:text-party-purple" to="/admin">Admin</RouterLink>
      </nav>
      <div class="ml-auto flex items-center gap-2">
        <template v-if="auth.user">
          <div class="hidden rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-party-purple sm:block">
            {{ auth.user.points }} điểm ảo
          </div>
          <RouterLink
            to="/profile"
            class="rounded-full bg-white px-3 py-1.5 text-sm font-bold text-party-ink shadow-sm ring-1 ring-violet-100"
          >
            {{ auth.user.nickname }}
          </RouterLink>
          <button class="rounded-full px-3 py-1.5 text-sm font-semibold text-party-muted" @click="logout">
            Đăng xuất
          </button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="rounded-full px-3 py-1.5 text-sm font-bold text-party-purple ring-1 ring-violet-200">
            Đăng nhập
          </RouterLink>
          <RouterLink to="/register" class="rounded-full bg-party-purple px-3 py-1.5 text-sm font-bold text-white shadow-soft">
            Đăng ký
          </RouterLink>
        </template>
      </div>
    </div>
  </header>
</template>
