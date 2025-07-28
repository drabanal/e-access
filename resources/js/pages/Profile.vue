<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
import { onMounted, ref } from 'vue';
import { useInitials } from '@/composables/useInitials';

const props = defineProps({ user: Object });

const { getInitials } = useInitials();

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
        <div class="flex h-full flex-1 flex-col rounded-xl p-4">
            <div class="grid grid-cols-3 gap-6">
                <Card>
                    <template #content>
                        <div class="relative min-h-[20vh] flex justify-center">
                            <div v-if="profile?.employee.empgender == 'MALE'" class="w-50 h-50 rounded-full bg-cyan-600 flex items-center justify-center text-white font-bold">
                                <span class="text-8xl">
                                    {{ getInitials(profile?.employee.empgname + ' ' + profile?.employee.empfname) }}
                                </span>
                            </div>
                            <div v-else class="w-50 h-50 rounded-full bg-pink-600 flex items-center justify-center text-white font-bold">
                                <span class="text-8xl">
                                    {{ getInitials(profile?.employee.empgname + ' ' + profile?.employee.empfname) }}
                                </span>
                            </div>
                        </div>
                        <Divider />
                        <div class="relative flex min-h-[20vh] flex-col">
                            <div class="w-full mb-4 uppercase">
                                <p>
                                    <span class="font-black text-xs">ID #</span>
                                </p>
                                <p>
                                    <span class="text-lg" v-html="profile?.userid"></span>
                                </p>
                            </div>
                            <div class="w-full mb-4 uppercase">
                                <p>
                                    <span class="font-black text-xs">Name</span>
                                </p>
                                <p>
                                    <span class="text-lg" v-html="profile?.employee.empgname + ' ' + profile?.employee.empfname"></span>
                                </p>
                            </div>
                            <div class="w-full mb-4 uppercase">
                                <p>
                                    <span class="font-black text-xs">Position</span>
                                </p>
                                <p>
                                    <span class="text-lg" v-html="profile?.employee.emppos"></span>
                                </p>
                            </div>
                            <div class="w-full mb-4 uppercase">
                                <p>
                                    <span class="font-black text-xs">Department</span>
                                </p>
                                <p>
                                    <span class="text-lg" v-html="profile?.employee.empdept"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>
                <Card class="col-span-2">
                    <template #content>
                        <Fieldset legend="CONTACT DETAILS">
                            <div class="relative flex min-h-[20vh] flex-col">
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Permanent Address</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.emphomeadd"></span>
                                    </p>
                                </div>
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Current Address</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.emptempadd"></span>
                                    </p>
                                </div>
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Phone #</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.emptel"></span>
                                    </p>
                                </div>
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Contact Person</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.empcontactperson ? profile?.employee.empcontactperson + ' / ' + profile?.employee.empcontactpersontel : '--'"></span>
                                    </p>
                                </div>
                            </div>
                        </Fieldset>
                        <br>
                        <Fieldset legend="EMPLOYMENT DETAILS" class="mt-5">
                            <div class="relative flex min-h-[20vh] flex-col">
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Status</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.empstatus"></span>
                                    </p>
                                </div>
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Date Hired</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.formatted_hired_date"></span>
                                    </p>
                                </div>
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Probationary Period</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.formatted_probationary_date_from + ' - ' + profile?.employee.formatted_probationary_date_to"></span>
                                    </p>
                                </div>
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Regularization Date</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.formatted_regularization_date"></span>
                                    </p>
                                </div>
                                <div class="w-full mb-4 uppercase">
                                    <p>
                                        <span class="font-black text-xs">Resignation Date</span>
                                    </p>
                                    <p>
                                        <span class="text-lg" v-html="profile?.employee.empdateofresig ? profile?.employee.empdateofresig : '--'"></span>
                                    </p>
                                </div>
                            </div>
                        </Fieldset>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
