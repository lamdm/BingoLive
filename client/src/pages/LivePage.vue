<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { api } from '../lib/api';
import { getEcho } from '../lib/echo';
import { useAuthStore } from '../stores/auth';
import BingoCard from '../components/BingoCard.vue';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const roomId = String(route.params.id);

const room = ref<any>(null);
const round = ref<any>(null);
const card = ref<any>(null);
const drawn = ref<number[]>([]);
const currentNumber = ref<number | null>(null);
const messages = ref<any[]>([]);
const participants = ref<any[]>([]);
const stream = ref<any>(null);
const chatInput = ref('');
const claimMsg = ref('');
const connected = ref(false);
const error = ref('');

const canClaim = computed(() => !!card.value && !!auth.user && round.value?.status === 'PLAYING');

async function load() {
  const data = await api<any>(`/rooms/${roomId}/live`);
  room.value = data.room;
  round.value = data.round;
  card.value = data.card;
  messages.value = data.messages || [];
  participants.value = data.participants || [];
  stream.value = data.stream;
  drawn.value = data.round?.drawn || [];
  currentNumber.value = data.round?.currentNumber ?? null;
}

async function ensureJoined() {
  if (!auth.user) return;
  try {
    const data = await api<any>(`/rooms/${roomId}/join`, { method: 'POST' });
    auth.setUser(data.user);
    card.value = data.card;
  } catch (e: any) {
    error.value = e.message;
  }
}

onMounted(async () => {
  await load();
  if (auth.user && !card.value) await ensureJoined();

  const echo = getEcho();
  if (!echo) return;
  connected.value = true;
  echo.channel(`room.${roomId}`)
    .listen('.game.number-drawn', (e: any) => {
      currentNumber.value = e.number;
      drawn.value = e.drawn || [];
    })
    .listen('.chat.message', (e: any) => {
      messages.value.push(e.message || e);
    })
    .listen('.game.winner', () => {
      router.push(`/rooms/${roomId}/result`);
    })
    .listen('.room.state', (e: any) => {
      if (e.state?.round) {
        round.value = e.state.round;
        drawn.value = e.state.round.drawn || drawn.value;
        currentNumber.value = e.state.round.currentNumber;
      }
      if (e.state?.room) room.value = e.state.room;
    });
});

onUnmounted(() => {
  getEcho()?.leave(`room.${roomId}`);
});

async function sendChat() {
  if (!chatInput.value.trim()) return;
  const content = chatInput.value;
  chatInput.value = '';
  const data = await api<any>(`/rooms/${roomId}/chat`, {
    method: 'POST',
    body: JSON.stringify({ content }),
  });
  if (data.message) messages.value.push(data.message);
}

async function claim() {
  if (!card.value) return;
  const data = await api<any>(`/rooms/${roomId}/claim`, {
    method: 'POST',
    body: JSON.stringify({ cardId: card.value.id }),
  });
  claimMsg.value = data.message;
  if (data.valid) router.push(`/rooms/${roomId}/result`);
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex flex-wrap gap-2 text-xs font-bold">
      <span class="rounded-full px-2 py-1" :class="connected?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-800'">
        {{ connected ? 'Realtime OK' : 'Echo offline' }}
      </span>
      <span
        v-if="room"
        class="rounded-full px-2 py-1"
        :class="room.entryType==='FREE'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-800'"
      >
        {{ room.entryType === 'FREE' ? 'Miễn phí' : `Phí ${room.entryFee} điểm` }}
      </span>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
      <div class="space-y-4 lg:col-span-2">
        <div class="relative aspect-video overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-violet-800 to-sky-800 text-white">
          <span class="absolute left-3 top-3 rounded-full bg-rose-500 px-2 py-0.5 text-[11px] font-extrabold">LIVE</span>
          <div class="flex h-full flex-col items-center justify-center p-6 text-center">
            <div class="font-bold text-violet-200">{{ room?.host?.nickname }}</div>
            <p class="mt-2 text-sm text-violet-100">{{ stream?.message }}</p>
          </div>
        </div>

        <div class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
          <div class="flex flex-wrap items-center gap-4">
            <div v-if="currentNumber" :key="currentNumber" class="ball ball-lg">{{ currentNumber }}</div>
            <div v-else class="flex h-24 w-24 items-center justify-center rounded-full bg-cream-100 text-xs font-bold text-party-muted">
              Chờ quay
            </div>
            <div>
              <div class="font-display text-lg font-bold">{{ room?.name }}</div>
              <div class="text-sm text-party-muted">{{ round?.status || '—' }} · {{ drawn.length }}/75</div>
              <div class="mt-2 flex flex-wrap gap-1">
                <span v-for="n in drawn.slice(-12)" :key="n" class="ball h-8 w-8 text-xs">{{ n }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
          <div class="mb-3 flex justify-between">
            <h2 class="font-display text-lg font-bold">Thẻ của bạn</h2>
            <RouterLink v-if="!auth.user" to="/login" class="text-sm font-bold text-party-purple">Đăng nhập</RouterLink>
            <button v-else-if="!card" class="text-sm font-bold text-party-purple" @click="ensureJoined">Nhận thẻ</button>
          </div>
          <p v-if="error" class="mb-2 text-sm font-bold text-rose-600">{{ error }}</p>
          <BingoCard v-if="card" :numbers="card.numbers" :drawn="drawn" />
          <button
            class="mt-4 w-full rounded-full bg-party-purple py-3 text-sm font-extrabold text-white disabled:opacity-50"
            :disabled="!canClaim"
            @click="claim"
          >
            Báo Bingo
          </button>
          <p v-if="claimMsg" class="mt-2 text-center text-sm font-bold text-party-purple">{{ claimMsg }}</p>
        </div>
      </div>

      <aside class="space-y-4">
        <div class="flex h-[420px] flex-col rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
          <h3 class="font-display font-bold">Chat</h3>
          <div class="mt-2 flex-1 space-y-2 overflow-y-auto text-sm">
            <div v-for="m in messages" :key="m.id" class="rounded-xl bg-cream-100 px-3 py-2">
              <div class="text-[11px] font-bold text-party-purple">{{ m.nickname }}</div>
              <div>{{ m.content }}</div>
            </div>
          </div>
          <form class="mt-2 flex gap-2" @submit.prevent="sendChat">
            <input
              v-model="chatInput"
              class="flex-1 rounded-full border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100"
              :disabled="!auth.user"
              placeholder="Nhắn tin…"
            />
            <button class="rounded-full bg-party-purple px-3 py-2 text-xs font-extrabold text-white" type="submit">Gửi</button>
          </form>
        </div>
        <div class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
          <h3 class="font-display font-bold">Người chơi</h3>
          <ul class="mt-2 space-y-2 text-sm">
            <li v-for="p in participants" :key="p.userId" class="flex justify-between rounded-xl bg-cream-100 px-3 py-2">
              <span class="font-bold">{{ p.nickname }}</span>
              <span class="text-xs text-party-muted">{{ p.points }}</span>
            </li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</template>
