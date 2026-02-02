<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Shield, AlertTriangle, Unlink, FileWarning, Share2, Calendar, TrendingUp, CheckCircle, DollarSign, Handshake, Gavel, Clock } from 'lucide-vue-next';

interface Props {
    statistiques: {
        totalGaranties: number;
        garantiesExpirees: number;
        garantiesNonAffectees: number;
        pretsNonCouverts: number;
        garantiesPartagees: number;
        garantiesLevees: number;
        garantiesSoldees: number;
        garantiesCedees: number;
        garantiesAdjuguees: number;
        garantiesEnAttenteMainLevee: number;
        derniereRevue: string;
        prochaineRevue: string;
        joursAvantRevue: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <Calendar class="h-4 w-4" />
                    <span>Prochaine revue trimestrielle : {{ props.statistiques.prochaineRevue }}</span>
                </div>
            </div>

            <!-- Cartes de statistiques -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Total des garanties -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total des garanties</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ props.statistiques.totalGaranties.toLocaleString('fr-FR') }}
                            </p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                            <Shield class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <Link href="/garanties" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir toutes les garanties →
                    </Link>
                </div>

                <!-- Garanties expirées -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Garanties expirées</p>
                            <p class="mt-2 text-3xl font-bold" :class="props.statistiques.garantiesExpirees > 0 ? 'text-red-600' : 'text-gray-900'">
                                {{ props.statistiques.garantiesExpirees.toLocaleString('fr-FR') }}
                            </p>
                        </div>
                        <div class="rounded-full bg-red-100 p-3 dark:bg-red-900">
                            <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                    <Link href="/garanties?filter=expirees" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les garanties expirées →
                    </Link>
                </div>

                <!-- Garanties non affectées -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Garanties non affectées</p>
                            <p class="mt-2 text-3xl font-bold" :class="props.statistiques.garantiesNonAffectees > 0 ? 'text-orange-600' : 'text-gray-900'">
                                {{ props.statistiques.garantiesNonAffectees.toLocaleString('fr-FR') }}
                            </p>
                        </div>
                        <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900">
                            <Unlink class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                    <Link href="/garanties?filter=non-affectees" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les garanties non affectées →
                    </Link>
                </div>

                <!-- Prêts non couverts -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Prêts non couverts</p>
                            <p class="mt-2 text-3xl font-bold" :class="props.statistiques.pretsNonCouverts > 0 ? 'text-red-600' : 'text-gray-900'">
                                {{ props.statistiques.pretsNonCouverts.toLocaleString('fr-FR') }}
                            </p>
                        </div>
                        <div class="rounded-full bg-red-100 p-3 dark:bg-red-900">
                            <FileWarning class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                    <Link href="/contrats-prets?filter=non-couverts" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les prêts non couverts →
                    </Link>
                </div>

                <!-- Garanties partagées -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Garanties partagées</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ props.statistiques.garantiesPartagees.toLocaleString('fr-FR') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">Entre plusieurs prêts</p>
                        </div>
                        <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900">
                            <Share2 class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                    <Link href="/liaisons" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les liaisons →
                    </Link>
                </div>

                <!-- Garanties levées -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Garanties levées</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ props.statistiques.garantiesLevees.toLocaleString('fr-FR') }}
                            </p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                            <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <Link href="/garanties?filter=main_leve" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les garanties levées →
                    </Link>
                </div>

                <!-- Garanties soldées -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Garanties soldées</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ props.statistiques.garantiesSoldees.toLocaleString('fr-FR') }}
                            </p>
                        </div>
                        <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-900">
                            <DollarSign class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                    <Link href="/garanties?filter=vendu" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les garanties soldées →
                    </Link>
                </div>

                <!-- Garanties cédées -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Garanties cédées</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ props.statistiques.garantiesCedees.toLocaleString('fr-FR') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">À un tiers</p>
                        </div>
                        <div class="rounded-full bg-indigo-100 p-3 dark:bg-indigo-900">
                            <Handshake class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                    <Link href="/garanties?filter=mutation_tiers" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les garanties cédées →
                    </Link>
                </div>

                <!-- Garanties adjugées -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Garanties adjugées</p>
                            <p class="mt-2 text-3xl font-bold text-orange-600">
                                {{ props.statistiques.garantiesAdjuguees.toLocaleString('fr-FR') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">En réalisation</p>
                        </div>
                        <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900">
                            <Gavel class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                    <Link href="/garanties?filter=realisation" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les garanties adjugées →
                    </Link>
                </div>

                <!-- Garanties en attente de main levée -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En attente de main levée</p>
                            <p class="mt-2 text-3xl font-bold" :class="props.statistiques.garantiesEnAttenteMainLevee > 0 ? 'text-yellow-600' : 'text-gray-900'">
                                {{ props.statistiques.garantiesEnAttenteMainLevee.toLocaleString('fr-FR') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">Prêtes pour main levée</p>
                        </div>
                        <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900">
                            <Clock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </div>
                    <Link href="/garanties?filter=attente_main_levee" class="mt-4 block text-sm text-blue-600 hover:underline">
                        Voir les garanties en attente →
                    </Link>
                </div>

                <!-- Revie trimestrielle -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Revue trimestrielle</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                {{ props.statistiques.joursAvantRevue }} jours
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Prochaine : {{ props.statistiques.prochaineRevue }}
                            </p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                            <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-gray-500">
                        Dernière revue : {{ props.statistiques.derniereRevue }}
                    </p>
                </div>
            </div>

            <!-- Section d'information sur la revue trimestrielle -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-6 dark:border-blue-800 dark:bg-blue-900/20">
                <div class="flex items-start gap-4">
                    <Calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                            Revue trimestrielle du paramétrage
                        </h3>
                        <p class="mt-2 text-sm text-blue-800 dark:text-blue-200">
                            Une revue trimestrielle est prévue pour ajuster le paramétrage selon le retour d'expérience.
                            La prochaine revue aura lieu le <strong>{{ props.statistiques.prochaineRevue }}</strong>.
                        </p>
                        <p class="mt-2 text-xs text-blue-700 dark:text-blue-300">
                            Cette revue permet d'analyser les performances du système, d'identifier les améliorations nécessaires
                            et d'ajuster les paramètres de gestion des garanties et des prêts selon le retour d'expérience.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
