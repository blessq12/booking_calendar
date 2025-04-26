<script>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { useCalendarStore } from "@/stores/calendarStore";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import FullCalendar from "@fullcalendar/vue3";
import { storeToRefs } from "pinia";

export default {
    name: "CalendarContent",
    components: {
        FullCalendar,
    },
    setup() {
        const calendarStore = useCalendarStore();
        const appStore = useAppCalendarStore();
        const { view } = storeToRefs(appStore);

        return {
            calendarStore,
            appStore,
            view,
        };
    },
    data() {
        return {
            calendarApi: null,
            calendarOptions: {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                initialView: "timeGridWeek",
                slotMinTime: "00:00:00",
                slotMaxTime: "24:00:00",
                allDaySlot: false,
                selectable: true,
                selectMirror: true,
                editable: true,
                droppable: true,
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
                events: (fetchInfo, successCallback, failureCallback) => {
                    this.fetchEvents(fetchInfo)
                        .then(successCallback)
                        .catch(failureCallback);
                },
                select: this.handleDateSelect,
                eventClick: this.handleEventClick,
                eventsSet: this.handleEvents,
                datesSet: this.handleDatesSet,
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
                    dayGridMonth: {
                        titleFormat: {
                            year: "numeric",
                            month: "long",
                        },
                    },
                },
            },
        };
    },
    methods: {
        async fetchEvents(fetchInfo) {
            if (!this.appStore.selectedSauna || !fetchInfo) return [];
            try {
                await this.calendarStore.fetchBookings({
                    saunaId: this.appStore.selectedSauna,
                    startDate: fetchInfo.startStr,
                    endDate: fetchInfo.endStr,
                });
                // console.log(this.calendarStore.getAllEvents);
                return this.calendarStore.getAllEvents;
            } catch (error) {
                console.error("Error fetching events:", error);
                return [];
            }
        },

        handleDateSelect(selectInfo) {
            this.appStore.setSelectedDateRange(
                selectInfo.start,
                selectInfo.end
            );
            this.appStore.openModal("create");
        },

        handleEventClick(clickInfo) {
            this.appStore.openModal("view", clickInfo.event);
        },

        handleDatesSet(dateInfo) {
            this.appStore.setSelectedDate(dateInfo.start);
        },

        initializeCalendar() {
            if (this.$refs.fullCalendar) {
                this.calendarApi = this.$refs.fullCalendar.getApi();
                const initialView =
                    window.innerWidth < 768 ? "timeGridDay" : "timeGridWeek";
                this.calendarApi.changeView(initialView);
                this.appStore.setViewMode(initialView);
            }
        },
    },
    mounted() {
        this.$nextTick(this.initializeCalendar);
    },
    watch: {
        "view.mode"(newMode) {
            if (this.calendarApi) {
                this.calendarApi.changeView(newMode);
            }
        },
        "appStore.selectedSauna"() {
            if (this.calendarApi) {
                this.calendarApi.refetchEvents();
            }
        },
    },
};
</script>

<template>
    <div class="bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 md:px-6 pb-12">
            <div class="bg-gray-800 rounded-xl text-gray-100 p-4">
                <FullCalendar
                    ref="fullCalendar"
                    :options="calendarOptions"
                    class="calendar-height"
                />
            </div>
        </div>
    </div>
</template>

<style scoped></style>
