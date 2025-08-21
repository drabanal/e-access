<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
// import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
// import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import Menu from 'primevue/menu';
import { onMounted, ref } from 'vue';
import { type User } from '@/types';

const menuItems = ref([
    {
        label: 'Profile',
        icon: 'pi pi-user',
        url: '/profile'
    },
    {
        label: 'Calendar',
        icon: 'pi pi-calendar',
        url: '/calendar'
    },
    {
        label: 'Leaves',
        items: [
            {
                label: 'New',
                icon: 'pi pi-plus',
                url: '/leaves/add'
            },
            {
                label: 'Approved',
                icon: 'pi pi-calendar-plus',
                url: '/leaves/approved'
            },
            {
                label: 'Pending',
                icon: 'pi pi-calendar-clock',
                url: '/leaves/pending'
            },
            {
                label: 'Disapproved',
                icon: 'pi pi-calendar-times',
                url: '/leaves/disapproved'
            },
            {
                label: 'Cancelled',
                icon: 'pi pi-calendar-minus',
                url: '/leaves/cancelled'
            },
        ]
    }
])

const page = usePage();
const user = page.props.auth.user as User;
onMounted(() => {
    if (user?.userlevel !== 3) {
        menuItems.value.push({
            label: 'Team',
            items: [
                {
                    label: 'Members',
                    icon: 'pi pi-users',
                    url: '/team/members'
                },
                {
                    label: 'Pending Leaves',
                    icon: 'pi pi-calendar-clock',
                    url: '/team/pending'
                }
            ]
        });

    }
})
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('profile')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <Menu :model="menuItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
