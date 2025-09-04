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
import Paginator from 'primevue/paginator';


defineProps({ leaveTypes: Array, employee: Object })

const breadcrumbs = ref([
    { label: 'Leaves' },
    { label: 'Cancelled' }
])

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
        leave_status: '5',
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

const showRemarksDialog = ref(false);
const selectedRequest = ref();

const viewRemarks = (item: object) => {
    showRemarksDialog.value = true;
    axios.get('/leaves/' + item.id + '/detail').then((response) => {
        selectedRequest.value = response.data;
    })
    .catch((error) => {
        toast.add({ severity: 'error', summary: 'Something went wrong!', detail: error.response.data.message, life: 5000 });
    });
}

watch(() => perPage.value, (value) => {
    console.log()
    if (value) {
        fetchRequests();
    }
}, { deep: true, immediate: true})

onMounted(() => {
    fetchRequests();
})
</script>

<template>
    <Head title="Cancelled Leaves" />

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
                    tableStyle="min-width: 50rem">
                    <Column field="date_added" header="Date Added" style="width: 10%"></Column>
                    <Column field="leave_type_name" header="Type" style="width: 10%"></Column>
                    <Column field="date_from" header="From" style="width: 15%"></Column>
                    <Column field="date_to" header="To" style="width: 15%"></Column>
                    <Column field="duration" header="Duration" style="width: 10%"></Column>
                    <Column header="Remarks" style="width: 25%">
                        <template #body="slotProps">
                            <p class="max-w-[15vw] truncate float-left">{{ slotProps.data.remarks }}</p>
                            <Button icon="pi pi-file" 
                                outlined 
                                rounded 
                                class="w-[2rem] h-[2rem] float-right" 
                                aria-label="View Remarks" 
                                v-tooltip.bottom="'View Remarks'"
                                @click="viewRemarks(slotProps.data)"
                                severity="info" />
                        </template>
                    </Column>
                    <Column field="cancel_reason" header="Reason" style="width: 15%"></Column>
                </DataTable>
            </div>
        </div>
        <Dialog v-model:visible="showRemarksDialog" modal header="Remarks" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <p class="mb-5">
                {{ selectedRequest?.remarks }}
            </p>
            <Divider v-if="selectedRequest?.additional_remarks.length > 0" />
            <h2 class="mb-3" v-if="selectedRequest?.additional_remarks.length > 0">Additional Remarks</h2>
            <p class="mb-5" v-for="log in selectedRequest?.additional_remarks" :key="log.id">
                <strong>{{ log.status }}</strong> - {{  log.changed_by }} on <em>{{ log.date_changed }}</em> <br />
                <em v-if="log.reason">"{{ log.reason }}"</em><br>
            </p>
        </Dialog>
    </AppLayout>
</template>
