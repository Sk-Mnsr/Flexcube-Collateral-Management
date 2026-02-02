<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import FormSection from '@/components/FormSection.vue';
import { User } from 'lucide-vue-next';

interface Props {
    garant: {
        id: number;
        civilite: string;
        nom: string;
        prenom: string;
        adresse?: string;
        date_naissance: string;
        lieu_naissance?: string;
        nationalite?: string;
        activite?: string;
        adresse_activite?: string;
        type_piece_identite: string;
        numero_piece_identite: string;
        fichier_piece_identite?: string;
        date_delivrance_piece_identite?: string;
        date_expiration_piece_identite?: string;
        telephone?: string;
    };
}

const props = defineProps<Props>();

// Fonction pour convertir une date au format YYYY-MM-DD pour les champs input type="date"
const formatDateForInput = (dateString: string | null | undefined): string => {
    if (!dateString) return '';
    // Si la date contient un espace ou "T", c'est un datetime, on extrait juste la partie date
    if (dateString.includes(' ') || dateString.includes('T')) {
        return dateString.split(' ')[0].split('T')[0];
    }
    // Sinon, on retourne la date telle quelle
    return dateString;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Garants', href: '/garants' },
    { title: `${props.garant.prenom} ${props.garant.nom}`, href: `/garants/${props.garant.id}` },
    { title: 'Modifier', href: '#' },
];

const form = useForm({
    civilite: props.garant.civilite,
    nom: props.garant.nom,
    prenom: props.garant.prenom,
    adresse: props.garant.adresse || '',
    date_naissance: formatDateForInput(props.garant.date_naissance),
    lieu_naissance: props.garant.lieu_naissance || '',
    nationalite: props.garant.nationalite || '',
    activite: props.garant.activite || '',
    adresse_activite: props.garant.adresse_activite || '',
    type_piece_identite: props.garant.type_piece_identite,
    numero_piece_identite: props.garant.numero_piece_identite,
    fichier_piece_identite: null as File | null,
    date_delivrance_piece_identite: formatDateForInput(props.garant.date_delivrance_piece_identite),
    date_expiration_piece_identite: formatDateForInput(props.garant.date_expiration_piece_identite),
    telephone: props.garant.telephone || '',
});

const submit = () => {
    form.post(`/garants/${props.garant.id}`, {
        preserveScroll: true,
        forceFormData: true,
        _method: 'PUT',
    });
};
</script>

