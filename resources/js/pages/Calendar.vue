<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, reactive, watch } from 'vue';
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
  events: props.items
})

const teams = ref([
    { name: 'All Team', id: 1 },
    { name: 'My Team', id: 2}
]);
const team = ref({ name: 'All Team', id: 1 });

const page = usePage();
const user = page.props.auth.user as User;

watch(() => team.value, (val) => {
    if (val.id === 2) {
        calendarItems.value = props.items?.filter(item => item.team == user.employee.teamname)
    } else {
        calendarItems.value = props.items
    }
}, { deep: true, immediate: true});

onMounted(() => {
    calendarItems.value = props.items
})
</script>

<template>
    <Head title="Calendar" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex">
                <div class="grid grid-cols-3 gap-4 mt-5">
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
            <div class="w-full">
                <FullCalendar
                    :options="calendarOptions"
                    ref="fullCalendar"
                    />
            </div>
        </div>
    </AppLayout>
</template>
