<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Search, X, ChevronDown, User } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Client {
    id: number;
    matricule: string;
    nom: string;
    prenom: string;
    telephone?: string;
}

interface Props {
    clients: Client[];
    modelValue: number | string | null;
    label?: string;
    placeholder?: string;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Client',
    placeholder: 'Rechercher un client...',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const searchQuery = ref('');
const isOpen = ref(false);
const isAdvancedModalOpen = ref(false);

// Trouver le client sélectionné
const selectedClient = computed(() => {
    if (!props.modelValue) return null;
    return props.clients.find(c => c.id === Number(props.modelValue)) || null;
});

// Filtrer les clients selon la recherche
const filteredClients = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.clients.slice(0, 10); // Limiter à 10 résultats par défaut
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.clients.filter(client => {
        const matricule = client.matricule?.toLowerCase() || '';
        const nom = client.nom?.toLowerCase() || '';
        const prenom = client.prenom?.toLowerCase() || '';
        const telephone = client.telephone?.toLowerCase() || '';
        
        return matricule.includes(query) || 
               nom.includes(query) || 
               prenom.includes(query) ||
               telephone.includes(query);
    });
});

const selectClient = (client: Client) => {
    emit('update:modelValue', client.id);
    isOpen.value = false;
    isAdvancedModalOpen.value = false;
    searchQuery.value = '';
};

const clearSelection = () => {
    emit('update:modelValue', null);
    searchQuery.value = '';
};

const openAdvancedModal = () => {
    isAdvancedModalOpen.value = true;
    searchQuery.value = '';
};

watch(() => props.modelValue, () => {
    if (!props.modelValue) {
        searchQuery.value = '';
    }
});

const handleBlur = () => {
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};
</script>

<template>
    <div class="relative">
        <Label v-if="label" :for="`client-combobox-${props.modelValue}`" class="text-base font-medium text-gray-700">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </Label>
        
        <div class="relative mt-1.5">
            <Input
                :id="`client-combobox-${props.modelValue}`"
                v-model="searchQuery"
                :placeholder="selectedClient ? `${selectedClient.matricule} - ${selectedClient.prenom} ${selectedClient.nom}` : placeholder"
                type="text"
                :required="required"
                @focus="isOpen = true"
                @blur="handleBlur"
                class="w-full pr-20"
            >
                <Search class="h-4 w-4" />
            </Input>
            
            <div v-if="selectedClient" class="absolute right-10 top-1/2 -translate-y-1/2">
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="clearSelection"
                    class="h-6 w-6 p-0 text-gray-400 hover:text-gray-600"
                >
                    <X class="h-4 w-4" />
                </Button>
            </div>
            
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="isOpen = !isOpen"
                class="absolute right-2 top-1/2 -translate-y-1/2 h-6 w-6 p-0"
            >
                <ChevronDown class="h-4 w-4" />
            </Button>
            
            <!-- Dropdown de résultats -->
            <div
                v-if="isOpen && (filteredClients.length > 0 || searchQuery.trim())"
                class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
            >
                <div v-if="filteredClients.length === 0" class="p-4 text-center text-sm text-gray-500">
                    Aucun client trouvé
                </div>
                <div
                    v-for="client in filteredClients"
                    :key="client.id"
                    @click="selectClient(client)"
                    class="cursor-pointer px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': selectedClient?.id === client.id }"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900">{{ client.matricule }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-700">{{ client.prenom }} {{ client.nom }}</p>
                            <div class="mt-1 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span v-if="client.telephone">
                                    📞 {{ client.telephone }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Affichage du client sélectionné -->
        <div v-if="selectedClient" class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <User class="h-4 w-4 text-gray-400" />
                        <span class="font-semibold text-gray-900">{{ selectedClient.matricule }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-700">{{ selectedClient.prenom }} {{ selectedClient.nom }}</p>
                    <div class="mt-1 flex flex-wrap gap-3 text-xs text-gray-600">
                        <span v-if="selectedClient.telephone">
                            📞 {{ selectedClient.telephone }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bouton pour ouvrir la modal avancée -->
        <Button
            type="button"
            variant="outline"
            size="sm"
            @click="openAdvancedModal"
            class="mt-2 w-full"
        >
            <Search class="h-4 w-4 mr-2" />
            Recherche avancée
        </Button>
        
        <!-- Modal de recherche avancée -->
        <Dialog v-model:open="isAdvancedModalOpen">
            <DialogContent class="max-w-3xl max-h-[80vh] overflow-hidden flex flex-col">
                <DialogHeader>
                    <DialogTitle>Recherche avancée de client</DialogTitle>
                </DialogHeader>
                
                <div class="flex-1 overflow-y-auto">
                    <!-- Barre de recherche -->
                    <div class="mb-4">
                        <Input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="placeholder"
                            class="w-full"
                        >
                            <Search class="h-4 w-4" />
                        </Input>
                    </div>
                    
                    <!-- Liste des clients -->
                    <div class="space-y-2">
                        <div v-if="filteredClients.length === 0" class="p-8 text-center text-gray-500">
                            <User class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2">Aucun client trouvé</p>
                        </div>
                        <div
                            v-for="client in filteredClients"
                            :key="client.id"
                            @click="selectClient(client)"
                            class="cursor-pointer rounded-lg border border-gray-200 p-4 hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:hover:border-blue-600 dark:hover:bg-blue-900/20 transition-all"
                            :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900/20': selectedClient?.id === client.id }"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="rounded-full bg-blue-100 p-1.5 dark:bg-blue-900/30">
                                            <User class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <span class="font-semibold text-gray-900">{{ client.matricule }}</span>
                                    </div>
                                    <p class="mt-1 text-sm font-medium text-gray-700">{{ client.prenom }} {{ client.nom }}</p>
                                    <div class="mt-2 space-y-1 text-xs text-gray-600">
                                        <div v-if="client.telephone">
                                            <span class="font-medium">Téléphone:</span> {{ client.telephone }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500 text-center">
                        {{ filteredClients.length }} résultat(s) trouvé(s)
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

