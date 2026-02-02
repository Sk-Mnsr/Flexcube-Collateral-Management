<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const props = defineProps<{
    title?: string;
    description?: string;
    logoSize?: number;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description" :logo-size="logoSize">
        <slot />
    </AuthLayout>
</template>
