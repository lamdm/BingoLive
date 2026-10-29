<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');

async function submit() {
  await auth.register(name.value, email.value, password.value, password_confirmation.value);
  router.push('/');
}
</script>

<template>
  <div class="mx-auto max-w-md rounded-3xl bg-white p-6 shadow-soft ring-1 ring-violet-100">
    <h1 class="font-display text-2xl font-bold">Đăng ký</h1>
    <form class="mt-4 space-y-3" @submit.prevent="submit">
      <input v-model="name" required class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" placeholder="Tên" />
      <input v-model="email" type="email" required class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" placeholder="Email" />
      <input v-model="password" type="password" required class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" placeholder="Mật khẩu" />
      <input v-model="password_confirmation" type="password" required class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" placeholder="Nhập lại mật khẩu" />
      <p v-if="auth.error" class="text-sm font-bold text-rose-600">{{ auth.error }}</p>
      <button class="w-full rounded-full bg-party-purple py-3 text-sm font-extrabold text-white" type="submit">Tạo tài khoản</button>
    </form>
    <p class="mt-3 text-center text-sm"><RouterLink to="/login" class="font-bold text-party-purple">Đăng nhập</RouterLink></p>
  </div>
</template>
