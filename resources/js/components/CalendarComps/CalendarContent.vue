<script>
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import FullCalendar from "@fullcalendar/vue3";
export default {
    name: "CalendarContent",
    components: {
        FullCalendar,
    },
    data() {
        return {
            calendarOptions: {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                initialView: "timeGridWeek",
                slotMinTime: "00:00:00",
                slotMaxTime: "24:00:00",
                allDaySlot: false,
                selectable: true,
                selectMirror: true,
                editable: false,
                dayMaxEvents: true,
                selectConstraint: {
                    start: new Date().toISOString(),
                    end: "2100-01-01",
                },
                selectOverlap: false,
                eventOverlap: false,
                headerToolbar: false,
                slotDuration: "01:00:00",
                selectMinDistance: 1000,
                locale: "ru",
                events: this.fetchEvents,
                select: this.handleDateSelect,
                eventClick: this.handleEventClick,
                eventsSet: this.handleEvents,
                nowIndicator: true,
                height: "auto",
                views: {
                    timeGridWeek: {
                        titleFormat: {
                            year: "numeric",
                            month: "long",
                            day: "2-digit",
                        },
                    },
                    timeGridDay: {
                        titleFormat: {
                            year: "numeric",
                            month: "long",
                            day: "2-digit",
                        },
                    },
                },
            },
        };
    },
};
</script>

<template>
    <div class="bg-gray-900 mx-auto max-w-7xl px-4 md:px-6">
        <div class="bg-gray-800 rounded-xl text-gray-100 p-4">
            <FullCalendar
                ref="fullCalendar"
                :options="calendarOptions"
                class="calendar-height"
            />
        </div>
    </div>
</template>

<style scoped></style>
