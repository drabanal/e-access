<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, reactive } from 'vue';
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

const breadcrumbs = ref([
    { label: 'Calendar' },
])

const props = defineProps({ leaveTypes: Array, items: Array });

const fullCalendar = ref(null)

const handleDateSelect = () => {
    const title = prompt('Please enter a new title for your event')
    // const calendarApi = selectInfo.view.calendar

    // calendarApi.unselect() // clear date selection

    // if (title) {
    //     calendarApi.addEvent({
    //     id: Date.now().toString(),
    //     title,
    //     start: selectInfo.startStr,
    //     end: selectInfo.endStr,
    //     allDay: selectInfo.allDay
    //     })
    // }
}

const handleEventClick = (clickInfo) => {
    if (confirm(`Are you sure you want to delete the event '${clickInfo.event.title}'?`)) {
        clickInfo.event.remove()
    }
}

const handleEvents = (events) => {

}

const addEvent = () => {

}

const getAllEvents = () => {

}

const calendarOptions = reactive({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  events: props.items
})
</script>

<template>
    <Head title="Calendar" />
    <AppLayout :breadcrumbs="breadcrumbs" class="text-sm">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <FullCalendar
                :options="calendarOptions"
                ref="fullCalendar"
                />
        </div>
    </AppLayout>
</template>
