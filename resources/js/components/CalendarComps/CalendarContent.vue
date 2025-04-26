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
                allDaySlot: true,
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
                selectMinDistance: 10,
                firstDay: 1,
                locale: "ru",
                longPressDelay: 100,
                eventLongPressDelay: 100,
                selectLongPressDelay: 100,
                events: (fetchInfo, successCallback, failureCallback) => {
                    this.fetchEvents(fetchInfo)
                        .then(successCallback)
                        .catch(failureCallback);
                },
                select: (selectInfo) => {
                    this.handleDateSelect(selectInfo);
                },
                eventClick: (clickInfo) => {
                    this.handleEventClick(clickInfo);
                },
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
                        selectConstraint: {
                            startTime: "00:00",
                            endTime: "24:00",
                        },
                    },
                    timeGridDay: {
                        titleFormat: {
                            year: "numeric",
                            month: "long",
                            day: "2-digit",
                        },
                        selectConstraint: {
                            startTime: "00:00",
                            endTime: "24:00",
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
                alert("Нельзя создать бронирование на прошедшее время");
                return;
            }

            const duration =
                (selectInfo.end - selectInfo.start) / (1000 * 60 * 60);
            if (duration < 1) {
                alert("Минимальное время бронирования - 1 час");
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

        async handleEventDrop(dropInfo) {
            try {
                await this.calendarStore.moveBooking({
                    id: dropInfo.event.id,
                    client_name: dropInfo.event.extendedProps.client_name,
                    client_phone: dropInfo.event.extendedProps.client_phone,
                    comment: dropInfo.event.extendedProps.comment,
                    start_datetime: dropInfo.event.start,
                    end_datetime: dropInfo.event.end,
                    sauna_id: dropInfo.event.extendedProps.sauna_id,
                    prepayment: dropInfo.event.extendedProps.prepayment,
                    total_amount: dropInfo.event.extendedProps.price,
                    payment_type: dropInfo.event.extendedProps.type,
                });

                // Обновляем события календаря после успешного перемещения
                if (this.calendarApi) {
                    this.calendarApi.refetchEvents();
                }
            } catch (error) {
                console.error("Error moving event:", error);
                dropInfo.revert();
            }
        },

        handleEventResize(resizeInfo) {
            try {
                this.calendarStore.resizeBooking({
                    id: resizeInfo.event.id,
                    client_name: resizeInfo.event.extendedProps.client_name,
                    client_phone: resizeInfo.event.extendedProps.client_phone,
                    comment: resizeInfo.event.extendedProps.comment,
                    start_datetime: resizeInfo.event.start,
                    end_datetime: resizeInfo.event.end,
                    sauna_id: resizeInfo.event.extendedProps.sauna_id,
                    prepayment: resizeInfo.event.extendedProps.prepayment,
                    total_amount: resizeInfo.event.extendedProps.price,
                    payment_type: resizeInfo.event.extendedProps.type,
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
        this.$nextTick(() => {
            if (this.$refs.fullCalendar) {
                this.initializeCalendar();
            }
        });
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
        "calendarStore.getUpdateCounter"() {
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
                    class="calendar-height modern-calendar"
                    @select="handleDateSelect"
                    @eventClick="handleEventClick"
                    @datesSet="handleDatesSet"
                    @eventDrop="handleEventDrop"
                    @eventResize="handleEventResize"
                    @eventChange="handleEventChange"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.modern-calendar {
    --fc-border-color: rgba(75, 85, 99, 0.3);
    --fc-today-bg-color: rgba(59, 130, 246, 0.1);
    --fc-neutral-bg-color: rgba(31, 41, 55, 0.7);
    --fc-list-event-hover-bg-color: rgba(55, 65, 81, 0.7);
    --fc-theme-standard-border-color: rgba(75, 85, 99, 0.3);
}

:deep(.fc) {
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont,
        "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

:deep(.fc-timegrid-slot-label),
:deep(.fc-col-header-cell) {
    font-size: 0.875rem;
    font-weight: 500;
}

:deep(.fc-timegrid-slot) {
    height: 3rem !important;
}

:deep(.fc-scrollgrid-section-header th) {
    padding: 0.75rem 0;
    background-color: rgba(31, 41, 55, 0.7);
}

:deep(.fc-event) {
    border-radius: 0.375rem;
    border: none;
    padding: 0.25rem;
    font-size: 0.875rem;
}

:deep(.fc-timegrid-event-harness) {
    margin: 0 2px;
}

:deep(.fc-timegrid-now-indicator-line) {
    border-color: #ef4444;
}

:deep(.fc-timegrid-now-indicator-arrow) {
    border-color: #ef4444;
    border-width: 5px;
}

:deep(.fc-button) {
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s;
}

:deep(.fc-highlight) {
    background-color: rgba(59, 130, 246, 0.15) !important;
}

:deep(.fc-day-today) {
    background-color: rgba(59, 130, 246, 0.1) !important;
}

:deep(.fc-timegrid-axis) {
    padding: 0.5rem;
}

:deep(.fc-timegrid-slot-label-cushion) {
    font-size: 0.75rem;
    color: #9ca3af;
}

:deep(.fc-col-header-cell-cushion) {
    padding: 0.5rem;
    color: #e5e7eb;
}
</style>
