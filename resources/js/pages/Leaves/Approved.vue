<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import Credits from './Credits.vue';
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useToast } from "primevue/usetoast";


defineProps({ leaveTypes: Array, employee: Object })

const breadcrumbs = ref([
    { label: 'Leaves' },
    { label: 'Approved Request' }
])

const toast = useToast();

const fetchingRequests = ref(true);
const leaveRequests = ref([]);
const pagination = ref({});
const perPage = ref(30);
const perPageOptions = ref([30, 50, 100]);
const leaveType = ref({ name: 'All Leaves', id: 0 })
const dateFilter = ref();

const fetchRequests = () => {
    fetchingRequests.value = true
    let params = {
        leave_status: '3',
        leave_type_id: leaveType.value.id,
        date_filter: dateFilter.value
    };
    axios.get('/leaves/requests', {params}).then((response) => {

        leaveRequests.value = response.data
        fetchingRequests.value = false
    })
    .catch((error) => {
        console.log(error)
    });
}

const showCancelDialog = ref(false);
const cancelReason = ref(null);
const isSubmitting = ref(false);
const selectedRequest = ref();

const cancelRequest = (item: object) => {
    selectedRequest.value = item;
    showCancelDialog.value = true;
    cancelReason.value = null;
};

const confirmCancel = () => {
    const payload = {
        id: [selectedRequest.value.id],
        reason: cancelReason.value,
        action: 'cancel'
    }

    axios.post(`/leaves/update-status`, payload).then((response) => {
        isSubmitting.value = false
        toast.add({ severity: 'success', summary: 'Success!', detail: response.data.message, life: 5000 });
        fetchRequests();
        showCancelDialog.value = false
    })
    .catch((error) => {
        console.log(error)
        isSubmitting.value = false
        toast.add({ severity: 'error', summary: 'Something went wrong!', detail: error.response.data.message, life: 5000 });
    });
}

watch(() => perPage.value, (value) => {
    if (value) {
        fetchRequests();
    }
}, { deep: true, immediate: true})

onMounted(() => {
    fetchRequests();
})
</script>

<template>
    <Head title="Approved Leaves" />

    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Credits :employee="employee"/>
            <div class="relative overflow-hidden rounded-xl ">
                <div class="card flex justify-between px-1">
                    <Calendar v-model="dateFilter" selectionMode="range" showIcon showButtonBar :manualInput="false" class="min-w-[300px]" placeholder="Date From - Date To" />
                    <Dropdown v-model="leaveType" :options="leaveTypes" optionLabel="name" placeholder="Select" checkmark :highlightOnSelect="false" class="min-w-[300px] md:w-14rem" />
                    <Button @click="fetchRequests" label="Filter" :disabled="fetchingRequests" severity="info" />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <DataTable :value="leaveRequests" 
                    paginator 
                    :rows="perPage" 
                    :rowsPerPageOptions="perPageOptions" 
                    stripedRows 
                    showGridlines
                    tableStyle="min-width: 50rem">
                    <Column field="date_added" header="Date Added" style="width: 10%"></Column>
                    <Column field="leave_type_name" header="Type" style="width: 15%"></Column>
                    <Column field="date_from" header="From" style="width: 15%"></Column>
                    <Column field="date_to" header="To" style="width: 15%"></Column>
                    <Column field="duration" header="Duration" style="width: 10%"></Column>
                    <Column field="remarks" header="Remarks" style="width: 25%"></Column>
                    <Column header="Action" style="width: 10%">
                        <template #body="slotProps">
                            <div v-if="slotProps.data.editable" class="card flex justify-content-center flex-wrap gap-2">
                                <Button icon="pi pi-times-circle" outlined class="w-[2rem] h-[2rem]" aria-label="Cancel" @click="cancelRequest(slotProps.data)" v-tooltip.bottom="'Cancel Request'" severity="warning" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
