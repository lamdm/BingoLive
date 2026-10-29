<script setup>
import PartyLayout from '@/Layouts/PartyLayout.vue';
defineOptions({ layout: PartyLayout });
defineProps({ leaders: Array });
</script>

<template>
    <div class="space-y-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-party-ink">Xếp hạng</h1>
            <p class="text-party-muted">Theo ván thắng & điểm ảo — không xếp hạng tiền thật</p>
        </div>
        <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-violet-100">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream-100 text-xs uppercase text-party-muted">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Người chơi</th>
                        <th class="px-4 py-3">Thắng</th>
                        <th class="px-4 py-3">Điểm ảo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="l in leaders" :key="l.id" class="border-t border-violet-50">
                        <td class="px-4 py-3 font-bold text-party-purple">{{ l.rank }}</td>
                        <td class="px-4 py-3 font-bold">{{ l.nickname }}</td>
                        <td class="px-4 py-3">{{ l.wins }}</td>
                        <td class="px-4 py-3">{{ l.points }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
