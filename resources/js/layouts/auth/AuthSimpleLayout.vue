<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

const props = withDefaults(defineProps<{
    title?: string;
    description?: string;
    logoSize?: number;
}>(), {
    logoSize: 180,
});
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-gradient-to-br from-background via-background to-muted/20 p-6 md:p-10"
    >
        <div class="w-full max-w-md">
            <div class="flex flex-col gap-8">
                <!-- Logo et titre -->
                <div class="flex flex-col items-center gap-6">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-3 font-medium transition-transform hover:scale-105"
                    >
                        <div
                            class="mb-2 flex items-center justify-center rounded-2xl bg-gradient-to-br from-primary/10 to-primary/5 p-4 shadow-lg backdrop-blur-sm"
                            :style="{ width: `${props.logoSize + 32}px`, height: `${Math.round(props.logoSize * 0.65) + 32}px` }"
                        >
                            <AppLogoIcon
                                class="w-auto object-contain drop-shadow-sm"
                                :style="{ maxHeight: `${Math.round(props.logoSize * 0.65)}px`, maxWidth: `${props.logoSize}px` }"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-3 text-center">
                        <h1 class="text-2xl font-semibold tracking-tight text-foreground">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground leading-relaxed">
                            {{ description }}
                        </p>
                    </div>
                </div>
                
                <!-- Carte du formulaire -->
                <div class="rounded-2xl border border-border/50 bg-card/50 p-8 shadow-xl backdrop-blur-sm transition-all hover:shadow-2xl">
                <slot />
                </div>
            </div>
        </div>
    </div>
</template>
