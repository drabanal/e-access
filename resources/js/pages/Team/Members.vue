<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { FilterMatchMode } from '@primevue/core/api';
import Credits from '../Leaves/Credits.vue';
import { useToast } from "primevue/usetoast";
import { router } from '@inertiajs/vue3';

const props = defineProps({ leaveTypes: Array });

const breadcrumbs = ref([
    { label: 'Team' },
    { label: 'Members' }
])

const toast = useToast();

const fetchingMembers = ref(true);
const members = ref([]);
const perPage = ref(30);
const perPageOptions = ref([30, 50, 100]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const fetchMembers = () => {
    fetchingMembers.value = true
    axios.get('/employees/list').then((response) => {

        members.value = response.data;
        fetchingMembers.value = false;
    })
    .catch((error) => {
        toast.add({ severity: 'error', summary: 'Something went wrong!', detail: error.response.data.message, life: 5000 });
    });
}

const showMemberInfoDialog = ref(false);
const selectedMember = ref();
const dialogHeader = ref('');
const leaveTypeFilter = ref({ name: 'All Leaves', id: 0 });
const dateFilter = ref();
const leaveRequests = ref();
const fetchingRequests = ref(false);
const selectedTab = ref('');
const tabsMenu = ref([
    { 
        label: 'Approved Leaves', 
        icon: 'pi pi-calendar-plus',
        command: () => {
            selectedTab.value = 'approved';
            leaveTypeFilter.value = { name: 'All Leaves', id: 0 };
            fetchMembersLeaveRequests();
        }
    },
    { 
        label: 'Pending Leaves', 
        icon: 'pi pi-calendar-clock',
        command: () => {
            selectedTab.value = 'pending';
            leaveTypeFilter.value = { name: 'All Leaves', id: 0 };
            fetchMembersLeaveRequests();
        }
    },
    { 
        label: 'Disapproved Leaves', 
        icon: 'pi pi-calendar-times',
        command: () => {
            selectedTab.value = 'disapproved';
            leaveTypeFilter.value = { name: 'All Leaves', id: 0 };
            fetchMembersLeaveRequests();
        }
    },
    { 
        label: 'Cancelled Leaves', 
        icon: 'pi pi-calendar-minus',
        command: () => {
            selectedTab.value = 'cancelled';
            leaveTypeFilter.value = { name: 'All Leaves', id: 0 };
            fetchMembersLeaveRequests();
        }
    }
]);

const viewMember = (item: object) => {
    showMemberInfoDialog.value = true;
    selectedMember.value = item;
    dialogHeader.value = selectedMember.value.employee_id + ' - ' + selectedMember.value.full_name;
    selectedTab.value = 'approved';
    fetchMembersLeaveRequests();
}

const fetchMembersLeaveRequests = () => {
    let status = null;
    switch (selectedTab.value) {
        case 'approved':
            status = '3';
            break;
        case 'pending':
            status = '1,2';
            break;
        case 'disapproved': 
            status = '4';
            break;
        case 'cancelled':
            status = '5';
            break;
        default:
            status = null
    }

    let params = {
        user_id: selectedMember.value?.user_id,
        leave_status: status,
        leave_type_id: leaveTypeFilter.value.id,
        date_filter: dateFilter.value
    };
    axios.get('/leaves/requests', {params}).then((response) => {

        leaveRequests.value = response.data
        fetchingRequests.value = false
    })
    .catch((error) => {
        toast.add({ severity: 'error', summary: 'Something went wrong!', detail: error.response.data.message, life: 5000 });
    });
}

const addLeave = (item: object) => {
    console.log(item)
    router.visit(`/leaves/add`, { method: 'get', data: { user_id: item?.user.id }})
}

onMounted(() => {
    fetchMembers();
})
</script>

<template>
    <Head title="Members" />

    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <DataTable :value="members" 
                    v-model:filters="filters" 
                    paginator 
                    :rows="perPage" 
                    :rowsPerPageOptions="perPageOptions" 
                    stripedRows 
                    showGridlines
                    :globalFilterFields="['employee_id', 'full_name']"
                    tableStyle="min-width: 50rem">
                    <template #header>
                        <div class="flex justify-content-center">
                            <IconField iconPosition="left">
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText v-model="filters['global'].value" placeholder="Search" />
                            </IconField>
                        </div>
                    </template>
                    <template #empty> No records found. </template>
                    <template #loading> Loading data. Please wait. </template>
                    <Column field="employee_id" header="Employee ID" style="width: 15%"></Column>
                    <Column field="full_name" header="Name" style="width: 50%"></Column>
                    <Column field="status" header="Status" style="width: 20%"></Column>
                    <Column header="Action" style="width: 15%">
                        <template #body="slotProps">
                            <div class="card flex justify-content-center flex-wrap gap-2">
                                <Button icon="pi pi-info-circle" outlined class="w-[2rem] h-[2rem]" aria-label="Edit" @click="viewMember(slotProps.data)" v-tooltip.bottom="'View Member'"  severity="info" />
                                <Button icon="pi pi-calendar-plus" outlined class="w-[2rem] h-[2rem]" aria-label="Cancel" @click="addLeave(slotProps.data)" v-tooltip.bottom="'Add Request'" severity="primary" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
        <Dialog v-model:visible="showMemberInfoDialog" 
            modal :header="dialogHeader" 
            :style="{ width: '80vw' }" 
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <template #header>
                <div class="inline-flex align-items-center justify-content-center gap-2">
                    <Avatar icon="pi pi-user" shape="circle" />
                    <span class="font-bold white-space-nowrap" v-html="dialogHeader"></span>
                </div>
            </template>
            <div class="flex mb-3 w-full">
                <Credits :member-credits="selectedMember?.credits" :employee="selectedMember" />
            </div>
            <div class="flex mb-3 w-full">
                <TabMenu :model="tabsMenu" class="w-full" />
            </div>
            <div class="card flex justify-between px-1 mb-3">
                <Calendar v-model="dateFilter" selectionMode="range" showIcon showButtonBar :manualInput="false" class="min-w-[300px]" placeholder="Date From - Date To" />
                <Dropdown v-model="leaveTypeFilter" :options="leaveTypes" optionLabel="name" placeholder="Select" checkmark :highlightOnSelect="false" class="min-w-[300px] md:w-14rem" />
                <Button @click="fetchMembersLeaveRequests" label="Filter" :disabled="fetchingRequests" severity="info" />
            </div>
            <div class="flex mb-3 w-full">
                <DataTable :value="leaveRequests" 
                    paginator 
                    :rows="perPage" 
                    :rowsPerPageOptions="perPageOptions" 
                    stripedRows 
                    :loading="fetchingRequests"
                    showGridlines
                    class="w-full"
                    tableStyle="min-width: 50rem">
                    <Column field="date_added" header="Date Added" style="width: 10%"></Column>
                    <Column field="leave_type_name" header="Type" style="width: 10%"></Column>
                    <Column field="date_from" header="From" style="width: 15%"></Column>
                    <Column field="date_to" header="To" style="width: 15%"></Column>
                    <Column field="duration" header="Duration" style="width: 10%"></Column>
                    <Column field="remarks" header="Remarks" style="width: 15%"></Column>
                    <Column field="disapprove_reason" v-if="['disapproved', 'cancelled'].includes(selectedTab)" header="Reason" style="width: 15%"></Column>
                    <Column header="Action" v-if="selectedTab !== 'cancelled'" style="width: 5%">
                        <template #body="slotProps">
                        </template>
                    </Column>
                </DataTable>
            </div>
            <div class="flex flex-wrap justify-end gap-3">
                <Button type="button" label="Close" severity="secondary" @click="showMemberInfoDialog = false"></Button>
            </div>
        </Dialog>
    </AppLayout>
</template>
