<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import Card from 'primevue/card';
import { useToast } from "primevue/usetoast";
import { router } from '@inertiajs/vue3';

const props = defineProps({ leaveTypes: Array, user: Object, leaveRequest: Object, inBehalf: Boolean, leaveUser: Object })

const breadcrumbs = ref([
    { label: 'Leaves' },
    { label: props.inBehalf ? 'Edit - ' + props.leaveUser?.empfname + ', ' + props.leaveUser?.empgname : 'Edit' }
])

const toast = useToast();

const leaveType = ref();
const dateRange = ref();
const totalHours = ref(0);
const totalHoursFormatted = ref();
const fullShift = ref(false);
const removeBreak = ref(false);
const slChargedToVl = ref(false);
const remarks = ref();
const invalidTotalHours = ref(false);
const timeFrom = ref();
const timeTo = ref();
const isSubmitting = ref(false);
const timeRange = ref()

watch(() => removeBreak.value, (val: boolean) => {
    if (val) {
        totalHours.value = totalHours.value - 1
    } else {
        totalHours.value = totalHours.value + 1
    }
    const totalSeconds = parseInt((totalHours.value * 3600))
    totalHoursFormatted.value = formatSecondsToHMS(totalSeconds)

})

watch([dateRange, timeRange, timeFrom, timeTo], () => {
    removeBreak.value = false
    calculateTotalHours()
});

onMounted(() => {
    console.log(props.leaveRequest)
    leaveType.value = props.leaveRequest?.leave_type
    
    const currentDate = new Date()
    let dateTimeFrom = props.leaveRequest?.date_time_from.split(' ')
    let dateTimeTo = props.leaveRequest?.date_time_to.split(' ')

    dateRange.value = [
        new Date(dateTimeFrom[0]),
        new Date(dateTimeTo[0])
    ];

    timeRange.value = [
        new Date(new Date().toLocaleDateString() + ' ' + dateTimeFrom[1]),
        new Date(new Date().toLocaleDateString() + ' ' + dateTimeTo[1])
    ]
    console.log(props.leaveRequest?.remove_break_hours == 1)
    removeBreak.value = true

    remarks.value = props.leaveRequest?.remarks
})

const canSubmit = computed(() => {
    return leaveType.value || dateRange.value || timeFrom.value || timeTo.value || remarks.value || timeRange.value
})

const calculateTotalHours = () => {
    if (dateRange.value.length == 2 && timeRange.value.length == 2) {
        totalHours.value = 0
        let selectedTimeFrom = timeRange.value[0]
        let selectedTimeTo = timeRange.value[1]

        if (dateRange.value[1]) {
            const dayDifference = getDayDifference(dateRange.value[0].toLocaleDateString(), dateRange.value[1].toLocaleDateString())

            let timeDifference = getTimeDifferenceInSeconds(selectedTimeFrom, selectedTimeTo)

            if (timeDifference < 0 && dayDifference == 0) {
                invalidTotalHours.value = true
                totalHoursFormatted.value = ''
            } else {

                invalidTotalHours.value = false
                if (timeDifference < 0 && dayDifference > 0) {

                    const currentDate = new Date()
                    const tmpAdjustedDate = new Date(currentDate.setDate(currentDate.getDate() + 1)).toLocaleDateString();
                    selectedTimeFrom = new Date(new Date().toLocaleDateString() + ' ' + selectedTimeFrom.toLocaleTimeString());
                    selectedTimeTo = (new Date(tmpAdjustedDate + ' ' + selectedTimeTo.toLocaleTimeString()));
                }

                timeDifference = getTimeDifferenceInSeconds(selectedTimeFrom, selectedTimeTo)
                totalHoursFormatted.value = formatSecondsToHMS(timeDifference)

            }
        }
    }
}

const getDayDifference = (dateFromString: string, dateToString: string) => {
    const date1 = new Date(dateFromString);
    const date2 = new Date(dateToString);
    
    const time1 = date1.getTime();
    const time2 = date2.getTime();

    const diffInMs = Math.abs(time2 - time1);
    const oneDayInMs = 1000 * 60 * 60 * 24;

    const diffInDays = Math.round(diffInMs / oneDayInMs);
    return diffInDays;
}

const getTimeDifferenceInSeconds = (time1: string, time2: string) => {
    // Ensure date1 and date2 are Date objects
    const d1 = new Date(time1).getTime();
    const d2 = new Date(time2).getTime();
    
    return parseInt((d2 - d1) / 1000);
}

const formatSecondsToHMS = (totalSeconds: number) => {
    if (isNaN(totalSeconds) || totalSeconds < 0) {
        return ''; // Handle invalid or negative input
    }

    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    // const seconds = totalSeconds % 60;

    // Pad with leading zeros for single-digit values
    const formattedHours = String(hours);
    const formattedMinutes = String(minutes).padStart(2, '0');

    totalHours.value = Math.round((totalSeconds / 3600) * 100) / 100;

    if (minutes > 0) {
        return `${formattedHours}h ${formattedMinutes}m`;
    }
    
    return `${formattedHours}h`;
}

