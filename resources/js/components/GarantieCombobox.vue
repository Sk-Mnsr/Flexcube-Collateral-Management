<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Search, ChevronDown, X, Shield, User, Calendar } from 'lucide-vue-next';

interface Garantie {
    id: number;
    reference_unique: string;
    nom: string;
    valeur_reelle: number;
    montant_restant?: number;
    statut?: string;
    type_garantie?: {
        libelle: string;
    };
    garant?: {
        nom: string;
        prenom: string;
    };
    date_expiration?: string;
}

interface Props {
    garanties: Garantie[];
    modelValue?: number | string | null;
    label?: string;
    placeholder?: string;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Garantie',
    placeholder: 'Rechercher une garantie...',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const searchQuery = ref('');
const isOpen = ref(false);
const showAdvancedModal = ref(false);

// Trouver la garantie sélectionnée
const selectedGarantie = computed(() => {
    if (!props.modelValue) return null;
    return props.garanties.find(g => g.id === Number(props.modelValue)) || null;
});

// Filtrer les garanties selon la recherche
const filteredGaranties = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.garanties.slice(0, 10); // Limiter à 10 résultats par défaut
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return props.garanties.filter(garantie => {
        const ref = garantie.reference_unique?.toLowerCase() || '';
        const nom = garantie.nom?.toLowerCase() || '';
        const garantNom = garantie.garant?.nom?.toLowerCase() || '';
        const garantPrenom = garantie.garant?.prenom?.toLowerCase() || '';
        const type = garantie.type_garantie?.libelle?.toLowerCase() || '';
        
        return ref.includes(query) || 
               nom.includes(query) || 
               garantNom.includes(query) || 
               garantPrenom.includes(query) ||
               type.includes(query);
    }).slice(0, 20); // Limiter à 20 résultats
});

const selectGarantie = (garantie: Garantie) => {
    emit('update:modelValue', garantie.id);
    searchQuery.value = '';
    isOpen.value = false;
    showAdvancedModal.value = false;
};

const clearSelection = () => {
    emit('update:modelValue', null);
    searchQuery.value = '';
};

const openAdvancedModal = () => {
    showAdvancedModal.value = true;
    searchQuery.value = '';
};

watch(() => props.modelValue, () => {
    if (!props.modelValue) {
        searchQuery.value = '';
    }
});
</script>

