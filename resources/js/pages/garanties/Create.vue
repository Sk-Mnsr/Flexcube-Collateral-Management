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
import ClientCombobox from '@/components/ClientCombobox.vue';

interface Props {
    typesGaranties: Array<{
        id: number;
        libelle: string;
        code: string;
        decote_pourcentage: number;
        ponderation_pourcentage: number
    }>;
    garants: Array<{ id: number; nom: string; prenom: string; date_naissance: string }>;
    matriculesClients: Array<{ id: number; matricule: string; nom: string; nature_juridique: string; secteur_activite?: string }>;
    clients?: Array<{ id: number; matricule: string; nom: string; prenom: string; telephone?: string }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Garanties', href: '/garanties' },
    { title: 'Créer une garantie', href: '#' },
];

const form = useForm({
    nom: '',
    description: '',
    emplacement: '',
    type_garantie_id: '',
    garant_id: '',
    valeur: '',
    date_creation: new Date().toISOString().split('T')[0],
    date_expiration: '',
    matricules_clients: [] as number[],
    client_id: '' as string | number,
    documentations: [] as Array<{
        type_documentation: 'texte' | 'fichier';
        nom: string;
        description?: string;
        valeur?: number;
        fichier?: File;
    }>,
});

const selectedType = computed(() => {
    return props.typesGaranties.find(t => t.id.toString() === form.type_garantie_id.toString());
});

const valeurReelle = computed(() => {
    if (!form.valeur || !selectedType.value) return 0;
    const valeur = parseFloat(form.valeur.toString());
    if (isNaN(valeur)) return 0;
    // Calcul: valeur * (ponderation_pourcentage / 100)
    return valeur * (selectedType.value.ponderation_pourcentage / 100);
});

const decoteMontant = computed(() => {
    if (!form.valeur || !selectedType.value) return 0;
    const valeur = parseFloat(form.valeur.toString());
    if (isNaN(valeur)) return 0;
    // Calcul: valeur * (decote_pourcentage / 100)
    return valeur * (selectedType.value.decote_pourcentage / 100);
});

const addDocumentation = () => {
    form.documentations.push({
        type_documentation: 'fichier',
        nom: '',
        description: '',
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
    // Vérifier que les documents avec type 'fichier' ont bien un fichier
    const hasInvalidDocs = form.documentations.some((doc) => {
        if (doc.type_documentation === 'fichier' && !doc.fichier) {
            return true;
        }
        if (doc.type_documentation === 'texte' && !doc.description) {
            return true;
        }
        return false;
    });

    if (hasInvalidDocs) {
        alert('Veuillez compléter tous les documents : les fichiers doivent être sélectionnés et les documents texte doivent avoir un contenu.');
        return;
    }

    form.post('/garanties', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Créer une garantie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-gray-900">Créer une garantie</h1>
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
                        <Label for="valeur" class="text-base font-medium text-gray-700">Valeur de la garantie inscrite (FCFA) *</Label>
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
                    </div>

                    <div>
                        <Label class="text-base font-medium text-gray-700">Valeur pondérée (FCFA)</Label>
                        <div class="mt-1.5 rounded-lg border-2 border-blue-200 bg-blue-50 p-3">
                            <div class="text-2xl font-bold text-blue-700">
                                {{ valeurReelle.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} FCFA
                            </div>
                            <div v-if="selectedType && form.valeur" class="mt-2 space-y-1 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Valeur de la garantie inscrite :</span>
                                    <span class="font-medium">{{ parseFloat(form.valeur.toString() || '0').toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} FCFA</span>
                                </div>
                                <div v-if="decoteMontant > 0" class="flex justify-between text-red-600">
                                    <span>Décote ({{ selectedType.decote_pourcentage }}%) :</span>
                                    <span class="font-medium">- {{ decoteMontant.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} FCFA</span>
                                </div>
                                <div class="flex justify-between text-green-600">
                                    <span>Pondération ({{ selectedType.ponderation_pourcentage }}%) :</span>
                                    <span class="font-medium">{{ valeurReelle.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} FCFA</span>
                                </div>
                            </div>
                            <p v-else-if="!selectedType" class="mt-1 text-sm text-gray-500 italic">
                                Sélectionnez un type de garantie pour voir la valeur pondérée
                            </p>
                            <p v-else class="mt-1 text-sm text-gray-500 italic">
                                Saisissez une valeur pour voir le calcul
                            </p>
                        </div>
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
                    <!-- Client -->
                    <div v-if="props.clients && props.clients.length > 0">
                        <p class="mb-2 text-sm text-gray-500">Sélectionnez le client qui pourra utiliser cette garantie</p>
                        <ClientCombobox
                            :clients="props.clients"
                            :model-value="form.client_id ? Number(form.client_id) : null"
                            label="Client associé"
                            placeholder="Rechercher par matricule, nom, prénom ou téléphone..."
                            @update:model-value="form.client_id = $event ? $event.toString() : ''"
                        />
                        <InputError :message="form.errors.client_id" />
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        Aucun client disponible. Créez d'abord des clients dans le système.
                    </div>
                </FormSection>

                <FormSection title="Documentation de la garantie" :columns="1" :show-code-icon="false">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <Label class="text-base font-medium text-gray-700">Documents associés</Label>
                                <p class="mt-1 text-sm text-gray-500">Ajoutez les documents liés à cette garantie (contrats, actes, certificats, etc.)</p>
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
                            :key="index"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <h4 class="font-medium text-gray-900">Document {{ index + 1 }}</h4>
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
                                            Fichier sélectionné: <span class="font-medium">{{ doc.fichier.name }}</span>
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
                    <Button type="button" variant="outline" @click="router.visit('/garanties')">
                        Annuler
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Création...' : 'Créer la garantie' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

