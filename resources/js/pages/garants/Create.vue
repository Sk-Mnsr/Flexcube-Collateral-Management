<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import FormSection from '@/components/FormSection.vue';
import { User, Calendar, Upload } from 'lucide-vue-next';

interface Props {}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Garants', href: '/garants' },
    { title: 'Créer un garant', href: '#' },
];

const form = useForm({
    civilite: 'M',
    nom: '',
    prenom: '',
    adresse: '',
    date_naissance: '',
    lieu_naissance: '',
    nationalite: '',
    activite: '',
    adresse_activite: '',
    type_piece_identite: 'CNI',
    numero_piece_identite: '',
    fichier_piece_identite: null as File | null,
    date_delivrance_piece_identite: '',
    date_expiration_piece_identite: '',
    telephone: '',
});

const submit = () => {
    form.post('/garants', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Créer un Propriétaire " />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-gray-900">Créer un Propriétaire (Garant)</h1>
                <User class="h-5 w-5 text-gray-500" />
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <!-- Section 1: Informations d'identification -->
                <FormSection title="Informations d'identification" :columns="2" :show-code-icon="false">
                    <div>
                        <Label for="civilite" class="text-base font-medium text-gray-700">Civilité *</Label>
                        <select
                            id="civilite"
                            v-model="form.civilite"
                            required
                            class="mt-1.5 flex h-10 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-2 focus-visible:ring-gray-200"
                        >
                            <option value="M">M.</option>
                            <option value="Mme">Mme</option>
                            <option value="Mlle">Mlle</option>
                        </select>
                        <InputError :message="form.errors.civilite" />
                    </div>

                    <div>
                        <Label for="telephone" class="text-base font-medium text-gray-700">Numéro de téléphone *</Label>
                        <Input
                            id="telephone"
                            v-model="form.telephone"
                            type="tel"
                            required
                            placeholder="Ex: +221 77 123 45 67"
                            class="mt-1.5 h-10 rounded-lg"
                        />
                        <InputError :message="form.errors.telephone" />
                    </div>
                </FormSection>

                <!-- Section 2: Informations personnelles -->
                <FormSection title="Informations personnelles" :columns="2" :show-code-icon="false">
                    <div>
                        <Label for="nom" class="text-base font-medium text-gray-700">Nom *</Label>
                        <Input
                            id="nom"
                            v-model="form.nom"
                            type="text"
                            required
                            class="mt-1.5 h-10 rounded-lg"
                        />
                        <InputError :message="form.errors.nom" />
                    </div>

                    <div>
                        <Label for="prenom" class="text-base font-medium text-gray-700">Prénom *</Label>
                        <Input
                            id="prenom"
                            v-model="form.prenom"
                            type="text"
                            required
                            class="mt-1.5 h-10 rounded-lg"
                        />
                        <InputError :message="form.errors.prenom" />
                    </div>

                    <div>
                        <Label for="date_naissance" class="text-base font-medium text-gray-700">Date de naissance *</Label>
                        <div class="relative mt-1.5">
                            <Input
                                id="date_naissance"
                                v-model="form.date_naissance"
                                type="date"
                                required
                                class="h-10 rounded-lg pr-10"
                            />
                            <Calendar class="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 pointer-events-none" />
                        </div>
                        <InputError :message="form.errors.date_naissance" />
                    </div>

                    <div>
                        <Label for="lieu_naissance" class="text-base font-medium text-gray-700">Lieu de naissance *</Label>
                        <Input
                            id="lieu_naissance"
                            v-model="form.lieu_naissance"
                            type="text"
                            required
                            class="mt-1.5 h-10 rounded-lg"
                        />
                        <InputError :message="form.errors.lieu_naissance" />
                    </div>

                    <div>
                        <Label for="nationalite" class="text-base font-medium text-gray-700">Nationalité *</Label>
                        <Input
                            id="nationalite"
                            v-model="form.nationalite"
                            type="text"
                            required
                            placeholder="Ex: Sénégalaise"
                            class="mt-1.5 h-10 rounded-lg"
                        />
                        <InputError :message="form.errors.nationalite" />
                    </div>

                    <div class="col-span-2">
                        <Label for="adresse" class="text-base font-medium text-gray-700">Adresse de résidence *</Label>
                        <textarea
                            id="adresse"
                            v-model="form.adresse"
                            rows="3"
                            required
                            class="mt-1.5 flex w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-2 focus-visible:ring-gray-200"
                            placeholder="Adresse complète"
                        />
                        <InputError :message="form.errors.adresse" />
                    </div>
                </FormSection>

                <!-- Section 3: Informations professionnelles -->
                <FormSection title="Informations professionnelles" :columns="2" :show-code-icon="false">
                    <div>
                        <Label for="activite" class="text-base font-medium text-gray-700">Activité professionnelle</Label>
                        <Input
                            id="activite"
                            v-model="form.activite"
                            type="text"
                            class="mt-1.5 h-10 rounded-lg"
                            placeholder="Ex: Commerçant, Fonctionnaire..."
                        />
                        <InputError :message="form.errors.activite" />
                    </div>

                    <div class="col-span-2">
                        <Label for="adresse_activite" class="text-base font-medium text-gray-700">Adresse de l'activité professionnelle</Label>
                        <textarea
                            id="adresse_activite"
                            v-model="form.adresse_activite"
                            rows="3"
                            class="mt-1.5 flex w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-2 focus-visible:ring-gray-200"
                            placeholder="Adresse du lieu de travail"
                        />
                        <InputError :message="form.errors.adresse_activite" />
                    </div>
                </FormSection>

                <!-- Section 4: Pièce d'identité -->
                <FormSection title="Pièce d'identité" :columns="2" :show-code-icon="false">
                    <div>
                        <Label for="type_piece_identite" class="text-base font-medium text-gray-700">Type de pièce d'identité *</Label>
                        <select
                            id="type_piece_identite"
                            v-model="form.type_piece_identite"
                            required
                            class="mt-1.5 flex h-10 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-2 focus-visible:ring-gray-200"
                        >
                            <option value="CNI">CNI</option>
                            <option value="Passeport">Passeport</option>
                            <option value="Permis de conduire">Permis de conduire</option>
                            <option value="Autre">Autre</option>
                        </select>
                        <InputError :message="form.errors.type_piece_identite" />
                    </div>

                    <div>
                        <Label for="numero_piece_identite" class="text-base font-medium text-gray-700">Numéro de pièce d'identité *</Label>
                        <Input
                            id="numero_piece_identite"
                            v-model="form.numero_piece_identite"
                            type="text"
                            required
                            class="mt-1.5 h-10 rounded-lg"
                        />
                        <InputError :message="form.errors.numero_piece_identite" />
                    </div>

                    <div>
                        <Label for="date_delivrance_piece_identite" class="text-base font-medium text-gray-700">Date de délivrance *</Label>
                        <div class="relative mt-1.5">
                            <Input
                                id="date_delivrance_piece_identite"
                                v-model="form.date_delivrance_piece_identite"
                                type="date"
                                required
                                class="h-10 rounded-lg pr-10"
                            />
                            <Calendar class="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 pointer-events-none" />
                        </div>
                        <InputError :message="form.errors.date_delivrance_piece_identite" />
                    </div>

                    <div>
                        <Label for="date_expiration_piece_identite" class="text-base font-medium text-gray-700">Date d'expiration *</Label>
                        <div class="relative mt-1.5">
                            <Input
                                id="date_expiration_piece_identite"
                                v-model="form.date_expiration_piece_identite"
                                type="date"
                                required
                                class="h-10 rounded-lg pr-10"
                            />
                            <Calendar class="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 pointer-events-none" />
                        </div>
                        <InputError :message="form.errors.date_expiration_piece_identite" />
                    </div>

                    <div class="col-span-2">
                        <Label for="fichier_piece_identite" class="text-base font-medium text-gray-700">
                            Fichier de la pièce d'identité (facultatif)
                        </Label>
                        <div class="mt-1.5">
                            <label
                                for="fichier_piece_identite"
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 transition-colors hover:border-gray-400 hover:bg-gray-100"
                            >
                                <Upload class="mb-2 h-8 w-8 text-gray-400" />
                                <span class="text-sm font-medium text-gray-600">
                                    Cliquez pour télécharger ou glissez-déposez le fichier
                                </span>
                                <span class="mt-1 text-xs text-gray-500">
                                    Formats acceptés: images (JPG, PNG) ou PDF
                                </span>
                                <input
                                    id="fichier_piece_identite"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="hidden"
                                    @input="form.fichier_piece_identite = ($event.target as HTMLInputElement).files?.[0] || null"
                                />
                            </label>
                            <InputError :message="form.errors.fichier_piece_identite" />
                            <p v-if="form.fichier_piece_identite" class="mt-2 text-sm text-gray-600">
                                Fichier sélectionné: {{ form.fichier_piece_identite.name }}
                            </p>
                        </div>
                    </div>
                </FormSection>

                <!-- Boutons d'action -->
                <div class="flex justify-end gap-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="rounded-lg px-6 py-2"
                        @click="router.visit('/garants')"
                    >
                        Annuler
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-purple-600 px-6 py-2 text-white hover:bg-purple-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Création...' : 'Créer Le Garant' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
