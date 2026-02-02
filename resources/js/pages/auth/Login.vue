<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, Mail, Lock, LogIn } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Bienvenu(e) sur Collateral Management !"
        description="Veuillez saisir vos identifiants pour vous connecter"
        :logo-size="300"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-6 rounded-lg border border-green-200 bg-green-50/50 p-4 text-center text-sm font-medium text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <!-- Email -->
                <div class="grid gap-2.5">
                    <Label for="email" class="text-sm font-medium text-foreground">Email</Label>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                            <Mail class="h-5 w-5" />
                        </div>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="email@cofinacorp.com"
                            class="pl-10 h-11 transition-all focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <!-- Mot de passe -->
                <div class="grid gap-2.5">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-medium text-foreground">Mot de passe</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs text-primary hover:text-primary/80 transition-colors"
                            :tabindex="5"
                        >
                            Mot de passe oublié ?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                            <Lock class="h-5 w-5" />
                        </div>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="pl-10 h-11 transition-all focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between pt-1">
                    <Label for="remember" class="flex cursor-pointer items-center space-x-2.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Se souvenir de moi</span>
                    </Label>
                </div>

                <!-- Bouton de connexion -->
                <Button
                    type="submit"
                    class="mt-2 h-11 w-full text-base font-medium shadow-md transition-all hover:shadow-lg"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <template v-if="!processing">
                        <LogIn class="mr-2 h-5 w-5" />
                    </template>
                    <LoaderCircle
                        v-else
                        class="mr-2 h-5 w-5 animate-spin"
                    />
                    {{ processing ? 'Connexion en cours...' : 'Se connecter' }}
                </Button>
            </div>

            <!-- Lien d'inscription 
            <div
                class="pt-4 text-center text-sm text-muted-foreground border-t border-border/50"
                v-if="canRegister"
            >
                Vous n'avez pas de compte ?
                <TextLink :href="register()" :tabindex="5" class="font-medium text-primary hover:text-primary/80">S'inscrire</TextLink>
            </div>
            -->
        </Form>
    </AuthBase>
</template>
