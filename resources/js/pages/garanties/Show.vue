<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Shield, Edit, ArrowLeft, TrendingUp, TrendingDown, FileText, Download, File, Paperclip, Plus, X, Eye } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Props {
    garantie: {
        id: number;
        reference_unique: string;
        nom: string;
        description?: string;
        emplacement?: string;
        statut: string;
        valeur: number;
        valeur_reelle: number;
        date_creation: string;
        date_expiration?: string;
        type_garantie?: {
            id: number;
            libelle: string;
            code: string;
        };
        garant?: {
            id: number;
            nom: string;
            prenom: string;
        };
        client?: {
            id: number;
            matricule: string;
            nom: string;
            prenom: string;
            telephone?: string;
        } | null;
        matricules_clients?: Array<{
            id: number;
            matricule: string;
            nom: string;
            nature_juridique: string;
        }>;
        contrats_pret?: Array<any>;
        documentations?: Array<{
            id: number;
            type_documentation: string;
            nom: string;
            description?: string;
            valeur?: number;
            chemin_fichier?: string;
            created_at: string;
        }>;
    };
    montantUtilise: number;
    montantRestant: number;
    pourcentageUtilisation: number;
    disponiblePourPret: boolean;
    statutsPossibles: string[];
    historiques?: Array<{
        id: number;
        ancien_statut: string;
        nouveau_statut: string;
        commentaire?: string;
        document_justificatif?: string | string[]; // Peut être un JSON string ou un tableau
        created_at: string;
        utilisateur?: {
            id: number;
            name: string;
            email: string;
        };
    }>;
}

const props = defineProps<Props>();

const documentsJustificatifs = ref<Array<{ id: number; file: File | null }>>([
    { id: 1, file: null }
]);

const statutForm = useForm({
    nouveau_statut: '',
    commentaire: '',
    documents_justificatifs: [] as File[],
});

const addDocumentField = () => {
    documentsJustificatifs.value.push({
        id: Date.now(),
        file: null
    });
};

const removeDocumentField = (index: number) => {
    if (documentsJustificatifs.value.length > 1) {
        documentsJustificatifs.value.splice(index, 1);
    }
};

const updateDocumentsArray = () => {
    statutForm.documents_justificatifs = documentsJustificatifs.value
        .map(doc => doc.file)
        .filter((file): file is File => file !== null);
};

const getStatutBadge = (statut: string) => {
    const badges: Record<string, string> = {
        normal: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        contentieux: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        realisation: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        mutation_tiers: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        mutation_cofina: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        vendu: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        main_leve: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        dation: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    };
    return badges[statut] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

const getStatutLabel = (statut: string) => {
    const labels: Record<string, string> = {
        normal: 'Normal',
        contentieux: 'Contentieux',
        realisation: 'Réalisation',
        mutation_tiers: 'Mutation à un tiers',
        mutation_cofina: 'Mutation au nom de Cofina',
        vendu: 'Vendu',
        main_leve: 'Main levé',
        dation: 'Dation',
    };
    return labels[statut] || statut;
};

const parseDocuments = (doc: string | string[] | undefined): string[] => {
    if (!doc) return [];
    if (Array.isArray(doc)) return doc;
    if (typeof doc === 'string') {
        try {
            const parsed = JSON.parse(doc);
            return Array.isArray(parsed) ? parsed : [doc];
        } catch {
            return [doc];
        }
    }
    return [];
};

const changerStatut = () => {
    // Mettre à jour le tableau de fichiers avant la soumission
    updateDocumentsArray();
    
    statutForm.post(`/garanties/${props.garantie.id}/changer-statut`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            statutForm.reset();
            documentsJustificatifs.value = [{ id: 1, file: null }];
        },
    });
};

// État pour la modal de prévisualisation
const previewModal = ref<{
    isOpen: boolean;
    url: string;
    fileName: string;
    fileType: string;
}>({
    isOpen: false,
    url: '',
    fileName: '',
    fileType: '',
});

