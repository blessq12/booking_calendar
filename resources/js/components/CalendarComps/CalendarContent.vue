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
                eventDrop: this.handleEventDrop,
                eventResize: this.handleEventResize,
                eventChange: this.handleEventChange,
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
                return this.calendarStore.getAllEvents;
            } catch (error) {
                console.error("Error fetching events:", error);
                return [];
            }
        },

        handleDateSelect(selectInfo) {
            const now = new Date();
            const selectedStart = new Date(selectInfo.start);

            // Проверяем, что выбранная дата не в прошлом
            if (selectedStart < now) {
                this.$toast.error(
                    "Нельзя создать бронирование на прошедшее время"
                );
                return;
            }

            const duration =
                (selectInfo.end - selectInfo.start) / (1000 * 60 * 60);
            if (duration < 1) {
                this.$toast.error("Минимальное время бронирования - 1 час");
                return;
            }

            this.appStore.setSelectedDateRange(
                selectInfo.start,
                selectInfo.end
            );

            this.appStore.openModal("create", {
                start: selectInfo.start,
                end: selectInfo.end,
                sauna_id: this.appStore.selectedSauna,
            });
        },

        handleEventClick(clickInfo) {
            this.appStore.openModal("view", clickInfo.event);
        },

        handleDatesSet(dateInfo) {
            this.appStore.setSelectedDate(dateInfo.start);
        },

        handleEventDrop(dropInfo) {
            try {
                this.calendarStore.moveBooking({
                    id: dropInfo.event.id,
                    start: dropInfo.event.start,
                    end: dropInfo.event.end,
                });
            } catch (error) {
                console.error("Error moving event:", error);
                dropInfo.revert();
            }
        },

        handleEventResize(resizeInfo) {
            try {
                this.calendarStore.resizeBooking({
                    id: resizeInfo.event.id,
                    start: resizeInfo.event.start,
                    end: resizeInfo.event.end,
                });
            } catch (error) {
                console.error("Error resizing event:", error);
                resizeInfo.revert();
            }
        },

        handleEventChange(changeInfo) {
            try {
                this.calendarStore.updateBooking({
                    id: changeInfo.event.id,
                    start: changeInfo.event.start,
                    end: changeInfo.event.end,
                });
            } catch (error) {
                console.error("Error updating event:", error);
                changeInfo.revert();
            }
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
