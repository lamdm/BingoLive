<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
const auth = useAuthStore();
const router = useRouter();
if (!auth.user) router.push('/login');
</script>

<template>
  <div v-if="auth.user" class="mx-auto max-w-lg rounded-3xl bg-white p-6 shadow-soft ring-1 ring-violet-100">
    <h1 class="font-display text-2xl font-bold">{{ auth.user.nickname }}</h1>
    <p class="text-sm text-party-muted">{{ auth.user.email }} · {{ auth.user.role }}</p>
    <div class="mt-4 rounded-2xl bg-cream-100 p-4">
      <div class="text-xs font-bold text-party-muted">Điểm ảo</div>
      <div class="font-display text-2xl font-bold text-party-purple">{{ auth.user.points }}</div>
    </div>
  </div>
</template>
