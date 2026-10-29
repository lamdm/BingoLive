<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../lib/api';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const rooms = ref<any[]>([]);
const selectedId = ref<number | null>(null);
const log = ref<string[]>([]);
const form = ref({ name: '', description: '', entryType: 'FREE', entryFee: 50 });

async function load() {
  const data = await api<{ rooms: any[] }>('/host/rooms');
  rooms.value = data.rooms;
  if (!selectedId.value && rooms.value.length) selectedId.value = rooms.value[0].id;
}

onMounted(async () => {
  if (!auth.isHost) return router.push('/');
  await load();
});

function push(msg: string) {
  log.value.unshift(`${new Date().toLocaleTimeString()} · ${msg}`);
}

async function call(path: string, body?: object) {
  if (!selectedId.value) return;
  try {
    const data = await api<any>(`/rooms/${selectedId.value}/${path}`, {
      method: 'POST',
      body: body ? JSON.stringify(body) : undefined,
    });
    push(path + (data.number ? ` → ${data.number}` : ' OK'));
  } catch (e: any) {
    push(path + ' lỗi: ' + e.message);
  }
}

async function createRoom() {
  await api('/rooms', { method: 'POST', body: JSON.stringify(form.value) });
  form.value.name = '';
  await load();
  push('Tạo phòng OK');
}
</script>

<template>
  <div class="space-y-6">
    <h1 class="font-display text-3xl font-bold">Host control</h1>
    <div class="grid gap-4 lg:grid-cols-2">
      <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-violet-100">
        <h2 class="font-display text-lg font-bold">Tạo phòng</h2>
        <div class="mt-3 space-y-3">
          <input v-model="form.name" class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" placeholder="Tên phòng" />
          <textarea v-model="form.description" rows="2" class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100" />
          <div class="flex gap-2">
            <button class="rounded-full px-3 py-1.5 text-xs font-extrabold" :class="form.entryType==='FREE'?'bg-emerald-500 text-white':'bg-cream-100'" @click="form.entryType='FREE'">Miễn phí</button>
            <button class="rounded-full px-3 py-1.5 text-xs font-extrabold" :class="form.entryType==='PAID'?'bg-amber-500 text-white':'bg-cream-100'" @click="form.entryType='PAID'">Có phí</button>
            <input v-if="form.entryType==='PAID'" v-model.number="form.entryFee" type="number" class="w-24 rounded-full border-0 bg-cream-100 px-3 py-1 text-xs" />
          </div>
          <button class="rounded-full bg-party-purple px-4 py-2 text-sm font-extrabold text-white" :disabled="!form.name" @click="createRoom">Tạo</button>
        </div>
      </section>
      <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-violet-100">
        <h2 class="font-display text-lg font-bold">Điều khiển</h2>
        <select v-model="selectedId" class="mt-3 w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm">
          <option v-for="r in rooms" :key="r.id" :value="r.id">{{ r.name }} · {{ r.status }}</option>
        </select>
        <div class="mt-4 grid grid-cols-2 gap-2">
          <button class="rounded-xl bg-emerald-500 py-3 text-sm font-extrabold text-white" @click="call('start')">Start</button>
          <button class="rounded-xl bg-party-purple py-3 text-sm font-extrabold text-white" @click="call('draw')">Quay số</button>
          <button class="rounded-xl bg-amber-400 py-2 text-sm font-extrabold text-white" @click="call('pause')">Pause</button>
          <button class="rounded-xl bg-sky-500 py-2 text-sm font-extrabold text-white" @click="call('resume')">Resume</button>
          <button class="rounded-xl bg-violet-200 py-2 text-sm font-extrabold" @click="call('stream',{live:true})">Stream</button>
          <button class="rounded-xl bg-rose-500 py-2 text-sm font-extrabold text-white" @click="call('end')">End</button>
        </div>
      </section>
    </div>
    <ul class="rounded-3xl bg-white p-4 font-mono text-xs text-party-muted shadow-soft ring-1 ring-violet-100">
      <li v-for="(l,i) in log" :key="i">{{ l }}</li>
    </ul>
  </div>
</template>
