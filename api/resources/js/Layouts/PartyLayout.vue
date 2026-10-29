<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen">
        <header class="sticky top-0 z-40 border-b border-violet-100/80 bg-cream-50/90 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
                <Link :href="route('home')" class="font-display text-xl font-bold text-party-ink">
                    Bingo<span class="text-party-purple">Live</span>
                </Link>
                <nav class="ml-4 hidden items-center gap-4 text-sm font-semibold text-party-muted md:flex">
                    <Link :href="route('home')" class="hover:text-party-purple">Trang chủ</Link>
                    <Link :href="route('rooms.index')" class="hover:text-party-purple">Phòng chơi</Link>
                    <Link :href="route('leaderboard')" class="hover:text-party-purple">Xếp hạng</Link>
                    <Link :href="route('how-to-play')" class="hover:text-party-purple">Hướng dẫn</Link>
                    <Link
                        v-if="user && (user.role === 'HOST' || user.role === 'ADMIN')"
                        :href="route('host.index')"
                        class="hover:text-party-purple"
                    >
                        Host
                    </Link>
                    <Link
                        v-if="user?.role === 'ADMIN'"
                        :href="route('admin.index')"
                        class="hover:text-party-purple"
                    >
                        Admin
                    </Link>
                </nav>
                <div class="ml-auto flex items-center gap-2">
                    <template v-if="user">
                        <div class="hidden rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-party-purple sm:block">
                            {{ user.points }} điểm ảo
                        </div>
                        <Link
                            :href="route('profile.edit')"
                            class="rounded-full bg-white px-3 py-1.5 text-sm font-bold text-party-ink shadow-sm ring-1 ring-violet-100"
                        >
                            {{ user.nickname }}
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="rounded-full px-3 py-1.5 text-sm font-semibold text-party-muted hover:text-party-ink"
                        >
                            Đăng xuất
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-full px-3 py-1.5 text-sm font-bold text-party-purple ring-1 ring-violet-200"
                        >
                            Đăng nhập
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-full bg-party-purple px-3 py-1.5 text-sm font-bold text-white shadow-soft"
                        >
                            Đăng ký
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-6">
            <slot />
        </main>

        <footer class="mx-auto max-w-6xl px-4 pb-10 pt-4 text-center text-xs text-party-muted">
            BingoLive · Laravel + Vue · Điểm ảo không đổi tiền · Free & phí điểm
        </footer>
    </div>
</template>