<template>
    <Head title="Modifier un garant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-gray-900">Modifier le garant</h1>
                <User class="h-5 w-5 text-gray-500" />
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <FormSection title="Informations personnelles" :columns="2">
                    <div>
                        <Label for="civilite" class="text-base font-medium text-gray-700">Civilité *</Label>
                        <select
                            id="civilite"
                            v-model="form.civilite"
                            required
                            class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        >
                            <option value="M">M.</option>
                            <option value="Mme">Mme</option>
                            <option value="Mlle">Mlle</option>
                        </select>
                        <InputError :message="form.errors.civilite" />
                    </div>

                    <div>
                        <Label for="prenom" class="text-base font-medium text-gray-700">Prénom *</Label>
                        <Input
                            id="prenom"
                            v-model="form.prenom"
                            type="text"
                            required
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.prenom" />
                    </div>

                    <div>
                        <Label for="nom" class="text-base font-medium text-gray-700">Nom *</Label>
                        <Input
                            id="nom"
                            v-model="form.nom"
                            type="text"
                            required
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.nom" />
                    </div>

                    <div>
                        <Label for="date_naissance" class="text-base font-medium text-gray-700">Date de naissance *</Label>
                        <Input
                            id="date_naissance"
                            v-model="form.date_naissance"
                            type="date"
                            required
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.date_naissance" />
                    </div>

                    <div>
                        <Label for="lieu_naissance" class="text-base font-medium text-gray-700">Lieu de naissance</Label>
                        <Input
                            id="lieu_naissance"
                            v-model="form.lieu_naissance"
                            type="text"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.lieu_naissance" />
                    </div>

                    <div>
                        <Label for="nationalite" class="text-base font-medium text-gray-700">Nationalité</Label>
                        <Input
                            id="nationalite"
                            v-model="form.nationalite"
                            type="text"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.nationalite" />
                    </div>

                    <div>
                        <Label for="telephone" class="text-base font-medium text-gray-700">Téléphone</Label>
                        <Input
                            id="telephone"
                            v-model="form.telephone"
                            type="tel"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.telephone" />
                    </div>

                    <div>
                        <Label for="activite" class="text-base font-medium text-gray-700">Activité</Label>
                        <Input
                            id="activite"
                            v-model="form.activite"
                            type="text"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.activite" />
                    </div>

                    <div class="col-span-2">
                        <Label for="adresse_activite" class="text-base font-medium text-gray-700">Adresse de l'activité</Label>
                        <textarea
                            id="adresse_activite"
                            v-model="form.adresse_activite"
                            rows="3"
                            class="mt-1.5 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        />
                        <InputError :message="form.errors.adresse_activite" />
                    </div>

                    <div class="col-span-2">
                        <Label for="adresse" class="text-base font-medium text-gray-700">Adresse</Label>
                        <textarea
                            id="adresse"
                            v-model="form.adresse"
                            rows="3"
                            class="mt-1.5 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        />
                        <InputError :message="form.errors.adresse" />
                    </div>
                </FormSection>

                <FormSection title="Pièce d'identité" :columns="2">
                    <div>
                        <Label for="type_piece_identite" class="text-base font-medium text-gray-700">Type de pièce *</Label>
                        <select
                            id="type_piece_identite"
                            v-model="form.type_piece_identite"
                            required
                            class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        >
                            <option value="CNI">CNI</option>
                            <option value="Passeport">Passeport</option>
                            <option value="Permis de conduire">Permis de conduire</option>
                            <option value="Autre">Autre</option>
                        </select>
                        <InputError :message="form.errors.type_piece_identite" />
                    </div>

                    <div>
                        <Label for="numero_piece_identite" class="text-base font-medium text-gray-700">Numéro de pièce *</Label>
                        <Input
                            id="numero_piece_identite"
                            v-model="form.numero_piece_identite"
                            type="text"
                            required
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.numero_piece_identite" />
                    </div>

                    <div>
                        <Label for="date_delivrance_piece_identite" class="text-base font-medium text-gray-700">Date de délivrance</Label>
                        <Input
                            id="date_delivrance_piece_identite"
                            v-model="form.date_delivrance_piece_identite"
                            type="date"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.date_delivrance_piece_identite" />
                    </div>

                    <div>
                        <Label for="date_expiration_piece_identite" class="text-base font-medium text-gray-700">Date d'expiration</Label>
                        <Input
                            id="date_expiration_piece_identite"
                            v-model="form.date_expiration_piece_identite"
                            type="date"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.date_expiration_piece_identite" />
                    </div>

                    <div>
                        <Label for="fichier_piece_identite" class="text-base font-medium text-gray-700">Fichier de la pièce</Label>
                        <Input
                            id="fichier_piece_identite"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            @input="form.fichier_piece_identite = $event.target.files[0]"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.fichier_piece_identite" />
                        <p class="mt-1 text-xs text-gray-500">Formats acceptés: PDF, JPG, PNG (max 5MB)</p>
                        <p v-if="props.garant.fichier_piece_identite" class="mt-1 text-xs text-gray-500">
                            Fichier actuel: <a :href="`/storage/${props.garant.fichier_piece_identite}`" target="_blank" class="text-blue-600 hover:underline">Voir</a>
                        </p>
                    </div>
                </FormSection>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="router.visit(`/garants/${props.garant.id}`)">
                        Annuler
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Mise à jour...' : 'Mettre à jour le garant' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

