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

interface Props {
    typeGarantie: {
        id: number;
        code: string;
        libelle: string;
        type: string;
        description?: string;
        decote_pourcentage: number;
        ponderation_pourcentage: number;
        actif: boolean;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Types de garanties', href: '/types-garanties' },
    { title: props.typeGarantie.code, href: `/types-garanties/${props.typeGarantie.id}` },
    { title: 'Modifier', href: '#' },
];

const form = useForm({
    code: props.typeGarantie.code,
    libelle: props.typeGarantie.libelle,
    type: props.typeGarantie.type,
    description: props.typeGarantie.description || '',
    decote_pourcentage: props.typeGarantie.decote_pourcentage,
    ponderation_pourcentage: props.typeGarantie.ponderation_pourcentage,
    actif: props.typeGarantie.actif,
});

const submit = () => {
    form.put(`/types-garanties/${props.typeGarantie.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Modifier un type de garantie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-gray-900">Modifier le type de garantie {{ props.typeGarantie.code }}</h1>
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
                            class="mt-1.5 font-mono"
                        />
                        <InputError :message="form.errors.code" />
                    </div>

                    <div>
                        <Label for="libelle" class="text-base font-medium text-gray-700">Libellé *</Label>
                        <Input
                            id="libelle"
                            v-model="form.libelle"
                            type="text"
                            required
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
                    </div>

                    <div class="col-span-2">
                        <Label for="description" class="text-base font-medium text-gray-700">Description</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1.5 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </FormSection>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="router.visit(`/types-garanties/${props.typeGarantie.id}`)">
                        Annuler
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Mise à jour...' : 'Mettre à jour le type' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

