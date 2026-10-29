<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const email = ref('player@bingolive.local');
const password = ref('password123');

async function submit() {
  await auth.login(email.value, password.value);
  router.push(String(route.query.redirect || '/'));
}
</script>

<template>
  <div class="mx-auto max-w-md rounded-3xl bg-white p-6 shadow-soft ring-1 ring-violet-100">
    <h1 class="font-display text-2xl font-bold">Đăng nhập</h1>
    <p class="mt-1 text-sm text-party-muted">player@ / host@ / admin@bingolive.local · password123</p>
    <form class="mt-4 space-y-3" @submit.prevent="submit">
      <input v-model="email" type="email" required class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" />
      <input v-model="password" type="password" required class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" />
      <p v-if="auth.error" class="text-sm font-bold text-rose-600">{{ auth.error }}</p>
      <button class="w-full rounded-full bg-party-purple py-3 text-sm font-extrabold text-white" type="submit">Đăng nhập</button>
    </form>
    <p class="mt-3 text-center text-sm"><RouterLink to="/register" class="font-bold text-party-purple">Đăng ký</RouterLink></p>
  </div>
</template>
