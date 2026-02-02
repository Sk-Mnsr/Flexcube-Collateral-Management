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

    // IT (Admin) voit tout
    if (auth.value?.isAdmin || auth.value?.isIt) {
        items.push(
            {
                title: 'Utilisateurs',
                icon: UserCog,
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
    }
    // Analyste Risque : peut créer et modifier
    else if (auth.value?.isAnalysteRisque) {
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
    // Autres rôles (anciens rôles pour compatibilité)
    else {
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
                title: 'Types de garanties',
                href: '/types-garanties',
                icon: Settings,
            },
            {
                title: 'Contrats de prêts',
                href: '/contrats-prets',
                icon: FileText,
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