// Fonction pour déterminer le type de fichier
const getFileType = (fileName: string, cheminFichier?: string): string => {
    // Combiner le nom et le chemin pour une meilleure détection
    const fileToCheck = (cheminFichier || fileName).toLowerCase();
    const fileNameLower = fileName.toLowerCase();
    
    // Extraire l'extension du chemin (plus fiable) ou du nom
    const parts = fileToCheck.split('.');
    let extension = parts.length > 1 ? parts.pop()?.toLowerCase() || '' : '';
    
    // Si pas d'extension dans le chemin, essayer le nom
    if (!extension || extension.length > 5) {
        const nameParts = fileNameLower.split('.');
        extension = nameParts.length > 1 ? nameParts.pop()?.toLowerCase() || '' : '';
    }
    
    // Vérifier les extensions standard
    if (['pdf'].includes(extension)) return 'pdf';
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension)) return 'image';
    if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension)) return 'office';
    
    // Si pas d'extension trouvée, vérifier si le nom ou le chemin contient des mots-clés
    if (fileToCheck.includes('pdf') || fileNameLower.includes('pdf')) return 'pdf';
    if (fileToCheck.includes('jpg') || fileToCheck.includes('jpeg') || fileToCheck.includes('png')) return 'image';
    if (fileToCheck.includes('doc') || fileToCheck.includes('xls') || fileToCheck.includes('ppt')) return 'office';
    
    // Par défaut, essayer de prévisualiser comme PDF si le nom suggère un document
    // (beaucoup de fichiers PDF ont des noms sans extension)
    if (fileNameLower.length > 0 && !fileNameLower.includes('.')) {
        // Si le nom se termine par "pdf" ou contient des mots suggérant un document
        if (fileNameLower.endsWith('pdf') || fileNameLower.includes('document') || fileNameLower.includes('titre') || fileNameLower.includes('contrat')) {
            return 'pdf';
        }
    }
    
    return 'other';
};

// Fonction pour ouvrir la prévisualisation
const openPreview = (cheminFichier: string, nom: string) => {
    const fileUrl = `/storage/${cheminFichier}`;
    const fileType = getFileType(nom, cheminFichier);
    
    previewModal.value = {
        isOpen: true,
        url: fileUrl,
        fileName: nom,
        fileType: fileType,
    };
};

// Fonction pour fermer la modal
const closePreview = () => {
    previewModal.value.isOpen = false;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Garanties', href: '/garanties' },
    { title: props.garantie.reference_unique, href: '#' },
];
</script>