const submitRequest = () => {

    isSubmitting.value = true
    const payload = {
        user_id: props.leaveRequest?.user_id,
        leave_type_id: leaveType.value.id,
        date_range: [
            dateRange.value[0].toLocaleDateString() + ' ' + timeRange.value[0].toLocaleTimeString(),
            dateRange.value[1].toLocaleDateString() + ' ' + timeRange.value[1].toLocaleTimeString()
        ],
        duration: totalHours.value,
        remarks: remarks.value,
        sl_charged_to_vl: slChargedToVl.value,
        is_full_shift: fullShift.value,
        remove_break_hours: removeBreak.value
    }

    axios.post(`/leaves/${props.leaveRequest?.id}`, payload).then((response) => {
        isSubmitting.value = false
        toast.add({ severity: 'success', summary: 'Success!', detail: response.data.message, life: 5000 });
        if (props.inBehalf) {
            router.visit('/team/pending', { method: 'get' })
        } else {
            router.visit('/leaves/pending', { method: 'get' })
        }
    })
    .catch((error) => {
        isSubmitting.value = false
        toast.add({ severity: 'error', summary: 'Something went wrong!', detail: error.response.data.message, life: 5000 });
    });
}

const makeRange = (start: number, end: number) => {
  const result: number[] = []
  for (let i = start; i <= end; i++) {
    result.push(i)
  }
  return result
}

const disabledSeconds = () => {
  return makeRange(1, 59)
}
</script>

<template>
    <Head title="Edit Leave" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Card>
                <template #content>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="mb-4">
                            <FloatLabel class="w-full md:w-14rem">
                                <Dropdown v-model="leaveType" 
                                    :options="leaveTypes" 
                                    optionLabel="name" 
                                    checkmark 
                                    :highlightOnSelect="false" 
                                    :readonly="isSubmitting" 
                                    class="w-full md:w-14rem" />
                                <label for="dd-city">Leave Type</label>
                            </FloatLabel>
                        </div>
                        <div class="mb-4 col-span-2"></div>
                        <div class="mb-4">
                            <FloatLabel class="w-full md:w-14rem mb-1">
                                <Calendar v-model="dateRange" 
                                    id="calendar-date-range" 
                                    selectionMode="range" 
                                    showIcon 
                                    showButtonBar 
                                    :invalid="invalidTotalHours" 
                                    :readonly="isSubmitting" 
                                    class="w-full md:w-14rem" />
                                <label for="dd-city">Date</label>
                            </FloatLabel>
                            <small class="text-red-400" v-if="invalidTotalHours" id="invalid-date-time">Invalid date and time</small>
                        </div>
                        <div class="mb-4">
                            <div>
                                <FloatLabel class="w-full md:w-14rem mb-1">
                                    <el-time-picker
                                        v-model="timeRange"
                                        is-range
                                        range-separator="-"
                                        start-placeholder="Start time"
                                        end-placeholder="End time"
                                        :disabled-seconds="disabledSeconds"
                                        size="large"
                                        style="width: auto;"
                                        />
                                    <label for="totalHours">Total Hours</label>
                                </FloatLabel>
                            </div>
                        </div>
                        <div class="mb-4">
                            <FloatLabel class="w-full md:w-14rem mb-1">
                                <InputText 
                                    v-model="totalHoursFormatted" 
                                    id="totalHours" 
                                    readonly 
                                    class="w-full md:w-14rem" />
                                <label for="totalHours">Total Hours</label>
                            </FloatLabel>
                            <div class="flex flex-wrap justify-content-center gap-3">
                                <div class="flex align-items-center">
                                    <Checkbox v-model="fullShift" 
                                        inputId="fullShift" 
                                        name="fullShift" 
                                        :disabled="totalHours <= 0" 
                                        :readonly="isSubmitting" 
                                        binary />
                                    <label for="fullShift" class="ml-2"> Full Shift </label>
                                </div>
                                <div class="flex align-items-center">
                                    <Checkbox v-model="removeBreak" 
                                        inputId="removeBreak" 
                                        name="removeBreak" 
                                        :disabled="totalHours <= 0" 
                                        :readonly="isSubmitting"
                                        binary />
                                    <label for="removeBreak" class="ml-2"> Remove 1hr Break </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4 col-start-1 col-end-4">
                            <FloatLabel class="w-full md:w-14rem mb-1">
                                <Textarea v-model="remarks" 
                                    rows="5" 
                                    cols="30" 
                                    inputId="remarks" 
                                    :readonly="isSubmitting" 
                                    class="w-full md:w-14rem" />
                                <label for="remarks">Remarks</label>
                            </FloatLabel>
                            <div class="flex flex-wrap justify-content-center gap-3">
                                <div class="flex align-items-center">
                                    <Checkbox v-model="slChargedToVl" 
                                        :disabled="!leaveType || (leaveType && leaveType.id !== 2)" 
                                        :readonly="isSubmitting" 
                                        inputId="slChargedToVl" 
                                        name="slChargedToVl" 
                                        value="1"
                                        binary />
                                    <label for="slChargedToVl" class="ml-2"> SL charged to VL </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3">
                        <Button label="Clear" icon="pi pi-undo" severity="secondary" :disabled="isSubmitting" />
                        <Button label="Submit" :icon="!isSubmitting ? 'pi pi-send' : 'pi pi-spin pi-spinner'" :disabled="!canSubmit || isSubmitting" @click="submitRequest" />
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>