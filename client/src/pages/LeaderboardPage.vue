<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { api } from '../lib/api';
const leaders = ref<any[]>([]);
onMounted(async () => {
  const data = await api<{ leaders: any[] }>('/leaderboard');
  leaders.value = data.leaders;
});
</script>

<template>
  <div class="space-y-4">
    <h1 class="font-display text-3xl font-bold">Xếp hạng</h1>
    <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-violet-100">
      <table class="min-w-full text-sm">
        <thead class="bg-cream-100 text-xs uppercase text-party-muted">
          <tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Player</th><th class="px-4 py-3">Wins</th><th class="px-4 py-3">Points</th></tr>
        </thead>
        <tbody>
          <tr v-for="l in leaders" :key="l.id" class="border-t border-violet-50">
            <td class="px-4 py-3 font-bold text-party-purple">{{ l.rank }}</td>
            <td class="px-4 py-3 font-bold">{{ l.nickname }}</td>
            <td class="px-4 py-3 text-center">{{ l.wins }}</td>
            <td class="px-4 py-3 text-center">{{ l.points }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
