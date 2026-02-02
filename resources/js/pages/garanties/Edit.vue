<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import FormSection from '@/components/FormSection.vue';
import { Shield, Plus, X, Upload, FileText } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    garantie: {
        id: number;
        reference_unique: string;
        nom: string;
        description?: string;
        emplacement?: string;
        type_garantie_id: number;
        garant_id: number;
        client_id?: number | null;
        valeur: number;
        valeur_reelle: number;
        date_creation: string;
        date_expiration?: string;
        matricules_clients?: Array<{ id: number }>;
        client?: {
            id: number;
            matricule: string;
            nom: string;
            prenom: string;
            telephone?: string;
        } | null;
        documentations?: Array<{
            id: number;
            type_documentation: string;
            nom: string;
            description?: string;
            valeur?: number;
            chemin_fichier?: string;
        }>;
    };
    typesGaranties: Array<{ id: number; libelle: string; code: string; decote_pourcentage: number; ponderation_pourcentage: number }>;
    garants: Array<{ id: number; nom: string; prenom: string; date_naissance: string }>;
    clients?: Array<{ id: number; matricule: string; nom: string; prenom: string; telephone?: string }>;
    matriculesClients: Array<{ id: number; matricule: string; nom: string; nature_juridique: string; secteur_activite?: string }>;
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
    { title: 'Garanties', href: '/garanties' },
    { title: props.garantie.reference_unique, href: `/garanties/${props.garantie.id}` },
    { title: 'Modifier', href: '#' },
];

// Préparer les documentations existantes pour le formulaire
const existingDocumentations = props.garantie.documentations?.map(doc => ({
    id: doc.id,
    type_documentation: doc.type_documentation as 'texte' | 'fichier',
    nom: doc.nom,
    description: doc.description || '',
    valeur: doc.valeur || undefined,
    fichier: null as File | null,
    chemin_fichier: doc.chemin_fichier,
    isExisting: true, // Pour distinguer les existantes des nouvelles
})) || [];

const form = useForm({
    nom: props.garantie.nom,
    description: props.garantie.description || '',
    emplacement: props.garantie.emplacement || '',
    type_garantie_id: props.garantie.type_garantie_id.toString(),
    garant_id: props.garantie.garant_id.toString(),
    valeur: props.garantie.valeur.toString(),
    date_creation: formatDateForInput(props.garantie.date_creation),
    date_expiration: formatDateForInput(props.garantie.date_expiration),
    client_id: props.garantie.client_id?.toString() || '',
    matricules_clients: props.garantie.matricules_clients?.map(c => c.id) || [],
    documentations: existingDocumentations as Array<{
        id?: number;
        type_documentation: 'texte' | 'fichier';
        nom: string;
        description?: string;
        valeur?: number;
        fichier?: File | null;
        chemin_fichier?: string;
        isExisting?: boolean;
    }>,
});

const selectedType = computed(() => {
    return props.typesGaranties.find(t => t.id.toString() === form.type_garantie_id.toString());
});

const valeurReelle = computed(() => {
    if (!form.valeur || !selectedType.value) return 0;
    const valeur = parseFloat(form.valeur);
    return valeur * (selectedType.value.ponderation_pourcentage / 100);
});

const addDocumentation = () => {
    form.documentations.push({
        type_documentation: 'fichier',
        nom: '',
        description: '',
        fichier: null,
        isExisting: false,
    });
};

const removeDocumentation = (index: number) => {
    form.documentations.splice(index, 1);
};

const handleFileChange = (index: number, event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.documentations[index].fichier = target.files[0];
        if (!form.documentations[index].nom) {
            form.documentations[index].nom = target.files[0].name;
        }
    }
};

