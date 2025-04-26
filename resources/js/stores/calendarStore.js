import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useCalendarStore = defineStore("calendar", {
    state: () => ({
        events: [],
        isLoading: false,
        error: null,
    }),

    actions: {
        async fetchBookings({ saunaId, startDate, endDate }) {
            if (this.isLoading) return;

            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/bookings", {
                    params: {
                        sauna_id: saunaId,
                        start_date: startDate,
                        end_date: endDate,
                    },
                });

                this.events = response.data.map((booking) => ({
                    id: booking.id,
                    title: `Бронь: ${booking.extendedProps.client_name}`,
                    start: booking.start,
                    end: booking.end,
                    extendedProps: {
                        client_name: booking.extendedProps.client_name,
                        client_phone: booking.extendedProps.client_phone,
                        comment: booking.extendedProps.comment,
                        sauna_id: booking.extendedProps.sauna_id,
                        price: booking.extendedProps.price,
                        prepayment: booking.extendedProps.prepayment,
                        type: booking.extendedProps.type,
                    },
                }));

                return this.events;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Ошибка при получении броней";
                throw this.error;
            } finally {
                this.isLoading = false;
            }
        },

        async createBooking(bookingData) {
            if (this.isLoading) return;

            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.post("/api/bookings", bookingData);

                // Добавляем новое событие в календарь
                const newEvent = {
                    id: response.data.id,
                    title: `Бронь: ${response.data.client_name}`,
                    start: response.data.start_datetime,
                    end: response.data.end_datetime,
                    extendedProps: {
                        client_name: response.data.client_name,
                        client_phone: response.data.client_phone,
                        comment: response.data.comment,
                        sauna_id: response.data.sauna_id,
                    },
                };

                this.events = [...this.events, newEvent];
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Ошибка при создании брони";
                throw this.error;
            } finally {
                this.isLoading = false;
            }
        },

        // Удаление брони
        async deleteBooking(bookingId) {
            if (this.isLoading) return;

            this.isLoading = true;
            this.error = null;

            try {
                await axios.delete(`/api/bookings/${bookingId}`);
                this.events = this.events.filter(
                    (event) => event.id !== bookingId
                );
                return true;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Ошибка при удалении брони";
                throw this.error;
            } finally {
                this.isLoading = false;
            }
        },

        // Очистка событий
        clearEvents() {
            this.events = [];
        },

        // Очистка ошибок
        clearError() {
            this.error = null;
        },

        async updateBooking(eventData) {
            const confirmation = confirm(
                "Вы уверены, что хотите изменить бронирование?"
            );
            if (!confirmation) return;

            if (this.isLoading) return;
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.put(
                    `/api/bookings/${eventData.id}`,
                    {
                        start_datetime: eventData.start,
                        end_datetime: eventData.end,
                    }
                );

                // Обновляем событие в локальном состоянии
                this.events = this.events.map((event) =>
                    event.id === eventData.id
                        ? {
                              ...event,
                              start: eventData.start,
                              end: eventData.end,
                          }
                        : event
                );

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Ошибка при обновлении брони";
                throw this.error;
            } finally {
                this.isLoading = false;
            }
        },

        // Изменение размера события
        async resizeBooking(eventData) {
            return this.updateBooking(eventData);
        },

        // Перетаскивание события
        async moveBooking(eventData) {
            return this.updateBooking(eventData);
        },
    },

    getters: {
        // Получение всех событий
        getAllEvents: (state) => state.events,

        // Получение событий для конкретной бани
        getEventsBySauna: (state) => (saunaId) => {
            return state.events.filter(
                (event) => event.extendedProps.sauna_id === saunaId
            );
        },

        // Получение событий за период
        getEventsByDateRange: (state) => (startDate, endDate) => {
            return state.events.filter((event) => {
                const eventStart = new Date(event.start);
                const eventEnd = new Date(event.end);
                const rangeStart = new Date(startDate);
                const rangeEnd = new Date(endDate);
                return eventStart >= rangeStart && eventEnd <= rangeEnd;
            });
        },

        // Проверка загрузки
        getLoadingState: (state) => state.isLoading,

        // Получение ошибки
        getError: (state) => state.error,
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useCalendarStore, import.meta.hot));
}
