<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { api } from '../lib/api';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const room = ref<any>(null);
const error = ref('');
const joining = ref(false);

onMounted(async () => {
  const data = await api<{ room: any }>(`/rooms/${route.params.id}`);
  room.value = data.room;
});

async function join() {
  if (!auth.isLoggedIn) {
    router.push({ path: '/login', query: { redirect: route.fullPath } });
    return;
  }
  joining.value = true;
  error.value = '';
  try {
    const data = await api<{ user: any }>(`/rooms/${room.value.id}/join`, { method: 'POST' });
    auth.setUser(data.user);
    router.push(`/rooms/${room.value.id}/live`);
  } catch (e: any) {
    error.value = e.message;
  } finally {
    joining.value = false;
  }
}
</script>

<template>
  <div v-if="room" class="grid gap-6 lg:grid-cols-5">
    <section class="rounded-[28px] bg-white p-6 shadow-soft ring-1 ring-violet-100 lg:col-span-3">
      <div class="mb-3 flex flex-wrap gap-2">
        <span class="rounded-full bg-rose-500 px-3 py-1 text-xs font-extrabold text-white">{{ room.status }}</span>
        <span
          class="rounded-full px-3 py-1 text-xs font-extrabold"
          :class="room.entryType==='FREE'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-800'"
        >
          {{ room.entryType === 'FREE' ? 'Miễn phí' : `Phí ${room.entryFee} điểm` }}
        </span>
      </div>
      <h1 class="font-display text-3xl font-bold">{{ room.name }}</h1>
      <p class="mt-2 text-party-muted">{{ room.description }}</p>
    </section>
    <aside class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-violet-100 lg:col-span-2">
      <h2 class="font-display text-lg font-bold">Thể lệ</h2>
      <ul class="mt-3 space-y-2 text-sm text-party-muted">
        <li>Bingo 75 · 5×5 · FREE giữa</li>
        <li>Thắng: {{ (room.bingoRule || []).join(' / ') }}</li>
        <li class="font-bold text-emerald-700">Không tiền thật</li>
      </ul>
      <p v-if="error" class="mt-2 text-sm font-bold text-rose-600">{{ error }}</p>
      <button
        class="mt-4 w-full rounded-full bg-party-purple py-3 text-sm font-extrabold text-white disabled:opacity-50"
        :disabled="joining || room.status==='ENDED'"
        @click="join"
      >
        Tham gia & nhận thẻ
      </button>
      <RouterLink :to="`/rooms/${room.id}/live`" class="mt-2 block text-center text-sm font-bold text-party-purple">
        Chỉ xem live
      </RouterLink>
    </aside>
  </div>
</template>