<template>
    <div class="space-y-2">
        <Label v-if="label" :for="`garantie-combobox-${props.modelValue}`" class="text-base font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </Label>

        <!-- Champ de recherche avec autocomplétion -->
        <div class="relative">
            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <Input
                    :id="`garantie-combobox-${props.modelValue}`"
                    v-model="searchQuery"
                    :placeholder="placeholder"
                    class="pl-10 pr-10"
                    @focus="isOpen = true"
                    @blur="setTimeout(() => isOpen = false, 200)"
                />
                <div v-if="selectedGarantie" class="absolute right-10 top-1/2 -translate-y-1/2">
                    <button
                        type="button"
                        @click="clearSelection"
                        class="rounded-full p-1 text-gray-400 hover:text-gray-600"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <button
                    type="button"
                    @click="openAdvancedModal"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-gray-400 hover:text-gray-600"
                    title="Recherche avancée"
                >
                    <ChevronDown class="h-4 w-4" />
                </button>
            </div>

            <!-- Dropdown de résultats -->
            <div
                v-if="isOpen && (filteredGaranties.length > 0 || searchQuery.trim())"
                class="absolute z-50 mt-1 max-h-80 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
            >
                <div v-if="filteredGaranties.length === 0" class="p-4 text-center text-sm text-gray-500">
                    Aucune garantie trouvée
                </div>
                <div
                    v-for="garantie in filteredGaranties"
                    :key="garantie.id"
                    @click="selectGarantie(garantie)"
                    class="cursor-pointer border-b border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': selectedGarantie?.id === garantie.id }"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <Shield class="h-4 w-4 text-blue-600" />
                                <span class="font-semibold text-gray-900">{{ garantie.reference_unique }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-700">{{ garantie.nom }}</p>
                            <div class="mt-1 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span v-if="garantie.type_garantie">{{ garantie.type_garantie.libelle }}</span>
                                <span v-if="garantie.garant">
                                    <User class="inline h-3 w-3" />
                                    {{ garantie.garant.prenom }} {{ garantie.garant.nom }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ garantie.valeur_reelle.toLocaleString('fr-FR') }} FCFA
                            </div>
                            <div v-if="garantie.montant_restant !== undefined" class="text-xs text-gray-500">
                                Restant: {{ garantie.montant_restant.toLocaleString('fr-FR') }} FCFA
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Affichage de la garantie sélectionnée -->
        <div v-if="selectedGarantie" class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <Shield class="h-5 w-5 text-blue-600" />
                        <span class="font-semibold text-gray-900">{{ selectedGarantie.reference_unique }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-700">{{ selectedGarantie.nom }}</p>
                    <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-600">
                        <span v-if="selectedGarantie.type_garantie">
                            Type: {{ selectedGarantie.type_garantie.libelle }}
                        </span>
                        <span v-if="selectedGarantie.garant">
                            Propriétaire du bien (Garant): {{ selectedGarantie.garant.prenom }} {{ selectedGarantie.garant.nom }}
                        </span>
                        <span v-if="selectedGarantie.montant_restant !== undefined">
                            Restant: {{ selectedGarantie.montant_restant.toLocaleString('fr-FR') }} FCFA
                        </span>
                    </div>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="clearSelection"
                    class="ml-2"
                >
                    <X class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Modal de recherche avancée -->
        <Dialog v-model:open="showAdvancedModal">
            <DialogContent class="max-w-4xl max-h-[80vh]">
                <DialogHeader>
                    <DialogTitle>Recherche avancée de garantie</DialogTitle>
                </DialogHeader>
                
                <div class="space-y-4">
                    <!-- Barre de recherche -->
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <Input
                            v-model="searchQuery"
                            :placeholder="placeholder"
                            class="pl-10"
                        />
                    </div>

                    <!-- Liste des garanties -->
                    <div class="max-h-96 overflow-auto rounded-md border border-gray-200 dark:border-gray-700">
                        <div v-if="filteredGaranties.length === 0" class="p-8 text-center text-gray-500">
                            <Shield class="mx-auto h-12 w-12 text-gray-300" />
                            <p class="mt-2">Aucune garantie trouvée</p>
                            <p class="mt-1 text-sm">Essayez de modifier vos critères de recherche</p>
                        </div>
                        <div
                            v-for="garantie in filteredGaranties"
                            :key="garantie.id"
                            @click="selectGarantie(garantie)"
                            class="cursor-pointer border-b border-gray-100 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            :class="{ 'bg-blue-50 dark:bg-blue-900/20': selectedGarantie?.id === garantie.id }"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <Shield class="h-5 w-5 text-blue-600" />
                                        <span class="font-semibold text-gray-900">{{ garantie.reference_unique }}</span>
                                        <span
                                            v-if="garantie.statut"
                                            class="rounded-full px-2 py-0.5 text-xs"
                                            :class="{
                                                'bg-green-100 text-green-800': garantie.statut === 'normal',
                                                'bg-red-100 text-red-800': garantie.statut === 'contentieux',
                                                'bg-orange-100 text-orange-800': garantie.statut === 'realisation',
                                            }"
                                        >
                                            {{ garantie.statut }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm font-medium text-gray-700">{{ garantie.nom }}</p>
                                    <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-600">
                                        <div v-if="garantie.type_garantie">
                                            <span class="font-medium">Type:</span> {{ garantie.type_garantie.libelle }}
                                        </div>
                                        <div v-if="garantie.garant">
                                            <span class="font-medium">Propriétaire du bien (Garant):</span> {{ garantie.garant.prenom }} {{ garantie.garant.nom }}
                                        </div>
                                        <div v-if="garantie.date_expiration">
                                            <Calendar class="inline h-3 w-3" />
                                            <span class="font-medium">Expiration:</span> {{ new Date(garantie.date_expiration).toLocaleDateString('fr-FR') }}
                                        </div>
                                        <div v-if="garantie.montant_restant !== undefined">
                                            <span class="font-medium">Montant restant:</span> {{ garantie.montant_restant.toLocaleString('fr-FR') }} FCFA
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 text-right">
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ garantie.valeur_reelle.toLocaleString('fr-FR') }} FCFA
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">Valeur pondérée</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations sur les résultats -->
                    <div class="text-sm text-gray-500">
                        {{ filteredGaranties.length }} résultat(s) trouvé(s)
                        <span v-if="searchQuery.trim()">pour "{{ searchQuery }}"</span>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

