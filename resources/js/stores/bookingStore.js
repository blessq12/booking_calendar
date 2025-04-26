import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useBookingStore = defineStore("booking", {
    state: () => ({
        bookings: [],
        saunas: [],
        selectedSauna: null,
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
    }),

    actions: {
        async fetchSaunas() {
            try {
                const response = await axios.get("/api/saunas");
                this.saunas = response.data;
                if (this.saunas.length > 0 && !this.selectedSauna) {
                    this.selectedSauna = this.saunas[0].id;
                }
            } catch (error) {
                throw new Error("Ошибка загрузки саун: " + error.message);
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
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useBookingStore, import.meta.hot));
}
