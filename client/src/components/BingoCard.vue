<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ numbers: number[][]; drawn: number[] }>();
const cols = ['B', 'I', 'N', 'G', 'O'];
const drawnSet = computed(() => new Set(props.drawn));
function marked(r: number, c: number, n: number) {
  return (r === 2 && c === 2) || (n > 0 && drawnSet.value.has(n));
}
</script>

<template>
  <div class="overflow-hidden rounded-2xl bg-white p-3 shadow-soft ring-1 ring-violet-100">
    <div class="mb-2 grid grid-cols-5 gap-1">
      <div
        v-for="c in cols"
        :key="c"
        class="rounded-lg bg-party-purple/10 py-1 text-center font-display text-sm font-bold text-party-purple"
      >
        {{ c }}
      </div>
    </div>
    <div class="grid grid-cols-5 gap-1">
      <template v-for="(row, r) in numbers" :key="r">
        <div
          v-for="(n, c) in row"
          :key="`${r}-${c}`"
          class="flex aspect-square items-center justify-center rounded-xl text-sm font-extrabold"
          :class="marked(r, c, n) ? 'bg-party-purple text-white shadow' : 'bg-cream-100 text-party-ink'"
        >
          {{ r === 2 && c === 2 ? 'FREE' : n }}
        </div>
      </template>
    </div>
  </div>
</template>
