<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { router } from '@inertiajs/vue3';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import Dialog from 'primevue/dialog';


const props = defineProps({ leaveTypes: Array })

const breadcrumbs = ref([
    { label: 'Team' },
    { label: 'Pending Requests' }
])

const confirm = useConfirm();
const toast = useToast();

const fetchingRequests = ref(true);
const leaveRequests = ref([]);
const perPage = ref(30);
const perPageOptions = ref([30, 50, 100]);
const leaveType = ref({ name: 'All Leaves', id: 0 })
const dateFilter = ref();

const fetchRequests = () => {
    fetchingRequests.value = true
    let params = {
        leave_type_id: leaveType.value.id,
        date_filter: dateFilter.value
    };
    axios.get('/team/requests', {params}).then((response) => {

        leaveRequests.value = response.data
        fetchingRequests.value = false
    })
    .catch((error) => {
        toast.add({ severity: 'error', summary: 'Something went wrong!', detail: error.response.data.message, life: 5000 });
    });
}

const showActionDialog = ref(false);
const actionReason = ref('');
const isSubmitting = ref(false);
const selectedRequest = ref();
const selectedStatus = ref('');

const canSubmit = computed(() => {
    return actionReason.value && actionReason.value.trim() !== '' ? true : false
});

const changeStatus = (item: object, status: string) => {
    selectedRequest.value = item;
    showActionDialog.value = true;
    actionReason.value = '';
    selectedStatus.value = status;
}

const confirmAction = () => {
    const payload = {
        id: [selectedRequest.value.id],
        reason: actionReason.value,
        action: selectedStatus.value
    }

    axios.post(`/leaves/update-status`, payload).then((response) => {
        isSubmitting.value = false
        toast.add({ severity: 'success', summary: 'Success!', detail: response.data.message, life: 5000 });
        fetchRequests();
        showActionDialog.value = false
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
    <Head title="Pending Leaves" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
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
                    <Column field="full_name" header="Name" style="width: 20%"></Column>
                    <Column field="leave_type_name" header="Type" style="width: 10%"></Column>
                    <Column field="date_from" header="From" style="width: 10%"></Column>
                    <Column field="date_to" header="To" style="width: 10%"></Column>
                    <Column field="duration" header="Duration" style="width: 5%"></Column>
                    <Column field="remarks" header="Remarks" style="width: 25%"></Column>
                    <Column header="Action" style="width: 10%">
                        <template #body="slotProps">
                            <div v-if="slotProps.data.editable" class="card flex justify-content-center flex-wrap gap-2">
                                <Button icon="pi pi-thumbs-up" outlined class="w-[2rem] h-[2rem]" aria-label="Approve Request" @click="changeStatus(slotProps.data, 'approve')" v-tooltip.bottom="'Approve Request'"  severity="primary" />
                                <Button icon="pi pi-thumbs-down" outlined class="w-[2rem] h-[2rem]" aria-label="Disapprove Request" @click="changeStatus(slotProps.data, 'disapprove')" v-tooltip.bottom="'Disapprove Request'" severity="danger" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
        <Dialog v-model:visible="showActionDialog" modal header="Confirm Action" :style="{ width: '25rem' }">
            <span class="p-text-secondary block mb-8">Are you sure you want to {{ selectedStatus }} the request?</span>
            <div class="flex align-items-center gap-3 mb-3">
                 <FloatLabel class="w-full md:w-14rem mb-1">
                    <Textarea v-model="actionReason" 
                        rows="5" 
                        cols="30" 
                        inputId="remarks" 
                        :readonly="isSubmitting" 
                        class="w-full md:w-14rem" />
                    <label for="remarks">Reason</label>
                </FloatLabel>
            </div>
            <div class="flex flex-wrap justify-end gap-3">
                <Button type="button" label="Cancel" severity="secondary" @click="showActionDialog = false"></Button>
                <Button type="button" label="Yes" @click="confirmAction" :disabled="!canSubmit" :severity="selectedStatus == 'approve' ? 'primary' : 'danger'"></Button>
            </div>
        </Dialog>

    </AppLayout>
</template>
