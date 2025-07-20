<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({ user: Object });

const breadcrumbs = ref([
    { label: 'Profile' }
])

const profile = ref()

onMounted(() => {
    profile.value = props.user
})
</script>

<template>
    <Head title="Profile" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <tbody>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">ID #</th>
                            <td class="px-6 py-4"><span v-html="profile?.userid"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">NAME</th>
                            <td class="px-6 py-4"><span v-html="profile?.full_name"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">PERMANENT ADDRESS</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.emphomeadd"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">CURRENT ADDRESS</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.emptempadd"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">BIRTHDATE</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empbdate"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">BIRTHPLACE</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empbplace"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">CONTACT #</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.emptel"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">CONTACT PERSON</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empcontactperson + ' / ' + profile?.employee.empcontactpersontel"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">POSITION / DEPARTMENT</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.emppos + ' / ' + profile?.employee.empdept"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">DATE HIRED</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empdatehired"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">PROBATIONARY PERIOD</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empprobfrom"></span><strong> to </strong><span v-html="profile?.employee.empprobto"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">REGULARIZATION DATE</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empdateofreg"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">RESIGNATION DATE</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empdateofresig"></span></td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th class="px-6 py-3 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">EMPLOYMENT STATUS</th>
                            <td class="px-6 py-4"><span v-html="profile?.employee.empstatus"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
