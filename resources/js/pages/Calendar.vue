<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, reactive, watch, computed, onBeforeMount } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import { type User } from '@/types';
import { usePage } from '@inertiajs/vue3';

const breadcrumbs = ref([
    { label: 'Calendar' },
])

const props = defineProps({ leaveTypes: Array, items: Array });

const fullCalendar = ref(null);
const calendarItems = ref();

const filteredEvents = computed(() => {
    return calendarItems.value.filter(event => {
        if (team.value.id === 2) {
            if (event.team == user.employee.teamname) {
                return true;
            }
            return false;                
        } else {
            return true;
        }
    });
});

const calendarOptions = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    editable: false,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
    events: filteredEvents
})

const teams = ref([
    { name: 'All Team', id: 1 },
    { name: 'My Team', id: 2}
]);
const team = ref({ name: 'All Team', id: 1 });

const page = usePage();
const user = page.props.auth.user as User;

onBeforeMount(() => {
    calendarItems.value = props.items
})
</script>

<template>
    <Head title="Calendar" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex gap-4 mt-5">
                <div class="w-1/4">
                    <FloatLabel class="w-full md:w-14rem">
                        <Dropdown v-model="team" 
                            :options="teams" 
                            optionLabel="name" 
                            checkmark 
                            :highlightOnSelect="false" 
                            class="w-full md:w-14rem" />
                        <label for="dd-city">Filter</label>
                    </FloatLabel>
                </div>
            </div>
            <div class="flex mt-5">
                <div class="grid grid-cols-6 gap-3 mt-2 uppercase">
                    <div class="p-4 rounded-2xl text-left align-middle">
                        <span class="bg-red-500 w-[10px] h-[10px] inline-block mr-2"></span>
                        <span>sick</span>
                    </div>
                    <div class="p-4 rounded-2xl text-left align-middle">
                        <span class="bg-green-500 w-[10px] h-[10px] inline-block mr-2"></span>
                        <span>vacation</span>
                    </div>
                    <div class="p-4 rounded-2xl text-left align-middle">
                        <span class="bg-orange-500 w-[10px] h-[10px] inline-block mr-2"></span>
                        <span>lwop</span>
                    </div>
                    <div class="p-4 rounded-2xl text-left align-middle">
                        <span class="bg-orange-400 w-[10px] h-[10px] inline-block mr-2"></span>
                        <span>undertime</span>
                    </div>
                    <div class="p-4 rounded-2xl text-left align-middle">
                        <span class="bg-blue-500 w-[10px] h-[10px] inline-block mr-2"></span>
                        <span>maternity / paternity</span>
                    </div>
                    <div class="p-4 rounded-2xl text-left align-middle">
                        <span class="bg-gray-500 w-[10px] h-[10px] inline-block mr-2"></span>
                        <span>bereavement</span>
                    </div>
                </div>
            </div>
            <div class="w-full">
                <FullCalendar
                    :options="calendarOptions"
                    ref="fullCalendar"
                    />
            </div>
        </div>
    </AppLayout>
</template>