<template>
    <Head :title="`Garantie: ${props.garantie.reference_unique}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold">{{ props.garantie.reference_unique }}</h1>
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium',
                            getStatutBadge(props.garantie.statut),
                        ]"
                    >
                        {{ getStatutLabel(props.garantie.statut) }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/garanties/${props.garantie.id}/edit`">
                        <Button variant="outline">
                            <Edit class="h-4 w-4 mr-2" />
                            Modifier
                        </Button>
                    </Link>
                    <Link href="/garanties">
                        <Button variant="outline">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Retour
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="text-sm text-gray-500">Valeur pondérée</div>
                    <div class="mt-1 text-2xl font-bold">{{ props.garantie.valeur_reelle.toLocaleString('fr-FR') }} FCFA</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="text-sm text-gray-500">Montant utilisé</div>
                    <div class="mt-1 text-2xl font-bold text-orange-600">{{ montantUtilise.toLocaleString('fr-FR') }} FCFA</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="text-sm text-gray-500">Stock de Garantie</div>
                    <div class="mt-1 text-2xl font-bold text-green-600">{{ montantRestant.toLocaleString('fr-FR') }} FCFA</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="text-sm text-gray-500">Taux d'utilisation</div>
                    <div class="mt-1 text-2xl font-bold" :class="pourcentageUtilisation > 80 ? 'text-red-600' : pourcentageUtilisation > 50 ? 'text-yellow-600' : 'text-green-600'">
                        {{ pourcentageUtilisation.toFixed(1) }}%
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                        <div
                            class="h-2 rounded-full transition-all"
                            :class="pourcentageUtilisation > 80 ? 'bg-red-500' : pourcentageUtilisation > 50 ? 'bg-yellow-500' : 'bg-green-500'"
                            :style="{ width: `${Math.min(100, pourcentageUtilisation)}%` }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Informations principales -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Informations de la garantie</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Nom</div>
                            <div class="mt-1 text-base">{{ props.garantie.nom }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Type</div>
                            <div class="mt-1 text-base">{{ props.garantie.type_garantie?.libelle }} ({{ props.garantie.type_garantie?.code }})</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Propriétaire du bien (Garant)</div>
                            <div class="mt-1 text-base">
                                <Link :href="`/garants/${props.garantie.garant?.id}`" class="text-blue-600 hover:underline">
                                    {{ props.garantie.garant?.prenom }} {{ props.garantie.garant?.nom }}
                                </Link>
                            </div>
                        </div>
                        <div v-if="props.garantie.client">
                            <div class="text-sm font-medium text-gray-500">Client associé</div>
                            <div class="mt-1 text-base">
                                <Link :href="`/clients/${props.garantie.client.id}`" class="text-blue-600 hover:underline">
                                    {{ props.garantie.client.matricule }} - {{ props.garantie.client.prenom }} {{ props.garantie.client.nom }}
                                    <span v-if="props.garantie.client.telephone"> ({{ props.garantie.client.telephone }})</span>
                                </Link>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Valeur de la garantie inscrite</div>
                            <div class="mt-1 text-base">{{ props.garantie.valeur.toLocaleString('fr-FR') }} FCFA</div>
                        </div>
                        <div v-if="props.garantie.emplacement">
                            <div class="text-sm font-medium text-gray-500">Emplacement</div>
                            <div class="mt-1 text-base">{{ props.garantie.emplacement }}</div>
                        </div>
                        <div v-if="props.garantie.description">
                            <div class="text-sm font-medium text-gray-500">Description</div>
                            <div class="mt-1 text-base">{{ props.garantie.description }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">Dates et statut</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Date de création</div>
                            <div class="mt-1 text-base">{{ new Date(props.garantie.date_creation).toLocaleDateString('fr-FR') }}</div>
                        </div>
                        <div v-if="props.garantie.date_expiration">
                            <div class="text-sm font-medium text-gray-500">Date d'expiration</div>
                            <div class="mt-1 text-base">{{ new Date(props.garantie.date_expiration).toLocaleDateString('fr-FR') }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Disponibilité</div>
                            <div class="mt-1">
                                <span :class="disponiblePourPret ? 'text-green-600' : 'text-red-600'">
                                    {{ disponiblePourPret ? 'Disponible pour nouveau prêt' : 'Non disponible' }}
                                </span>
                            </div>
                        </div>

                        <!-- Changement de statut (uniquement pour le rôle juridique) -->
                        <div v-if="statutsPossibles.length > 0 && $page.props.auth.isJuridique">
                            <div class="text-sm font-medium text-gray-500 mb-2">Changer le statut</div>
                            <form @submit.prevent="changerStatut" class="space-y-3">
                                <select
                                    v-model="statutForm.nouveau_statut"
                                    required
                                    class="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm text-gray-900 shadow-sm"
                                >
                                    <option value="">Sélectionner un statut</option>
                                    <option v-for="statut in statutsPossibles" :key="statut" :value="statut">
                                        {{ getStatutLabel(statut) }}
                                    </option>
                                </select>
                                <textarea
                                    v-model="statutForm.commentaire"
                                    placeholder="Commentaire (optionnel)"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-400 focus:ring-1 focus:ring-gray-400"
                                ></textarea>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <Label class="text-sm font-medium text-gray-700">
                                            Documents justificatifs (optionnel)
                                        </Label>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="addDocumentField"
                                            class="h-7 text-xs"
                                        >
                                            <Plus class="h-3 w-3 mr-1" />
                                            Ajouter un document
                                        </Button>
                                    </div>
                                    <div class="space-y-2">
                                        <div
                                            v-for="(doc, index) in documentsJustificatifs"
                                            :key="doc.id"
                                            class="flex items-start gap-2"
                                        >
                                            <div class="flex-1">
                                                <Input
                                                    :id="`document_justificatif_${doc.id}`"
                                                    type="file"
                                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                    @input="doc.file = $event.target.files[0]; updateDocumentsArray()"
                                                    class="mt-1.5"
                                                />
                                                <p v-if="doc.file" class="mt-1 text-xs text-green-600">
                                                    Fichier sélectionné: {{ doc.file.name }} ({{ (doc.file.size / 1024 / 1024).toFixed(2) }} MB)
                                                </p>
                                            </div>
                                            <Button
                                                v-if="documentsJustificatifs.length > 1"
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                @click="removeDocumentField(index); updateDocumentsArray()"
                                                class="mt-1.5 h-9 text-red-600 hover:text-red-700 hover:bg-red-50"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Formats acceptés: PDF, JPG, PNG, DOC, DOCX (max 10MB par fichier)
                                    </p>
                                </div>
                                <Button type="submit" :disabled="statutForm.processing" size="sm" class="w-full">
                                    Changer le statut
                                </Button>
                            </form>
                        </div>
                        <div v-else-if="statutsPossibles.length > 0" class="rounded-md border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-800 dark:bg-yellow-900/20">
                            <div class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong>Note :</strong> Seul le service juridique peut changer le statut des garanties.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matricules clients -->
            <div v-if="props.garantie.matricules_clients && props.garantie.matricules_clients.length > 0" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold">Matricules clients autorisés</h2>
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="client in props.garantie.matricules_clients" :key="client.id" class="rounded-md border border-gray-200 p-3">
                        <div class="font-medium">{{ client.matricule }}</div>
                        <div class="text-sm text-gray-600">{{ client.nom }}</div>
                        <div class="text-xs text-gray-500">{{ client.nature_juridique }}</div>
                    </div>
                </div>
            </div>

            <!-- Documentation de la garantie -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Documentation de la garantie</h2>
                    <span v-if="props.garantie.documentations && props.garantie.documentations.length > 0" class="rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                        {{ props.garantie.documentations.length }} document(s)
                    </span>
                </div>
                
                <div v-if="props.garantie.documentations && props.garantie.documentations.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div v-for="doc in props.garantie.documentations" :key="doc.id" class="rounded-lg border border-gray-200 bg-gray-50 p-4 hover:border-gray-300 hover:shadow-sm transition-all dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <div class="mt-1 flex-shrink-0">
                                    <div class="rounded-full p-2" :class="doc.type_documentation === 'texte' ? 'bg-blue-100 dark:bg-blue-900' : 'bg-green-100 dark:bg-green-900'">
                                        <FileText v-if="doc.type_documentation === 'texte'" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        <File v-else class="h-5 w-5 text-green-600 dark:text-green-400" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ doc.nom }}</div>
                                    <div v-if="doc.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ doc.description }}
                                    </div>
                                    <div v-if="doc.valeur" class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Valeur : <span class="text-green-600 dark:text-green-400">{{ doc.valeur.toLocaleString('fr-FR') }} FCFA</span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="doc.type_documentation === 'texte' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'">
                                            {{ doc.type_documentation === 'texte' ? 'Texte' : 'Fichier' }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ new Date(doc.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="doc.type_documentation === 'fichier' && doc.chemin_fichier" class="flex-shrink-0 flex items-center gap-2">
                                <Button 
                                    variant="outline" 
                                    size="sm" 
                                    class="h-8"
                                    @click="openPreview(doc.chemin_fichier, doc.nom)"
                                >
                                    <Eye class="h-4 w-4 mr-1" />
                                    Voir
                                </Button>
                                <a 
                                    :href="`/storage/${doc.chemin_fichier}`" 
                                    target="_blank" 
                                    :download="doc.nom"
                                    class="inline-flex items-center"
                                >
                                    <Button variant="outline" size="sm" class="h-8">
                                        <Download class="h-4 w-4 mr-1" />
                                        Télécharger
                                    </Button>
                                </a>
                            </div>
                            <div v-else-if="doc.type_documentation === 'texte'" class="flex-shrink-0">
                                <Button variant="outline" size="sm" class="h-8" disabled>
                                    <FileText class="h-4 w-4 mr-1" />
                                    Texte
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <FileText class="mx-auto h-12 w-12 text-gray-300" />
                    <p class="mt-2 text-sm text-gray-500">Aucune documentation disponible pour cette garantie.</p>
                </div>
            </div>

            <!-- Historique des changements de statut -->
            <div v-if="props.historiques && props.historiques.length > 0" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold">Historique des changements de statut</h2>
                <div class="space-y-4">
                    <div v-for="historique in props.historiques" :key="historique.id" class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="['rounded-full px-2 py-1 text-xs font-medium', getStatutBadge(historique.ancien_statut)]">
                                        {{ getStatutLabel(historique.ancien_statut) }}
                                    </span>
                                    <span class="text-gray-400">→</span>
                                    <span :class="['rounded-full px-2 py-1 text-xs font-medium', getStatutBadge(historique.nouveau_statut)]">
                                        {{ getStatutLabel(historique.nouveau_statut) }}
                                    </span>
                                </div>
                                <div v-if="historique.commentaire" class="mt-2 text-sm text-gray-600 italic">
                                    "{{ historique.commentaire }}"
                                </div>
                                <div v-if="historique.document_justificatif" class="mt-2">
                                    <div class="text-xs font-medium text-gray-600 mb-1">
                                        Documents justificatifs ({{ parseDocuments(historique.document_justificatif).length }}) :
                                    </div>
                                    <div class="space-y-2">
                                        <div
                                            v-for="(doc, docIndex) in parseDocuments(historique.document_justificatif)"
                                            :key="docIndex"
                                            class="flex items-center gap-2"
                                        >
                                            <Paperclip class="h-4 w-4 text-gray-500" />
                                            <span class="text-sm text-gray-700 dark:text-gray-300 flex-1">Document {{ docIndex + 1 }}</span>
                                            <div class="flex items-center gap-1">
                                                <Button 
                                                    variant="ghost" 
                                                    size="sm" 
                                                    class="h-7 text-xs"
                                                    @click="openPreview(doc, `Document ${docIndex + 1}`)"
                                                >
                                                    <Eye class="h-3 w-3 mr-1" />
                                                    Voir
                                                </Button>
                                                <a
                                                    :href="`/storage/${doc}`"
                                                    target="_blank"
                                                    :download="`Document ${docIndex + 1}`"
                                                >
                                                    <Button variant="ghost" size="sm" class="h-7 text-xs">
                                                        <Download class="h-3 w-3 mr-1" />
                                                        Télécharger
                                                    </Button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    Par {{ historique.utilisateur?.name || 'Utilisateur inconnu' }}
                                    le {{ new Date(historique.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold">Historique des changements de statut</h2>
                <p class="text-gray-500 text-sm">Aucun historique disponible.</p>
            </div>
        </div>

        <!-- Modal de prévisualisation des documents -->
        <div 
            v-if="previewModal.isOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4"
            @click.self="closePreview"
        >
            <div class="relative w-full max-w-6xl max-h-[90vh] bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <!-- En-tête de la modal -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <File class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">
                            {{ previewModal.fileName }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <a 
                            :href="previewModal.url" 
                            target="_blank" 
                            :download="previewModal.fileName"
                            class="inline-flex items-center"
                        >
                            <Button variant="outline" size="sm">
                                <Download class="h-4 w-4 mr-1" />
                                Télécharger
                            </Button>
                        </a>
                        <Button variant="ghost" size="sm" @click="closePreview">
                            <X class="h-5 w-5" />
                        </Button>
                    </div>
                </div>

                <!-- Contenu de la modal -->
                <div class="p-4 overflow-auto" style="max-height: calc(90vh - 80px);">
                    <!-- PDF -->
                    <div v-if="previewModal.fileType === 'pdf'" class="w-full">
                        <iframe 
                            :src="previewModal.url" 
                            class="w-full h-[calc(90vh-120px)] border border-gray-200 dark:border-gray-700 rounded"
                            frameborder="0"
                        ></iframe>
                    </div>

                    <!-- Images -->
                    <div v-else-if="previewModal.fileType === 'image'" class="flex justify-center">
                        <img 
                            :src="previewModal.url" 
                            :alt="previewModal.fileName"
                            class="max-w-full max-h-[calc(90vh-120px)] object-contain rounded"
                        />
                    </div>

                    <!-- Fichiers Office et autres -->
                    <div v-else class="flex flex-col items-center justify-center py-12">
                        <FileText class="h-16 w-16 text-gray-400 mb-4" />
                        <p class="text-gray-600 dark:text-gray-400 mb-2">
                            Ce type de fichier ne peut pas être prévisualisé directement.
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">
                            Veuillez le télécharger pour l'ouvrir.
                        </p>
                        <a 
                            :href="previewModal.url" 
                            target="_blank" 
                            :download="previewModal.fileName"
                        >
                            <Button>
                                <Download class="h-4 w-4 mr-2" />
                                Télécharger le fichier
                            </Button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

