<script setup lang="ts">
import AppHeader from '../components/AppHeader.vue';
</script>

<template>
  <div class="min-h-screen font-body">
    <AppHeader />
    <main class="mx-auto max-w-6xl px-4 py-6">
      <RouterView />
    </main>
    <footer class="mx-auto max-w-6xl px-4 pb-10 text-center text-xs text-party-muted">
      BingoLive · Vue client + Laravel API · Điểm ảo không đổi tiền
    </footer>
  </div>
</template>
