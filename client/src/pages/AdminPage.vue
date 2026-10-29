<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../lib/api';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const tab = ref('overview');
const data = ref<any>(null);

async function load() {
  data.value = await api('/admin/overview');
}

onMounted(async () => {
  if (!auth.isAdmin) return router.push('/');
  await load();
});

async function setUserStatus(id: number, status: string) {
  await api(`/admin/users/${id}`, { method: 'PATCH', body: JSON.stringify({ status }) });
  await load();
}
async function closeRoom(id: number) {
  await api(`/admin/rooms/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ status: 'ENDED', streamStatus: 'ENDED' }),
  });
  await load();
}
</script>

<template>
  <div v-if="data" class="space-y-6">
    <h1 class="font-display text-3xl font-bold">Admin</h1>
    <div class="flex flex-wrap gap-2">
      <button
        v-for="t in [['overview','Tổng quan'],['rooms','Phòng'],['users','Users'],['logs','Logs']]"
        :key="t[0]"
        class="rounded-full px-4 py-2 text-sm font-extrabold"
        :class="tab===t[0]?'bg-party-purple text-white':'bg-white ring-1 ring-violet-100'"
        @click="tab=t[0]"
      >{{ t[1] }}</button>
    </div>
    <div v-if="tab==='overview'" class="grid gap-4 sm:grid-cols-4">
      <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
        <div class="text-xs text-party-muted">Users</div>
        <div class="font-display text-3xl font-bold">{{ data.stats.users }}</div>
      </div>
      <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
        <div class="text-xs text-party-muted">Live</div>
        <div class="font-display text-3xl font-bold text-rose-500">{{ data.stats.live }}</div>
      </div>
      <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
        <div class="text-xs text-party-muted">Upcoming</div>
        <div class="font-display text-3xl font-bold text-sky-600">{{ data.stats.scheduled }}</div>
      </div>
      <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
        <div class="text-xs text-party-muted">Ended</div>
        <div class="font-display text-3xl font-bold">{{ data.stats.ended }}</div>
      </div>
    </div>
    <div v-if="tab==='rooms'" class="overflow-x-auto rounded-3xl bg-white shadow-soft ring-1 ring-violet-100">
      <table class="min-w-full text-sm">
        <thead class="bg-cream-100 text-xs uppercase text-party-muted">
          <tr><th class="px-4 py-3 text-left">Phòng</th><th class="px-4 py-3">Loại</th><th class="px-4 py-3">Status</th><th></th></tr>
        </thead>
        <tbody>
          <tr v-for="r in data.rooms" :key="r.id" class="border-t border-violet-50">
            <td class="px-4 py-3 font-bold">{{ r.name }}</td>
            <td class="px-4 py-3 text-center">{{ r.entryType==='FREE'?'Free':r.entryFee+'điểm' }}</td>
            <td class="px-4 py-3 text-center">{{ r.status }}</td>
            <td class="px-4 py-3"><button v-if="r.status!=='ENDED'" class="text-xs font-bold text-rose-600" @click="closeRoom(r.id)">Đóng</button></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="tab==='users'" class="overflow-x-auto rounded-3xl bg-white shadow-soft ring-1 ring-violet-100">
      <table class="min-w-full text-sm">
        <thead class="bg-cream-100 text-xs uppercase text-party-muted">
          <tr><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Points</th><th class="px-4 py-3">Status</th><th></th></tr>
        </thead>
        <tbody>
          <tr v-for="u in data.users" :key="u.id" class="border-t border-violet-50">
            <td class="px-4 py-3 font-bold">{{ u.nickname || u.name }}</td>
            <td class="px-4 py-3 text-center">{{ u.role }}</td>
            <td class="px-4 py-3 text-center">{{ u.points }}</td>
            <td class="px-4 py-3 text-center">{{ u.status }}</td>
            <td class="px-4 py-3 text-center">
              <button v-if="u.status==='ACTIVE'" class="text-xs font-bold text-rose-600" @click="setUserStatus(u.id,'LOCKED')">Khóa</button>
              <button v-else class="text-xs font-bold text-emerald-600" @click="setUserStatus(u.id,'ACTIVE')">Mở</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <ul v-if="tab==='logs'" class="max-h-96 space-y-2 overflow-y-auto rounded-3xl bg-white p-4 font-mono text-xs shadow-soft ring-1 ring-violet-100">
      <li v-for="l in data.logs" :key="l.id" class="rounded-lg bg-cream-100 px-3 py-2">{{ l.created_at }} · {{ l.action }}</li>
    </ul>
  </div>
</template>
