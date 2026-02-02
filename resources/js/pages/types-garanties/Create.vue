<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import FormSection from '@/components/FormSection.vue';
import { Settings } from 'lucide-vue-next';

interface Props {}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Types de garanties', href: '/types-garanties' },
    { title: 'Créer un type de garantie', href: '#' },
];

const form = useForm({
    code: '',
    libelle: '',
    type: '',
    description: '',
    decote_pourcentage: 0,
    ponderation_pourcentage: 100,
    actif: true,
});

const submit = () => {
    form.post('/types-garanties', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Créer un type de garantie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-gray-900">Créer un type de garantie</h1>
                <Settings class="h-5 w-5 text-gray-500" />
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <FormSection title="Informations du type" :columns="2">
                    <div>
                        <Label for="code" class="text-base font-medium text-gray-700">Code *</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            type="text"
                            required
                            placeholder="Ex: CAU-HYP"
                            class="mt-1.5 font-mono"
                        />
                        <InputError :message="form.errors.code" />
                        <p class="mt-1 text-xs text-gray-500">Code unique pour identifier le type (ex: CAU-HYP, GAR-HYP)</p>
                    </div>

                    <div>
                        <Label for="libelle" class="text-base font-medium text-gray-700">Libellé *</Label>
                        <Input
                            id="libelle"
                            v-model="form.libelle"
                            type="text"
                            required
                            placeholder="Ex: Caution Hypothécaire"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.libelle" />
                    </div>

                    <div>
                        <Label for="type" class="text-base font-medium text-gray-700">Type *</Label>
                        <select
                            id="type"
                            v-model="form.type"
                            required
                            class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        >
                            <option value="">Sélectionner un type</option>
                            <option value="Matérielle (réelle)">Matérielle (réelle)</option>
                            <option value="Personnelle">Personnelle</option>
                            <option value="Financière">Financière</option>
                            <option value="Garantie dérivée">Garantie dérivée</option>
                            <option value="Divers">Divers</option>
                        </select>
                        <InputError :message="form.errors.type" />
                    </div>

                    <div>
                        <Label for="actif" class="text-base font-medium text-gray-700">Statut</Label>
                        <select
                            id="actif"
                            v-model="form.actif"
                            class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        >
                            <option :value="true">Actif</option>
                            <option :value="false">Inactif</option>
                        </select>
                        <InputError :message="form.errors.actif" />
                    </div>

                    <div>
                        <Label for="decote_pourcentage" class="text-base font-medium text-gray-700">Décote (%) *</Label>
                        <Input
                            id="decote_pourcentage"
                            v-model.number="form.decote_pourcentage"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            required
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.decote_pourcentage" />
                        <p class="mt-1 text-xs text-gray-500">Pourcentage de décote appliqué (ex: 30 pour 30%)</p>
                    </div>

                    <div>
                        <Label for="ponderation_pourcentage" class="text-base font-medium text-gray-700">Pondération (%) *</Label>
                        <Input
                            id="ponderation_pourcentage"
                            v-model.number="form.ponderation_pourcentage"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            required
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.ponderation_pourcentage" />
                        <p class="mt-1 text-xs text-gray-500">Pourcentage de pondération (généralement 100 - décote)</p>
                    </div>

                    <div class="col-span-2">
                        <Label for="description" class="text-base font-medium text-gray-700">Description</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1.5 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                            placeholder="Description du type de garantie..."
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </FormSection>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="router.visit('/types-garanties')">
                        Annuler
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Création...' : 'Créer le type de garantie' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

