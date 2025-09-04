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
    { label: 'Pending Leaves' }
])

const confirm = useConfirm();
const toast = useToast();

const fetchingRequests = ref(true);
const leaveRequests = ref([]);
const perPage = ref(30);
const perPageOptions = ref([30, 50, 100]);
const leaveType = ref({ name: 'All Leaves', id: 0 })
const dateFilter = ref();
const selectAll = ref(false);

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
const selectedRequests = ref([]);
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
    let payload: object = {
        id: [],
        reason: actionReason.value,
        action: selectedStatus.value
    };
    if (selectedRequests.value.length > 1) {
        payload.id = selectedRequests.value.map((r: any) => r.id)
    } else {
        payload.id = [selectedRequest.value.id]
    }
    
    isSubmitting.value = true

    axios.post(`/leaves/update-status`, payload).then((response) => {
        isSubmitting.value = false
        toast.add({ severity: 'success', summary: 'Success!', detail: response.data.message, life: 5000 });
        fetchRequests();
        showActionDialog.value = false
    })
    .catch((error) => {
        isSubmitting.value = false
        toast.add({ severity: 'error', summary: 'Something went wrong!', detail: error.response.data.message, life: 5000 });
    });
}

const bulkUpdateStatus = (status: string) => {
    if (selectedRequests.value.length < 2) {
        toast.add({ severity: 'warn', summary: 'Warning!', detail: 'Please select at least 2 requests to perform bulk action.', life: 5000 });
        return;
    }
    showActionDialog.value = true;
    actionReason.value = '';
    selectedStatus.value = status;
}


const selectableRequests = computed(() => 
    leaveRequests.value.filter(p => p.editable)
)

const showRemarksDialog = ref(false);

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
    if (value) {
        fetchRequests();
    }
}, { deep: true, immediate: true})

watch(() => selectAll.value, (value) => {
    if (value) {
        selectedRequests.value = [...selectableRequests.value]
    } else {
        selectedRequests.value = []
    }
}, { deep: true })

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
                    <div class="flex w-full">
                        <div class="w-2/3">
                            <Calendar v-model="dateFilter" selectionMode="range" showIcon showButtonBar :manualInput="false" class="min-w-[300px] mr-2" placeholder="Date From - Date To" />
                            <Dropdown v-model="leaveType" :options="leaveTypes" optionLabel="name" placeholder="Select" checkmark :highlightOnSelect="false" class="min-w-[300px] mr-2 md:w-14rem" />
                        </div>
                        <div class="w-1/3 text-right">
                            <Button :disabled="selectedRequests.length < 2" @click="bulkUpdateStatus('approve')" class="mr-2" label="Bulk Approve" aria-label="Approve Request" v-tooltip.bottom="'Bulk Approve Request'" severity="primary" />
                            <Button :disabled="selectedRequests.length < 2" @click="bulkUpdateStatus('disapprove')" class="mr-2" label="Bulk Disapprove" aria-label="Disapprove Request" v-tooltip.bottom="'Bulk Disapprove Request'" severity="danger" />
                            <Button @click="fetchRequests" label="Filter" :disabled="fetchingRequests" severity="info" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <DataTable :value="leaveRequests" 
                    paginator 
                    :rows="perPage" 
                    :rowsPerPageOptions="perPageOptions" 
                    stripedRows 
                    showGridlines
                    v-model:selection="selectedRequests"
                    dataKey="id"
                    tableStyle="min-width: 50rem">
                    <Column headerStyle="width: 3rem">
                        <template #body="slotProps">
                            <Checkbox 
                            v-model="selectedRequests" 
                            :value="slotProps.data"
                            :disabled="!slotProps.data.editable"
                            />
                        </template>
                        <template #header>
                            <Checkbox 
                            v-model="selectAll" 
                            :indeterminate="selectedRequests.length > 0 && selectedRequests.length < selectableRequests.length"
                            :binary="true"
                            />
                        </template>
                    </Column>
                    <Column field="date_added" header="Date Added" style="width: 10%"></Column>
                    <Column field="full_name" header="Name" style="width: 20%"></Column>
                    <Column field="leave_type_name" header="Type" style="width: 10%"></Column>
                    <Column field="date_from" header="From" style="width: 10%"></Column>
                    <Column field="date_to" header="To" style="width: 10%"></Column>
                    <Column field="duration" header="Duration" style="width: 5%"></Column>
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
                    <Column header="Action" style="width: 10%">
                        <template #body="slotProps">
                            <div v-if="slotProps.data.editable" class="card flex justify-content-center flex-wrap gap-2">
                                <Button icon="pi pi-thumbs-up" 
                                    :disabled="selectedRequests.length > 1" 
                                    outlined 
                                    rounded 
                                    class="w-[2rem] h-[2rem]" 
                                    aria-label="Approve Request" 
                                    @click="changeStatus(slotProps.data, 'approve')" 
                                    v-tooltip.bottom="'Approve Request'"  
                                    severity="primary" />
                                <Button icon="pi pi-thumbs-down" 
                                    :disabled="selectedRequests.length > 1" 
                                    outlined 
                                    rounded 
                                    class="w-[2rem] h-[2rem]" 
                                    aria-label="Disapprove Request" 
                                    @click="changeStatus(slotProps.data, 'disapprove')" 
                                    v-tooltip.bottom="'Disapprove Request'" 
                                    severity="danger" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
        <Dialog v-model:visible="showActionDialog" modal header="Confirm Action" :style="{ width: '25rem' }">
            <span class="p-text-secondary block mb-8">Are you sure you want to {{ selectedStatus }} the selected request(s)?</span>
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
                <Button type="button" label="Cancel" severity="secondary" :disabled="isSubmitting" @click="showActionDialog = false"></Button>
                <Button type="button" :icon="!isSubmitting ? 'pi pi-check' : 'pi pi-spin pi-spinner'" label="Yes" @click="confirmAction" :disabled="!canSubmit || isSubmitting" :severity="selectedStatus == 'approve' ? 'primary' : 'danger'"></Button>
            </div>
        </Dialog>
        <Dialog v-model:visible="showRemarksDialog" modal header="Remarks" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <p class="mb-5">
                {{ selectedRequest?.remarks }}
            </p>
            <Divider v-if="selectedRequest?.additional_remarks.length > 0" />
            <h2 class="mb-3" v-if="selectedRequest?.additional_remarks.length > 0">Additional Remarks</h2>
            <p class="mb-5" v-for="log in selectedRequest?.additional_remarks" :key="log.id">
                <strong>{{ log.status }}</strong> - {{  log.changed_by }} on <em>{{ log.date_changed }}</em> <br />
                <em>"{{ log.reason }}"</em><br>
            </p>
        </Dialog>
    </AppLayout>
</template>
