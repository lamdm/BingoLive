<script setup>
import PartyLayout from '@/Layouts/PartyLayout.vue';
defineOptions({ layout: PartyLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-4">
        <h1 class="font-display text-3xl font-bold text-party-ink">Hướng dẫn chơi</h1>
        <div class="space-y-3 rounded-3xl bg-white p-6 text-sm leading-relaxed text-party-muted shadow-soft ring-1 ring-violet-100">
            <p><strong class="text-party-ink">1.</strong> Đăng ký / đăng nhập — nhận điểm ảo demo.</p>
            <p>
                <strong class="text-party-ink">2.</strong> Chọn phòng
                <span class="font-bold text-emerald-700">Miễn phí</span> hoặc
                <span class="font-bold text-amber-700">Có phí điểm</span>.
            </p>
            <p><strong class="text-party-ink">3.</strong> Vào phòng nhận thẻ 5×5.</p>
            <p><strong class="text-party-ink">4.</strong> Host quay số (Laravel Reverb realtime) — thẻ tự đánh dấu.</p>
            <p><strong class="text-party-ink">5.</strong> Đủ hàng/cột/chéo → Báo Bingo. Server PHP xác minh.</p>
            <p class="rounded-2xl bg-violet-50 p-3 font-bold text-party-ink">
                Điểm ảo chỉ dùng trong demo. Không nạp / rút / đổi tiền thật.
            </p>
        </div>
    </div>
</template>
