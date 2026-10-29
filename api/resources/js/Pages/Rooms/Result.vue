<script setup>
import { Link } from '@inertiajs/vue3';
import PartyLayout from '@/Layouts/PartyLayout.vue';
import BingoCard from '@/Components/BingoCard.vue';

defineOptions({ layout: PartyLayout });
defineProps({ room: Object, winners: Array });
</script>

<template>
    <div class="mx-auto max-w-xl space-y-4 text-center">
        <div class="rounded-[28px] bg-gradient-to-br from-violet-100 to-emerald-50 p-8 shadow-soft ring-1 ring-violet-100">
            <p class="text-sm font-bold uppercase tracking-wide text-party-purple">Kết quả ván</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-party-ink">
                {{ winners[0] ? `Chúc mừng ${winners[0].nickname}!` : 'Chưa có người thắng' }}
            </h1>
            <p v-if="winners[0]" class="mt-2 text-party-muted">
                Pattern {{ winners[0].pattern }} · +{{ winners[0].pointsAwarded }} điểm ảo
            </p>
            <p class="mt-2 text-xs font-bold text-emerald-700">Không tiền thật · chỉ điểm demo</p>
        </div>
        <div v-if="winners[0]" class="rounded-3xl bg-white p-4 text-left shadow-soft ring-1 ring-violet-100">
            <BingoCard :numbers="winners[0].numbers" :drawn="[]" />
        </div>
        <div class="flex flex-wrap justify-center gap-3">
            <Link :href="route('rooms.index')" class="rounded-full bg-party-purple px-5 py-3 text-sm font-extrabold text-white">
                Phòng khác
            </Link>
            <Link
                :href="route('home')"
                class="rounded-full bg-white px-5 py-3 text-sm font-extrabold text-party-purple ring-1 ring-violet-200"
            >
                Về trang chủ
            </Link>
        </div>
    </div>
</template>
