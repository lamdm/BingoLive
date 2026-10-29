<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ room: { type: Object, required: true } });

const statusLabel = computed(() => {
    if (props.room.status === 'LIVE') return 'LIVE';
    if (props.room.status === 'SCHEDULED') return 'SẮP DIỄN RA';
    return 'ĐÃ KẾT THÚC';
});

const statusClass = computed(() => {
    if (props.room.status === 'LIVE') return 'bg-rose-500 text-white';
    if (props.room.status === 'SCHEDULED') return 'bg-sky-100 text-sky-700';
    return 'bg-slate-200 text-slate-600';
});
</script>

<template>
    <article class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
        <div class="flex items-start gap-3">
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-200 to-sky-200 font-display text-lg font-bold text-party-purple"
            >
                {{ room.host?.nickname?.slice(0, 1) || 'B' }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-extrabold" :class="statusClass">
                        {{ statusLabel }}
                    </span>
                    <span
                        v-if="room.entryType === 'FREE'"
                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-extrabold text-emerald-700"
                    >
                        Miễn phí
                    </span>
                    <span
                        v-else
                        class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-extrabold text-amber-800"
                    >
                        Phí {{ room.entryFee }} điểm
                    </span>
                </div>
                <h3 class="truncate font-display text-lg font-semibold text-party-ink">{{ room.name }}</h3>
                <p class="text-xs text-party-muted">
                    Host {{ room.host?.nickname || '—' }} · {{ room.viewerCount }} đang xem
                </p>
            </div>
        </div>
        <p v-if="room.description" class="line-clamp-2 text-sm text-party-muted">{{ room.description }}</p>
        <div class="mt-auto flex gap-2">
            <Link
                :href="route('rooms.show', room.id)"
                class="rounded-full px-3 py-1.5 text-sm font-bold text-party-purple ring-1 ring-violet-200"
            >
                Chi tiết
            </Link>
            <Link
                v-if="room.status !== 'ENDED'"
                :href="route('rooms.live', room.id)"
                class="rounded-full bg-party-purple px-3 py-1.5 text-sm font-bold text-white"
            >
                Vào phòng
            </Link>
        </div>
    </article>
</template>
