<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PartyLayout from '@/Layouts/PartyLayout.vue';

defineOptions({ layout: PartyLayout });
defineProps({ stats: Object, rooms: Array, users: Array, logs: Array });
const tab = ref('overview');

function setUserStatus(id, status) {
    router.patch(route('admin.users.update', id), { status }, { preserveScroll: true });
}
function closeRoom(id) {
    router.patch(
        route('admin.rooms.update', id),
        { status: 'ENDED', streamStatus: 'ENDED' },
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-party-ink">Admin</h1>
            <p class="text-party-muted">Quản lý phòng / user / nhật ký — không module tiền thật</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="t in [
                    ['overview', 'Tổng quan'],
                    ['rooms', 'Phòng'],
                    ['users', 'Người dùng'],
                    ['logs', 'Nhật ký'],
                ]"
                :key="t[0]"
                class="rounded-full px-4 py-2 text-sm font-extrabold"
                :class="tab === t[0] ? 'bg-party-purple text-white' : 'bg-white text-party-muted ring-1 ring-violet-100'"
                @click="tab = t[0]"
            >
                {{ t[1] }}
            </button>
        </div>

        <div v-if="tab === 'overview'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                <div class="text-xs font-bold text-party-muted">Người dùng</div>
                <div class="font-display text-3xl font-bold">{{ stats.users }}</div>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                <div class="text-xs font-bold text-party-muted">Phòng live</div>
                <div class="font-display text-3xl font-bold text-rose-500">{{ stats.live }}</div>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                <div class="text-xs font-bold text-party-muted">Sắp diễn ra</div>
                <div class="font-display text-3xl font-bold text-sky-600">{{ stats.scheduled }}</div>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
                <div class="text-xs font-bold text-party-muted">Đã kết thúc</div>
                <div class="font-display text-3xl font-bold">{{ stats.ended }}</div>
            </div>
        </div>

        <div v-if="tab === 'rooms'" class="overflow-x-auto rounded-3xl bg-white shadow-soft ring-1 ring-violet-100">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream-100 text-xs uppercase text-party-muted">
                    <tr>
                        <th class="px-4 py-3">Phòng</th>
                        <th class="px-4 py-3">Host</th>
                        <th class="px-4 py-3">Loại</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rooms" :key="r.id" class="border-t border-violet-50">
                        <td class="px-4 py-3 font-bold">{{ r.name }}</td>
                        <td class="px-4 py-3">{{ r.hostNickname }}</td>
                        <td class="px-4 py-3">
                            {{ r.entryType === 'FREE' ? 'Miễn phí' : `${r.entryFee} điểm` }}
                        </td>
                        <td class="px-4 py-3">{{ r.status }}</td>
                        <td class="px-4 py-3">
                            <button
                                v-if="r.status !== 'ENDED'"
                                class="text-xs font-bold text-rose-600"
                                @click="closeRoom(r.id)"
                            >
                                Đóng
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="tab === 'users'" class="overflow-x-auto rounded-3xl bg-white shadow-soft ring-1 ring-violet-100">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream-100 text-xs uppercase text-party-muted">
                    <tr>
                        <th class="px-4 py-3">Nickname</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Điểm</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in users" :key="u.id" class="border-t border-violet-50">
                        <td class="px-4 py-3 font-bold">{{ u.nickname || u.name }}</td>
                        <td class="px-4 py-3">{{ u.email }}</td>
                        <td class="px-4 py-3">{{ u.role }}</td>
                        <td class="px-4 py-3">{{ u.points }}</td>
                        <td class="px-4 py-3">{{ u.status }}</td>
                        <td class="px-4 py-3">
                            <button
                                v-if="u.status === 'ACTIVE'"
                                class="text-xs font-bold text-rose-600"
                                @click="setUserStatus(u.id, 'LOCKED')"
                            >
                                Khóa
                            </button>
                            <button v-else class="text-xs font-bold text-emerald-600" @click="setUserStatus(u.id, 'ACTIVE')">
                                Mở
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="tab === 'logs'" class="rounded-3xl bg-white p-4 shadow-soft ring-1 ring-violet-100">
            <ul class="max-h-96 space-y-2 overflow-y-auto font-mono text-xs text-party-muted">
                <li v-for="l in logs" :key="l.id" class="rounded-lg bg-cream-100 px-3 py-2">
                    {{ l.created_at }} · {{ l.action }} · {{ l.resource_type }}/{{ l.resource_id }}
                </li>
            </ul>
        </div>
    </div>
</template>
