<script setup>
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import PartyLayout from '@/Layouts/PartyLayout.vue';
import BingoCard from '@/Components/BingoCard.vue';

defineOptions({ layout: PartyLayout });

const props = defineProps({
    room: Object,
    round: Object,
    card: Object,
    messages: Array,
    participants: Array,
    stream: Object,
});

const page = usePage();
const drawn = ref([...(props.round?.drawn || [])]);
const currentNumber = ref(props.round?.currentNumber ?? null);
const roundStatus = ref(props.round?.status ?? null);
const chatList = ref([...(props.messages || [])]);
const chatForm = useForm({ content: '' });
const claimForm = useForm({ cardId: props.card?.id });
const connected = ref(false);
let channel;

watch(
    () => props.round,
    (r) => {
        drawn.value = [...(r?.drawn || [])];
        currentNumber.value = r?.currentNumber ?? null;
        roundStatus.value = r?.status ?? null;
    },
);

watch(
    () => props.card?.id,
    (id) => {
        claimForm.cardId = id;
    },
);

const canClaim = computed(
    () => !!props.card && !!page.props.auth.user && roundStatus.value === 'PLAYING',
);

onMounted(() => {
    if (!window.Echo) return;
    channel = window.Echo.channel(`room.${props.room.id}`);
    connected.value = true;
    channel.listen('.game.number-drawn', (e) => {
        currentNumber.value = e.number;
        drawn.value = e.drawn || [];
    });
    channel.listen('.chat.message', (e) => {
        chatList.value.push(e.message || e);
    });
    channel.listen('.game.winner', () => {
        router.visit(route('rooms.result', props.room.id));
    });
    channel.listen('.room.state', (e) => {
        if (e.state?.round) {
            roundStatus.value = e.state.round.status;
            drawn.value = e.state.round.drawn || drawn.value;
            currentNumber.value = e.state.round.currentNumber;
        }
    });
});

onUnmounted(() => {
    if (channel && window.Echo) {
        window.Echo.leave(`room.${props.room.id}`);
    }
});

function sendChat() {
    chatForm.post(route('rooms.chat', props.room.id), {
        preserveScroll: true,
        onSuccess: () => chatForm.reset('content'),
    });
}

function claim() {
    claimForm.post(route('rooms.claim', props.room.id));
}

function join() {
    router.post(route('rooms.join', props.room.id));
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <span
                class="rounded-full px-2 py-1"
                :class="connected ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800'"
            >
                {{ connected ? 'Realtime OK' : 'Chưa kết nối Echo' }}
            </span>
            <span
                class="rounded-full px-2 py-1"
                :class="room.entryType === 'FREE' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800'"
            >
                {{ room.entryType === 'FREE' ? 'Miễn phí' : `Phí ${room.entryFee} điểm` }}
            </span>
            <span class="rounded-full bg-sky-100 px-2 py-1 text-sky-700">Demo · điểm ảo · không đổi tiền</span>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="overflow-hidden rounded-3xl bg-party-ink text-white shadow-soft">
                    <div class="relative aspect-video bg-gradient-to-br from-indigo-900 via-violet-800 to-sky-800">
                        <div class="absolute left-3 top-3 flex gap-2">
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[11px] font-extrabold">LIVE</span>
                        </div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                            <div class="mb-2 text-sm font-bold text-violet-200">{{ room.host?.nickname }}</div>
                            <p class="max-w-md text-sm text-violet-100/90">{{ stream?.message }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                    <div class="flex flex-wrap items-center gap-4">
                        <div v-if="currentNumber" :key="currentNumber" class="ball ball-lg">{{ currentNumber }}</div>
                        <div
                            v-else
                            class="flex h-24 w-24 items-center justify-center rounded-full bg-cream-100 text-xs font-bold text-party-muted"
                        >
                            Chờ quay
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-display text-lg font-bold text-party-ink">{{ room.name }}</div>
                            <div class="text-sm text-party-muted">
                                {{ roundStatus || '—' }} · Đã quay {{ drawn.length }}/75
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <span v-for="n in drawn.slice(-12)" :key="n" class="ball h-8 w-8 text-xs">{{ n }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-display text-lg font-bold">Thẻ của bạn</h2>
                        <button
                            v-if="!card && page.props.auth.user"
                            class="text-sm font-bold text-party-purple"
                            @click="join"
                        >
                            Nhận thẻ
                        </button>
                        <Link v-else-if="!page.props.auth.user" :href="route('login')" class="text-sm font-bold text-party-purple">
                            Đăng nhập
                        </Link>
                    </div>
                    <BingoCard v-if="card" :numbers="card.numbers" :drawn="drawn" />
                    <div v-if="page.props.errors?.claim" class="mt-2 text-sm font-bold text-rose-600">
                        {{ page.props.errors.claim }}
                    </div>
                    <button
                        class="mt-4 w-full rounded-full bg-party-purple py-3 text-sm font-extrabold text-white disabled:opacity-50"
                        :disabled="!canClaim || claimForm.processing"
                        @click="claim"
                    >
                        Báo Bingo
                    </button>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="flex h-[420px] flex-col rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                    <h3 class="font-display font-bold">Chat phòng</h3>
                    <div class="mt-2 flex-1 space-y-2 overflow-y-auto text-sm">
                        <div v-for="m in chatList" :key="m.id" class="rounded-xl bg-cream-100 px-3 py-2">
                            <div class="text-[11px] font-bold text-party-purple">{{ m.nickname }}</div>
                            <div>{{ m.content }}</div>
                        </div>
                    </div>
                    <form class="mt-2 flex gap-2" @submit.prevent="sendChat">
                        <input
                            v-model="chatForm.content"
                            class="flex-1 rounded-full border-0 bg-cream-100 px-3 py-2 text-sm ring-1 ring-violet-100"
                            placeholder="Nhắn gì đó vui vẻ…"
                            :disabled="!page.props.auth.user"
                        />
                        <button class="rounded-full bg-party-purple px-3 py-2 text-xs font-extrabold text-white" type="submit">
                            Gửi
                        </button>
                    </form>
                </div>

                <div class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                    <h3 class="font-display font-bold">Người chơi</h3>
                    <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto text-sm">
                        <li
                            v-for="p in participants"
                            :key="p.userId"
                            class="flex justify-between rounded-xl bg-cream-100 px-3 py-2"
                        >
                            <span class="font-bold">{{ p.nickname }}</span>
                            <span class="text-xs text-party-muted">{{ p.points }} sao</span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</template>
