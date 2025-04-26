<script>
import "@mdi/font/css/materialdesignicons.css";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";
import { Russian } from "flatpickr/dist/l10n/ru.js";

import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
const toast = useToast({ timeout: 3000, position: "top-right" });

import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { useCalendarStore } from "@/stores/calendarStore";

import axios from "axios";

export default {
    name: "Calendar",
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    provide() {
        return {
            user: this.user,
        };
    },
    setup() {
        const calendarStore = useCalendarStore();
        const appCalendarStore = useAppCalendarStore();

        return {
            calendarStore,
            appCalendarStore,
        };
    },
    data() {
        return {
            showModal: false,
            showViewModal: false,
            selectedEvent: null,
            currentEvents: [],
        };
    },
    watch: {
        "appCalendarStore.selectedSauna"(newValue) {
            if (newValue) {
                this.$refs.calendarContent?.fetchEvents();
            }
        },
        "appCalendarStore.viewMode"(newValue) {
            if (this.$refs.fullCalendar) {
                const calendarApi = this.$refs.fullCalendar.getApi();
                calendarApi.changeView(newValue);
            }
        },
        showModal(isOpen) {
            if (isOpen) {
                this.$nextTick(() => {
                    this.initializeDatePickers();
                });
            } else {
                this.destroyDatePickers();
            }
        },
    },
    methods: {
        async initialize() {
            try {
                await this.appCalendarStore.fetchSaunas();
                if (this.$refs.fullCalendar) {
                    const calendarApi = this.$refs.fullCalendar.getApi();
                    calendarApi.setOption(
                        "initialView",
                        this.appCalendarStore.viewMode
                    );
                }
                if (this.appCalendarStore.selectedSauna) {
                    await this.fetchEvents();
                }
            } catch (error) {
                toast.error(error.message);
            }
        },

        async fetchEvents() {
            if (!this.appCalendarStore.selectedSauna) return;

            try {
                const calendarApi = this.$refs.fullCalendar.getApi();
                const start = calendarApi.view.activeStart;
                const end = calendarApi.view.activeEnd;

                await this.calendarStore.fetchBookings(
                    this.appCalendarStore.selectedSauna,
                    start.toISOString(),
                    end.toISOString()
                );
            } catch (error) {
                toast.error(error.message);
            }
        },

        handleDateSelect(selectInfo) {
            const now = new Date();
            const selectedStart = new Date(selectInfo.start);

            if (selectedStart < now) {
                toast.error("Выбранная дата уже прошла");
                return;
            }

            this.calendarStore.setSelectedDates(
                selectInfo.start,
                selectInfo.end
            );
            this.appCalendarStore.openModal("create");
        },

        handleEventClick(clickInfo) {
            this.selectedEvent = clickInfo.event;
            this.appCalendarStore.openModal("view", this.selectedEvent);
        },

        handleEvents(events) {
            this.currentEvents = events;
        },

        handleDatesSet(dateInfo) {
            console.log("Dates changed:", dateInfo);
        },

        getInitialView() {
            return window.innerWidth < 768 ? "timeGridDay" : "timeGridWeek";
        },

        async createBooking() {
            try {
                await this.bookingStore.createBooking(this.bookingForm);
                toast.success("Бронирование успешно создано");
                this.showModal = false;
                this.bookingStore.resetBookingForm();
                if (this.$refs.fullCalendar) {
                    this.$refs.fullCalendar.getApi().refetchEvents();
                }
            } catch (error) {
                toast.error(error.message);
            }
        },

        async deleteBooking() {
            if (!this.selectedEvent) return;
            if (!confirm("Вы уверены, что хотите удалить эту бронь?")) return;

            try {
                await this.bookingStore.deleteBooking(this.selectedEvent.id);
                this.selectedEvent.remove();
                this.showViewModal = false;
                this.selectedEvent = null;
                toast.success("Бронирование успешно удалено");
            } catch (error) {
                toast.error(error.message);
            }
        },

        async onSaunaChange() {
            if (this.$refs.fullCalendar) {
                const calendarApi = this.$refs.fullCalendar.getApi();
                calendarApi.refetchEvents();
            }
        },

        calculateEndTime(startTime) {
            const date = new Date(startTime);
            date.setHours(date.getHours() + 1);
            return date.toTimeString().split(" ")[0];
        },

        initializeCalendar() {
            if (this.$refs.fullCalendar) {
                const calendarApi = this.$refs.fullCalendar.getApi();
                calendarApi.setOption("initialView", this.getInitialView());
                calendarApi.setOption("height", window.innerHeight - 200);
                this.fetchEvents();
            }
        },

        destroyDatePickers() {
            const startInput = document.getElementById("start_datetime");
            const endInput = document.getElementById("end_datetime");
            if (startInput?._flatpickr) {
                startInput._flatpickr.destroy();
            }
            if (endInput?._flatpickr) {
                endInput._flatpickr.destroy();
            }
            this.startPicker = null;
            this.endPicker = null;
        },

        initializeDatePickers() {
            try {
                const startInput = document.getElementById("start_datetime");
                const endInput = document.getElementById("end_datetime");
                flatpickr(startInput, {
                    locale: Russian,
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: "Z",
                    altFormat: "d.m.Y H:i",
                    altInput: true,
                    minDate: "today",
                    minuteIncrement: 60,
                    defaultHour: new Date().getHours(),
                    position: "auto center",
                    allowInput: false,
                    onChange: (selectedDates) => {
                        this.bookingForm.start_datetime =
                            selectedDates[0].toISOString();
                    },
                });
                flatpickr(endInput, {
                    locale: Russian,
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: "Z",
                    altFormat: "d.m.Y H:i",
                    altInput: true,
                    minDate: "today",
                    minuteIncrement: 60,
                    defaultHour: new Date().getHours() + 1,
                    position: "auto center",
                    allowInput: false,
                    onChange: (selectedDates) => {
                        this.bookingForm.end_datetime =
                            selectedDates[0].toISOString();
                    },
                });
            } catch (error) {
                toast.error("Ошибка инициализации календаря");
            }
        },

        switchView(view) {
            const calendar = this.$refs.fullCalendar.getApi();
            calendar.changeView(view);
            this.calendarOptions.initialView = view;
        },

        prevPeriod() {
            const calendar = this.$refs.fullCalendar.getApi();
            calendar.prev();
        },

        nextPeriod() {
            const calendar = this.$refs.fullCalendar.getApi();
            calendar.next();
        },

        goToToday() {
            const calendar = this.$refs.fullCalendar.getApi();
            calendar.today();
        },
    },
    async mounted() {
        const token = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        axios.defaults.headers.common = {
            "X-CSRF-TOKEN": token,
            "Content-Type": "application/json",
            Accept: "application/json",
        };

        await this.initialize();
    },
    updated() {
        if (this.selectedSauna) {
            this.fetchEvents();
        }
    },
};
</script>

<template>
    <calendar-header />
    <calendar-content
        ref="calendarContent"
        @date-select="handleDateSelect"
        @event-click="handleEventClick"
        @events-set="handleEvents"
        @dates-set="handleDatesSet"
    />
    <calendar-modal />
    <calendar-footer />
</template>

<style></style>
