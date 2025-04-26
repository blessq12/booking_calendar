import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useAppCalendarStore = defineStore("appCalendar", {
    state: () => ({
        bookings: [],
        saunas: [],
        selectedSauna: null,
        modal: {
            isOpen: false,
            type: null,
            title: null,
            data: null,
        },
        view: {
            mode: "timeGridDay",
            selectedDate: null,
            selectedDateRange: {
                start: null,
                end: null,
            },
        },
        bookingForm: {
            sauna_id: null,
            client_name: "",
            client_phone: "",
            start_datetime: "",
            end_datetime: "",
            comment: "",
        },
        validationErrors: {},
        isLoading: false,
        error: null,
    }),

    actions: {
        async fetchSaunas() {
            this.isLoading = true;
            try {
                const response = await axios.get("/api/saunas");
                this.saunas = response.data;
                if (this.saunas.length > 0 && !this.selectedSauna) {
                    this.selectedSauna = this.saunas[0].id;
                }
                return this.saunas;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Ошибка при получении списка бань";
                throw this.error;
            } finally {
                this.isLoading = false;
            }
        },

        async fetchBookings() {
            if (!this.selectedSauna) return [];

            try {
                const response = await axios.get(`/api/bookings`, {
                    params: { sauna_id: this.selectedSauna },
                });

                return response.data.map((booking) => ({
                    id: booking.id,
                    title: "Забронировано",
                    start: booking.start_datetime,
                    end: booking.end_datetime,
                    extendedProps: {
                        client: booking.client,
                        comment: booking.comment,
                    },
                }));
            } catch (error) {
                throw new Error(
                    "Ошибка при получении броней: " + error.message
                );
            }
        },

        async createBooking(bookingData) {
            this.isLoading = true;
            try {
                const response = await axios.post("/api/bookings", bookingData);
                this.isLoading = false;
                return response.data;
            } catch (error) {
                this.isLoading = false;
                throw new Error(
                    "Ошибка при создании бронирования: " +
                        error.response?.data?.message || error.message
                );
            }
        },

        async deleteBooking(bookingId) {
            try {
                const response = await axios.delete(
                    `/api/bookings/${bookingId}`
                );
                return response.data;
            } catch (error) {
                throw new Error("Ошибка при удалении брони: " + error.message);
            }
        },

        setSelectedSauna(saunaId) {
            this.selectedSauna = saunaId;
        },

        resetBookingForm() {
            this.bookingForm = {
                sauna_id: this.selectedSauna,
                client_name: "",
                client_phone: "",
                start_datetime: "",
                end_datetime: "",
                comment: "",
            };
            this.validationErrors = {};
        },

        setBookingForm(formData) {
            this.bookingForm = { ...formData, sauna_id: this.selectedSauna };
        },

        setValidationErrors(errors) {
            this.validationErrors = errors;
        },

        openModal(type, data = null) {
            const titles = {
                create: "Создание брони",
                view: "Просмотр брони",
                update: "Редактирование брони",
                search: "Поиск бронирований",
            };

            this.modal = {
                isOpen: true,
                type,
                title: titles[type] || "Модальное окно",
                data,
            };
        },

        closeModal() {
            this.modal = {
                isOpen: false,
                type: null,
                title: null,
                data: null,
            };
        },

        setViewMode(mode) {
            const allowedModes = [
                "timeGridDay",
                "timeGridWeek",
                "dayGridMonth",
            ];
            if (allowedModes.includes(mode)) {
                this.view.mode = mode;
            }
        },

        setSelectedDate(date) {
            this.view.selectedDate = date;
        },

        setSelectedDateRange(start, end) {
            this.view.selectedDateRange = { start, end };
        },

        clearError() {
            this.error = null;
        },
    },

    getters: {
        getSaunas: (state) => state.saunas,

        getCurrentSauna: (state) =>
            state.saunas.find((sauna) => sauna.id === state.selectedSauna),

        getModalState: (state) => state.modal,

        getViewMode: (state) => state.view.mode,

        getSelectedDate: (state) => state.view.selectedDate,

        getSelectedDateRange: (state) => state.view.selectedDateRange,

        isAppLoading: (state) => state.isLoading,

        getError: (state) => state.error,
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(
        acceptHMRUpdate(useAppCalendarStore, import.meta.hot)
    );
}