const submit = () => {
    // Vérifier que les documents avec type 'fichier' ont bien un fichier (sauf ceux existants)
    const hasInvalidDocs = form.documentations.some((doc) => {
        if (doc.type_documentation === 'fichier') {
            // Si c'est un document existant, pas besoin de fichier
            if (doc.isExisting && doc.chemin_fichier) {
                return false;
            }
            // Si c'est nouveau, il faut un fichier
            return !doc.fichier;
        }
        if (doc.type_documentation === 'texte' && !doc.description) {
            return true;
        }
        return false;
    });

    if (hasInvalidDocs) {
        alert('Veuillez compléter tous les documents : les nouveaux fichiers doivent être sélectionnés et les documents texte doivent avoir un contenu.');
        return;
    }

    // Préparer les documentations pour l'envoi (inclure les IDs pour les existantes)
    const documentationsToSend = form.documentations.map((doc) => {
        const docData: any = {
            type_documentation: doc.type_documentation,
            nom: doc.nom,
            description: doc.description || null,
            valeur: doc.valeur || null,
        };
        
        // Si c'est un document existant, inclure l'ID et le chemin
        if (doc.isExisting && doc.id) {
            docData.id = doc.id;
            if (doc.chemin_fichier) {
                docData.chemin_fichier = doc.chemin_fichier;
            }
        }
        
        // Ajouter le fichier si présent
        if (doc.fichier) {
            docData.fichier = doc.fichier;
        }
        
        return docData;
    });

    // Créer un nouveau formulaire avec les données préparées
    const submitData = {
        ...form.data(),
        documentations: documentationsToSend,
    };

    // Utiliser form.transform pour modifier les données avant l'envoi
    form.transform((data) => ({
        ...data,
        documentations: documentationsToSend,
    })).post(`/garanties/${props.garantie.id}`, {
        preserveScroll: true,
        forceFormData: true,
        _method: 'PUT',
    });
};
</script>

