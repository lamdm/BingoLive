<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import PartyLayout from '@/Layouts/PartyLayout.vue';
import RoomCard from '@/Components/RoomCard.vue';

defineOptions({ layout: PartyLayout });
const props = defineProps({ rooms: Array, filters: Object });
const status = ref(props.filters.status || 'all');
const entryType = ref(props.filters.entryType || 'all');
const q = ref(props.filters.q || '');

function reload() {
    router.get(
        route('rooms.index'),
        {
            status: status.value === 'all' ? undefined : status.value,
            entryType: entryType.value === 'all' ? undefined : entryType.value,
            q: q.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

watch([status, entryType], reload);
let t;
function onSearch() {
    clearTimeout(t);
    t = setTimeout(reload, 250);
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-party-ink">Phòng chơi</h1>
            <p class="text-party-muted">Lọc miễn phí / có phí điểm</p>
        </div>
        <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100 md:flex-row md:items-center">
            <input
                v-model="q"
                type="search"
                placeholder="Tìm tên phòng…"
                class="w-full rounded-full border-0 bg-cream-100 px-4 py-2 text-sm outline-none ring-1 ring-violet-100 md:max-w-xs"
                @input="onSearch"
            />
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="s in [
                        ['all', 'Tất cả'],
                        ['LIVE', 'Đang live'],
                        ['SCHEDULED', 'Sắp diễn ra'],
                        ['ENDED', 'Đã kết thúc'],
                    ]"
                    :key="s[0]"
                    class="rounded-full px-3 py-1.5 text-xs font-extrabold"
                    :class="status === s[0] ? 'bg-party-purple text-white' : 'bg-cream-100 text-party-muted'"
                    @click="status = s[0]"
                >
                    {{ s[1] }}
                </button>
            </div>
            <div class="flex flex-wrap gap-2 md:ml-auto">
                <button
                    v-for="e in [
                        ['all', 'Mọi loại'],
                        ['FREE', 'Miễn phí'],
                        ['PAID', 'Có phí điểm'],
                    ]"
                    :key="e[0]"
                    class="rounded-full px-3 py-1.5 text-xs font-extrabold"
                    :class="entryType === e[0] ? 'bg-emerald-500 text-white' : 'bg-cream-100 text-party-muted'"
                    @click="entryType = e[0]"
                >
                    {{ e[1] }}
                </button>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <RoomCard v-for="r in rooms" :key="r.id" :room="r" />
        </div>
    </div>
</template>
