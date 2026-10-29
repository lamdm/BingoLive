<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { api } from '../lib/api';
import RoomCard from '../components/RoomCard.vue';

const rooms = ref<any[]>([]);
onMounted(async () => {
  const data = await api<{ rooms: any[] }>('/rooms');
  rooms.value = data.rooms;
});
const live = computed(() => rooms.value.filter((r) => r.status === 'LIVE'));
const upcoming = computed(() => rooms.value.filter((r) => r.status === 'SCHEDULED'));
</script>

<template>
  <div class="space-y-10">
    <section
      class="rounded-[28px] bg-gradient-to-br from-violet-100 via-cream-50 to-sky-100 p-8 shadow-soft ring-1 ring-violet-100 md:p-12"
    >
      <p class="mb-2 text-sm font-bold uppercase tracking-wide text-party-purple">Vue client · Laravel API</p>
      <h1 class="font-display text-4xl font-bold text-party-ink md:text-5xl">Bingo đêm hội cùng bạn bè</h1>
      <p class="mt-3 max-w-xl text-party-muted">
        Phòng <strong>miễn phí</strong> và <strong>phí điểm ảo</strong> — không tiền thật.
      </p>
      <div class="mt-6 flex flex-wrap gap-3">
        <RouterLink to="/rooms" class="rounded-full bg-party-purple px-5 py-3 text-sm font-extrabold text-white shadow-soft">
          Vào phòng miễn phí
        </RouterLink>
        <RouterLink
          to="/how-to-play"
          class="rounded-full bg-white px-5 py-3 text-sm font-extrabold text-party-purple ring-1 ring-violet-200"
        >
          Hướng dẫn
        </RouterLink>
      </div>
    </section>

    <section>
      <h2 class="mb-4 font-display text-2xl font-bold">Phòng đang live</h2>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <RoomCard v-for="r in live" :key="r.id" :room="r" />
      </div>
    </section>
    <section>
      <h2 class="mb-4 font-display text-2xl font-bold">Sắp diễn ra</h2>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <RoomCard v-for="r in upcoming" :key="r.id" :room="r" />
      </div>
    </section>
  </div>
</template>
