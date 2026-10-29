<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { api } from '../lib/api';
import BingoCard from '../components/BingoCard.vue';

const route = useRoute();
const winners = ref<any[]>([]);
onMounted(async () => {
  const data = await api<{ winners: any[] }>(`/rooms/${route.params.id}/result`);
  winners.value = data.winners;
});
</script>

<template>
  <div class="mx-auto max-w-xl space-y-4 text-center">
    <div class="rounded-[28px] bg-gradient-to-br from-violet-100 to-emerald-50 p-8 shadow-soft ring-1 ring-violet-100">
      <h1 class="font-display text-3xl font-bold">
        {{ winners[0] ? `Chúc mừng ${winners[0].nickname}!` : 'Chưa có người thắng' }}
      </h1>
      <p v-if="winners[0]" class="mt-2 text-party-muted">
        {{ winners[0].pattern }} · +{{ winners[0].pointsAwarded }} điểm ảo
      </p>
    </div>
    <div v-if="winners[0]" class="rounded-3xl bg-white p-4 text-left shadow-soft ring-1 ring-violet-100">
      <BingoCard :numbers="winners[0].numbers" :drawn="[]" />
    </div>
    <div class="flex justify-center gap-3">
      <RouterLink to="/rooms" class="rounded-full bg-party-purple px-5 py-3 text-sm font-extrabold text-white">Phòng khác</RouterLink>
      <RouterLink to="/" class="rounded-full bg-white px-5 py-3 text-sm font-extrabold text-party-purple ring-1 ring-violet-200">Home</RouterLink>
    </div>
  </div>
</template>
