<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PartyLayout from '@/Layouts/PartyLayout.vue';

defineOptions({ layout: PartyLayout });
const props = defineProps({ rooms: Array });
const selectedId = ref(props.rooms[0]?.id || null);
const createForm = useForm({
    name: '',
    description: '',
    entryType: 'FREE',
    entryFee: 50,
});

function post(path) {
    if (!selectedId.value) return;
    router.post(route(path, selectedId.value), {}, { preserveScroll: true });
}

function createRoom() {
    createForm.post(route('rooms.store'), {
        onSuccess: () => createForm.reset('name', 'description'),
    });
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-party-ink">Host control</h1>
            <p class="text-party-muted">Tạo phòng · quay số · stream demo · kết thúc</p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-violet-100">
                <h2 class="font-display text-lg font-bold">Tạo phòng</h2>
                <div class="mt-3 space-y-3">
                    <input
                        v-model="createForm.name"
                        class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100"
                        placeholder="Tên phòng"
                    />
                    <textarea
                        v-model="createForm.description"
                        rows="2"
                        class="w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100"
                        placeholder="Mô tả"
                    />
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full px-3 py-1.5 text-xs font-extrabold"
                            :class="createForm.entryType === 'FREE' ? 'bg-emerald-500 text-white' : 'bg-cream-100'"
                            @click="createForm.entryType = 'FREE'"
                        >
                            Miễn phí
                        </button>
                        <button
                            type="button"
                            class="rounded-full px-3 py-1.5 text-xs font-extrabold"
                            :class="createForm.entryType === 'PAID' ? 'bg-amber-500 text-white' : 'bg-cream-100'"
                            @click="createForm.entryType = 'PAID'"
                        >
                            Có phí điểm
                        </button>
                        <input
                            v-if="createForm.entryType === 'PAID'"
                            v-model.number="createForm.entryFee"
                            type="number"
                            min="10"
                            class="w-28 rounded-full border-0 bg-cream-100 px-3 py-1.5 text-xs ring-1 ring-violet-100"
                        />
                    </div>
                    <button
                        class="rounded-full bg-party-purple px-4 py-2 text-sm font-extrabold text-white"
                        :disabled="createForm.processing || !createForm.name"
                        @click="createRoom"
                    >
                        Tạo phòng
                    </button>
                </div>
            </section>

            <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-violet-100">
                <h2 class="font-display text-lg font-bold">Điều khiển ván</h2>
                <select
                    v-model="selectedId"
                    class="mt-3 w-full rounded-xl border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100"
                >
                    <option v-for="r in rooms" :key="r.id" :value="r.id">
                        {{ r.name }} · {{ r.status }} ·
                        {{ r.entryType === 'FREE' ? 'Free' : r.entryFee + 'điểm' }}
                    </option>
                </select>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button class="rounded-xl bg-emerald-500 py-3 text-sm font-extrabold text-white" @click="post('rooms.start')">
                        Start round
                    </button>
                    <button class="rounded-xl bg-party-purple py-3 text-sm font-extrabold text-white" @click="post('rooms.draw')">
                        Quay số tiếp
                    </button>
                    <button class="rounded-xl bg-amber-400 py-2 text-sm font-extrabold text-white" @click="post('rooms.pause')">
                        Tạm dừng
                    </button>
                    <button class="rounded-xl bg-sky-500 py-2 text-sm font-extrabold text-white" @click="post('rooms.resume')">
                        Tiếp tục
                    </button>
                    <button
                        class="rounded-xl bg-violet-200 py-2 text-sm font-extrabold text-party-ink"
                        @click="router.post(route('rooms.stream', selectedId), { live: true })"
                    >
                        Bật stream demo
                    </button>
                    <button class="rounded-xl bg-rose-500 py-2 text-sm font-extrabold text-white" @click="post('rooms.end')">
                        Kết thúc
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