<template>
    <Head title="Modifier une garantie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-gray-900">Modifier la garantie {{ props.garantie.reference_unique }}</h1>
                <Shield class="h-5 w-5 text-gray-500" />
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <FormSection title="Informations de la garantie" :columns="2">
                    <div>
                        <Label for="nom" class="text-base font-medium text-gray-700">Nom *</Label>
                        <Input
                            id="nom"
                            v-model="form.nom"
                            type="text"
                            required
                            placeholder="Ex: Hypothèque maison"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.nom" />
                    </div>

                    <div>
                        <Label for="type_garantie_id" class="text-base font-medium text-gray-700">Type de garantie *</Label>
                        <select
                            id="type_garantie_id"
                            v-model="form.type_garantie_id"
                            required
                            class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        >
                            <option value="">Sélectionner un type</option>
                            <option v-for="type in typesGaranties" :key="type.id" :value="type.id">
                                {{ type.libelle }} ({{ type.code }})
                            </option>
                        </select>
                        <InputError :message="form.errors.type_garantie_id" />
                    </div>

                    <div>
                        <Label for="garant_id" class="text-base font-medium text-gray-700">Propriétaire du bien (Garant) *</Label>
                        <select
                            id="garant_id"
                            v-model="form.garant_id"
                            required
                            class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        >
                            <option value="">Sélectionner un propriétaire du bien (Garant)</option>
                            <option v-for="garant in garants" :key="garant.id" :value="garant.id">
                                {{ garant.prenom }} {{ garant.nom }} ({{ new Date(garant.date_naissance).toLocaleDateString('fr-FR') }})
                            </option>
                        </select>
                        <InputError :message="form.errors.garant_id" />
                        <Link href="/garants/create" class="mt-1 text-sm text-blue-600 hover:underline">
                            Créer un nouveau propriétaire du bien (Garant)
                        </Link>
                    </div>

                    <div>
                        <Label for="valeur" class="text-base font-medium text-gray-700">Valeur (FCFA) *</Label>
                        <Input
                            id="valeur"
                            v-model.number="form.valeur"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            placeholder="0.00"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.valeur" />
                        <p v-if="form.valeur" class="mt-1 text-sm text-gray-600">
                            Saisi : <span class="font-medium">{{ parseFloat(form.valeur.toString() || '0').toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} FCFA</span>
                        </p>
                        <p v-if="selectedType" class="mt-1 text-xs text-gray-500">
                            Nouvelle valeur réelle estimée : {{ valeurReelle.toLocaleString('fr-FR') }} FCFA
                            (Décote: {{ selectedType.decote_pourcentage }}%, Pondération: {{ selectedType.ponderation_pourcentage }}%)
                        </p>
                    </div>

                    <div>
                        <Label for="emplacement" class="text-base font-medium text-gray-700">Emplacement</Label>
                        <Input
                            id="emplacement"
                            v-model="form.emplacement"
                            type="text"
                            placeholder="Ex: Dakar, Plateau"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.emplacement" />
                    </div>

                    <div>
                        <Label for="date_creation" class="text-base font-medium text-gray-700">Date de création *</Label>
                        <Input
                            id="date_creation"
                            v-model="form.date_creation"
                            type="date"
                            required
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.date_creation" />
                    </div>

                    <div>
                        <Label for="date_expiration" class="text-base font-medium text-gray-700">Date d'expiration</Label>
                        <Input
                            id="date_expiration"
                            v-model="form.date_expiration"
                            type="date"
                            class="mt-1.5"
                        />
                        <InputError :message="form.errors.date_expiration" />
                    </div>

                    <div class="col-span-2">
                        <Label for="description" class="text-base font-medium text-gray-700">Description</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1.5 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                            placeholder="Description de la garantie..."
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </FormSection>

                <FormSection title="Liaison avec le client" :columns="1">
                    <div v-if="props.clients && props.clients.length > 0">
                        <Label for="client_id" class="text-base font-medium text-gray-700">Client associé</Label>
                        <p class="mb-2 text-sm text-gray-500">Sélectionnez le client qui pourra utiliser cette garantie</p>
                        <select
                            id="client_id"
                            v-model="form.client_id"
                            class="mt-1.5 flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-base text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-1 focus-visible:ring-gray-400"
                        >
                            <option value="">Aucun client</option>
                            <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                {{ client.matricule }} - {{ client.prenom }} {{ client.nom }}
                                <span v-if="client.telephone"> ({{ client.telephone }})</span>
                            </option>
                        </select>
                        <InputError :message="form.errors.client_id" />
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        Aucun client disponible.
                    </div>
                </FormSection>

                <FormSection title="Liaison des matricules clients" :columns="1">
                    <div>
                        <Label class="text-base font-medium text-gray-700">Matricules clients autorisés</Label>
                        <p class="mb-2 text-sm text-gray-500">Sélectionnez les clients qui pourront utiliser cette garantie</p>
                        <div class="max-h-48 space-y-2 overflow-y-auto rounded-md border border-gray-300 p-3">
                            <label
                                v-for="client in matriculesClients"
                                :key="client.id"
                                class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded"
                            >
                                <input
                                    type="checkbox"
                                    :value="client.id"
                                    v-model="form.matricules_clients"
                                    class="rounded border-gray-300"
                                />
                                <div class="flex-1">
                                    <span class="font-medium">{{ client.matricule }}</span>
                                    <span class="ml-2 text-sm text-gray-600">{{ client.nom }}</span>
                                    <span class="ml-2 text-xs text-gray-500">({{ client.nature_juridique }})</span>
                                </div>
                            </label>
                        </div>
                        <InputError :message="form.errors.matricules_clients" />
                    </div>
                </FormSection>

                <FormSection title="Documentation de la garantie" :columns="1" :show-code-icon="false">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <Label class="text-base font-medium text-gray-700">Documents associés</Label>
                                <p class="mt-1 text-sm text-gray-500">Ajoutez ou modifiez les documents liés à cette garantie (contrats, actes, certificats, etc.)</p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                @click="addDocumentation"
                                class="rounded-lg"
                            >
                                <Plus class="h-4 w-4 mr-2" />
                                Ajouter un document
                            </Button>
                        </div>

                        <div v-if="form.documentations.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                            <FileText class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">Aucun document ajouté</p>
                            <p class="text-xs text-gray-400">Cliquez sur "Ajouter un document" pour commencer</p>
                        </div>

                        <div
                            v-for="(doc, index) in form.documentations"
                            :key="doc.isExisting ? `existing-${doc.id}` : `new-${index}`"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-medium text-gray-900">Document {{ index + 1 }}</h4>
                                    <span v-if="doc.isExisting" class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                                        Existant
                                    </span>
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeDocumentation(index)"
                                    class="text-red-600 hover:text-red-700"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label :for="`doc-type-${index}`" class="text-sm font-medium text-gray-700">Type *</Label>
                                    <select
                                        :id="`doc-type-${index}`"
                                        v-model="doc.type_documentation"
                                        class="mt-1.5 flex h-9 w-full rounded-lg border border-gray-300 bg-white px-3 py-1 text-sm text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-2 focus-visible:ring-gray-200"
                                    >
                                        <option value="fichier">Fichier</option>
                                        <option value="texte">Texte</option>
                                    </select>
                                </div>

                                <div>
                                    <Label :for="`doc-nom-${index}`" class="text-sm font-medium text-gray-700">Nom du document *</Label>
                                    <Input
                                        :id="`doc-nom-${index}`"
                                        v-model="doc.nom"
                                        type="text"
                                        required
                                        placeholder="Ex: Contrat d'hypothèque"
                                        class="mt-1.5 h-9 rounded-lg text-sm"
                                    />
                                </div>

                                <div v-if="doc.type_documentation === 'fichier'" class="md:col-span-2">
                                    <Label :for="`doc-fichier-${index}`" class="text-sm font-medium text-gray-700">Fichier *</Label>
                                    <div class="mt-1.5">
                                        <div v-if="doc.isExisting && doc.chemin_fichier" class="mb-2 rounded-lg border border-gray-300 bg-white p-3">
                                            <p class="text-sm text-gray-600">
                                                Fichier actuel: <a :href="`/storage/${doc.chemin_fichier}`" target="_blank" class="text-blue-600 hover:underline">{{ doc.nom }}</a>
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">Sélectionnez un nouveau fichier pour le remplacer</p>
                                        </div>
                                        <label
                                            :for="`doc-fichier-${index}`"
                                            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white p-4 transition-colors hover:border-gray-400 hover:bg-gray-50"
                                        >
                                            <Upload class="mb-2 h-6 w-6 text-gray-400" />
                                            <span class="text-xs font-medium text-gray-600">
                                                Cliquez pour télécharger ou glissez-déposez
                                            </span>
                                            <span class="mt-1 text-xs text-gray-500">
                                                Formats acceptés: PDF, JPG, PNG (max 10MB)
                                            </span>
                                            <input
                                                :id="`doc-fichier-${index}`"
                                                type="file"
                                                accept=".pdf,.jpg,.jpeg,.png"
                                                class="hidden"
                                                @change="handleFileChange(index, $event)"
                                            />
                                        </label>
                                        <p v-if="doc.fichier" class="mt-2 text-xs text-gray-600">
                                            Nouveau fichier sélectionné: <span class="font-medium">{{ doc.fichier.name }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div v-else class="md:col-span-2">
                                    <Label :for="`doc-description-${index}`" class="text-sm font-medium text-gray-700">Contenu textuel *</Label>
                                    <textarea
                                        :id="`doc-description-${index}`"
                                        v-model="doc.description"
                                        rows="3"
                                        required
                                        placeholder="Saisissez le contenu du document..."
                                        class="mt-1.5 flex w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition-[color,box-shadow] outline-none focus-visible:border-gray-400 focus-visible:ring-2 focus-visible:ring-gray-200"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <Label :for="`doc-valeur-${index}`" class="text-sm font-medium text-gray-700">Valeur (optionnel)</Label>
                                    <Input
                                        :id="`doc-valeur-${index}`"
                                        v-model.number="doc.valeur"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="mt-1.5 h-9 rounded-lg text-sm"
                                    />
                                    <p v-if="doc.valeur" class="mt-1 text-xs text-gray-600">
                                        Saisi : <span class="font-medium">{{ (doc.valeur || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} FCFA</span>
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">Valeur associée au document si applicable</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </FormSection>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="router.visit(`/garanties/${props.garantie.id}`)">
                        Annuler
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Mise à jour...' : 'Mettre à jour la garantie' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

