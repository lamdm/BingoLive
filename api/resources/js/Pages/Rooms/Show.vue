<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import PartyLayout from '@/Layouts/PartyLayout.vue';

defineOptions({ layout: PartyLayout });
defineProps({ room: Object });
const page = usePage();
const form = useForm({});
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-5">
        <section class="space-y-4 lg:col-span-3">
            <div class="rounded-[28px] bg-white p-6 shadow-soft ring-1 ring-violet-100">
                <div class="mb-3 flex flex-wrap gap-2">
                    <span class="rounded-full bg-rose-500 px-3 py-1 text-xs font-extrabold text-white">{{
                        room.status
                    }}</span>
                    <span
                        class="rounded-full px-3 py-1 text-xs font-extrabold"
                        :class="
                            room.entryType === 'FREE'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-amber-100 text-amber-800'
                        "
                    >
                        {{ room.entryType === 'FREE' ? 'Miễn phí' : `Phí ${room.entryFee} điểm ảo` }}
                    </span>
                </div>
                <h1 class="font-display text-3xl font-bold text-party-ink">{{ room.name }}</h1>
                <p class="mt-2 text-party-muted">{{ room.description }}</p>
                <p class="mt-4 text-sm text-party-muted">
                    Host <strong class="text-party-ink">{{ room.host?.nickname }}</strong>
                </p>
            </div>
        </section>
        <aside class="space-y-4 lg:col-span-2">
            <div class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-violet-100">
                <h2 class="font-display text-lg font-bold">Thể lệ</h2>
                <ul class="mt-3 space-y-2 text-sm text-party-muted">
                    <li>Thẻ Bingo 75 · 5×5 · FREE giữa</li>
                    <li>Thắng: {{ (room.bingoRule || []).join(' / ') }}</li>
                    <li class="font-bold text-emerald-700">Không tiền thật · không rút tiền</li>
                </ul>
                <div v-if="page.props.errors?.join" class="mt-2 text-sm font-bold text-rose-600">
                    {{ page.props.errors.join }}
                </div>
                <button
                    v-if="page.props.auth.user"
                    class="mt-4 w-full rounded-full bg-party-purple py-3 text-sm font-extrabold text-white disabled:opacity-60"
                    :disabled="form.processing || room.status === 'ENDED'"
                    @click="form.post(route('rooms.join', room.id))"
                >
                    Tham gia & nhận thẻ
                </button>
                <Link v-else :href="route('login')" class="mt-4 block text-center font-bold text-party-purple">
                    Đăng nhập để tham gia
                </Link>
                <Link :href="route('rooms.live', room.id)" class="mt-2 block text-center text-sm font-bold text-party-purple">
                    Chỉ xem live
                </Link>
            </div>
        </aside>
    </div>
</template>
