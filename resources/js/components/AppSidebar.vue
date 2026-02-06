<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LayoutGrid, UserCog, Shield, User, Settings, FileText, Users, CreditCard, Link2 } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const auth = computed(() => page.props.auth as any);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // SuperAdmin et Admin voient les mêmes onglets (sauf Configuration réservé à SuperAdmin)
    if (auth.value?.isSuperAdmin || auth.value?.isAdmin) {
        items.push(
            {
                title: 'Garants',
                icon: User,
                items: [
                    {
                        title: 'Nouveau',
                        href: '/garants/create',
                    },
                    {
                        title: 'Historique',
                        href: '/garants',
                    },
                ],
            },
            {
                title: 'Garanties',
                icon: Shield,
                items: [
                    {
                        title: 'Nouveau',
                        href: '/garanties/create',
                    },
                    {
                        title: 'Historique',
                        href: '/garanties',
                    },
                ],
            },
            {
                title: 'Contrats de prêts',
                href: '/contrats-prets',
                icon: FileText,
            },
            {
                title: 'Clients',
                href: '/clients',
                icon: Users,
            },
            {
                title: 'Liaison',
                href: '/liaisons',
                icon: Link2,
            }
        );
        
        // Admin voit "Types de garanties" dans la liste principale (lecture seule)
        if (auth.value?.isAdmin) {
            items.push(
                {
                    title: 'Types de garanties',
                    href: '/types-garanties',
                    icon: Settings,
                }
            );
        }
        
        // Seul SuperAdmin a accès à l'onglet Configuration
        if (auth.value?.isSuperAdmin) {
            items.push(
                {
                    title: 'Configuration',
                    icon: Settings,
                    separator: true,
                    items: [
                        {
                            title: 'Utilisateurs',
                            items: [
                                {
                                    title: 'Créer un nouvel utilisateur',
                                    href: '/users/create',
                                },
                                {
                                    title: 'Liste des utilisateurs',
                                    href: '/users',
                                },
                            ],
                        },
                        {
                            title: 'Types de garanties',
                            href: '/types-garanties',
                        },
                    ],
                }
            );
        }
    }
    // Chargé d'Affaires : lecture seule
    else if (auth.value?.isChargeAffaires) {
        items.push(
            {
                title: 'Garants',
                icon: User,
                href: '/garants',
            },
            {
                title: 'Garanties',
                icon: Shield,
                href: '/garanties',
            },
            {
                title: 'Types de garanties',
                href: '/types-garanties',
                icon: Settings,
            },
            {
                title: 'Contrats de prêts',
                href: '/contrats-prets',
                icon: FileText,
            },
            {
                title: 'Liaison',
                href: '/liaisons',
                icon: Link2,
            }
        );
    }
    // Juridique : lecture seule + changement de statut
    else if (auth.value?.isJuridique) {
        items.push(
            {
                title: 'Garants',
                icon: User,
                href: '/garants',
            },
            {
                title: 'Garanties',
                icon: Shield,
                href: '/garanties',
            },
            {
                title: 'Types de garanties',
                href: '/types-garanties',
                icon: Settings,
            },
            {
                title: 'Contrats de prêts',
                href: '/contrats-prets',
                icon: FileText,
            },
            {
                title: 'Liaison',
                href: '/liaisons',
                icon: Link2,
            }
        );
    }

    return items;
});


</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="pb-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <Link :href="dashboard()" class="flex items-center p-2">
                        <AppLogo />
                    </Link>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="pt-4">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
