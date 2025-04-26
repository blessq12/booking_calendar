<script>
import "@mdi/font/css/materialdesignicons.css";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";
import { Russian } from "flatpickr/dist/l10n/ru.js";

import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
const toast = useToast({ timeout: 3000, position: "top-right" });

import { useBookingStore } from "@/stores/bookingStore";
import axios from "axios";
import { storeToRefs } from "pinia";

export default {
    name: "Calendar",
    setup() {
        const bookingStore = useBookingStore();
        const {
            saunas,
            selectedSauna,
            bookingForm,
            validationErrors,
            isLoading,
        } = storeToRefs(bookingStore);
        return {
            bookingStore,
            saunas,
            selectedSauna,
            bookingForm,
            validationErrors,
            isLoading,
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
        selectedSauna() {
            this.onSaunaChange();
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
        getInitialView() {
            return window.innerWidth < 768 ? "timeGridDay" : "timeGridWeek";
        },

        async fetchEvents() {
            try {
                return await this.bookingStore.fetchBookings();
            } catch (error) {
                toast.error(error.message);
                return [];
            }
        },

        handleDateSelect(selectInfo) {
            const now = new Date();
            const selectedStart = new Date(selectInfo.start);

            if (selectedStart < now) {
                toast.error("Выбранная дата уже прошла");
                return;
            }

            this.bookingStore.setBookingForm({
                start_datetime: selectInfo.start,
                end_datetime: selectInfo.end,
                comment: "",
                client_name: "",
                client_phone: "",
            });

            this.showModal = true;
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

        handleEventClick(clickInfo) {
            this.selectedEvent = clickInfo.event;
            this.showViewModal = true;
        },

        handleEvents(events) {
            this.currentEvents = events;
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

        try {
            await this.bookingStore.fetchSaunas();
            this.initializeCalendar();
        } catch (error) {
            toast.error(error.message);
        }
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
    <calendar-content />
    <calendar-modal />
    <calendar-footer />
</template>

<style></style>
