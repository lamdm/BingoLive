<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { api } from '../lib/api';
import RoomCard from '../components/RoomCard.vue';

const rooms = ref<any[]>([]);
const status = ref('all');
const entryType = ref('all');
const q = ref('');

async function load() {
  const params = new URLSearchParams();
  if (status.value !== 'all') params.set('status', status.value);
  if (entryType.value !== 'all') params.set('entryType', entryType.value);
  if (q.value) params.set('q', q.value);
  const data = await api<{ rooms: any[] }>(`/rooms?${params}`);
  rooms.value = data.rooms;
}

onMounted(load);
watch([status, entryType], load);
let t: number | undefined;
function onSearch() {
  clearTimeout(t);
  t = window.setTimeout(load, 250);
}
</script>

<template>
  <div class="space-y-6">
    <h1 class="font-display text-3xl font-bold">Phòng chơi</h1>
    <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100 md:flex-row md:items-center">
      <input
        v-model="q"
        class="rounded-full border-0 bg-cream-100 px-4 py-2 text-sm ring-1 ring-violet-100 md:max-w-xs"
        placeholder="Tìm phòng…"
        @input="onSearch"
      />
      <div class="flex flex-wrap gap-2">
        <button
          v-for="s in [['all','Tất cả'],['LIVE','Live'],['SCHEDULED','Sắp tới'],['ENDED','Xong']]"
          :key="s[0]"
          class="rounded-full px-3 py-1.5 text-xs font-extrabold"
          :class="status===s[0]?'bg-party-purple text-white':'bg-cream-100'"
          @click="status=s[0]"
        >{{ s[1] }}</button>
      </div>
      <div class="flex flex-wrap gap-2 md:ml-auto">
        <button
          v-for="e in [['all','Mọi loại'],['FREE','Miễn phí'],['PAID','Có phí']]"
          :key="e[0]"
          class="rounded-full px-3 py-1.5 text-xs font-extrabold"
          :class="entryType===e[0]?'bg-emerald-500 text-white':'bg-cream-100'"
          @click="entryType=e[0]"
        >{{ e[1] }}</button>
      </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <RoomCard v-for="r in rooms" :key="r.id" :room="r" />
    </div>
  </div>
</template>
