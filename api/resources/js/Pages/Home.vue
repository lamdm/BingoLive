<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import PartyLayout from '@/Layouts/PartyLayout.vue';
import RoomCard from '@/Components/RoomCard.vue';

defineOptions({ layout: PartyLayout });
const props = defineProps({ rooms: Array });
const live = computed(() => props.rooms.filter((r) => r.status === 'LIVE'));
const upcoming = computed(() => props.rooms.filter((r) => r.status === 'SCHEDULED'));
</script>

<template>
    <div class="space-y-10">
        <section
            class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-violet-100 via-cream-50 to-sky-100 p-8 shadow-soft ring-1 ring-violet-100 md:p-12"
        >
            <div class="relative z-10 max-w-xl">
                <p class="mb-2 text-sm font-bold uppercase tracking-wide text-party-purple">Live party bingo</p>
                <h1 class="font-display text-4xl font-bold leading-tight text-party-ink md:text-5xl">
                    Bingo đêm hội cùng bạn bè
                </h1>
                <p class="mt-3 text-base text-party-muted md:text-lg">
                    Laravel + Vue mới nhất. Phòng <strong>miễn phí</strong> và
                    <strong>phí điểm ảo</strong> — không tiền thật.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link
                        :href="route('rooms.index')"
                        class="rounded-full bg-party-purple px-5 py-3 text-sm font-extrabold text-white shadow-soft"
                    >
                        Vào phòng miễn phí
                    </Link>
                    <Link
                        :href="route('how-to-play')"
                        class="rounded-full bg-white px-5 py-3 text-sm font-extrabold text-party-purple ring-1 ring-violet-200"
                    >
                        Xem hướng dẫn
                    </Link>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">Miễn phí</span>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">Phí điểm ảo</span>
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-sky-700">Không đổi tiền</span>
                </div>
            </div>
        </section>

        <section>
            <h2 class="mb-4 font-display text-2xl font-bold text-party-ink">Phòng đang live</h2>
            <div v-if="!live.length" class="rounded-2xl bg-white p-6 text-party-muted ring-1 ring-violet-100">
                Chưa có phòng live.
            </div>
            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <RoomCard v-for="r in live" :key="r.id" :room="r" />
            </div>
        </section>

        <section>
            <h2 class="mb-4 font-display text-2xl font-bold text-party-ink">Sắp diễn ra</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <RoomCard v-for="r in upcoming" :key="r.id" :room="r" />
            </div>
        </section>
    </div>
</template>
