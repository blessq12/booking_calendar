import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useCalendarStore = defineStore("calendar", {
    state: () => ({
        events: [],
        isLoading: false,
        error: null,
        updateCounter: 0,
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
                    created_at: booking.created_at,
                    extendedProps: {
                        client_name: booking.extendedProps.client_name,
                        client_phone: booking.extendedProps.client_phone,
                        comment: booking.extendedProps.comment,
                        sauna_id: booking.extendedProps.sauna_id,
                        price: booking.extendedProps.price,
                        prepayment: booking.extendedProps.prepayment,
                        type: booking.extendedProps.type,
                        created_at: booking.created_at,
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
                this.updateCounter++;
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

        async deleteBooking(bookingId) {
            if (this.isLoading) return;

            this.isLoading = true;
            this.error = null;

            try {
                await axios.delete(`/api/bookings/${bookingId}`);
                this.events = this.events.filter(
                    (event) => event.id !== bookingId
                );
                this.updateCounter++;
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

        clearEvents() {
            this.events = [];
        },

        clearError() {
            this.error = null;
        },

        async updateBooking(eventData) {
            if (this.isLoading) return;
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.put(
                    `/api/bookings/${eventData.id}`,
                    {
                        client_name: eventData.client_name,
                        client_phone: eventData.client_phone,
                        comment: eventData.comment,
                        start_datetime: eventData.start_datetime,
                        end_datetime: eventData.end_datetime,
                        sauna_id: eventData.sauna_id,
                        prepayment: eventData.prepayment,
                        total_amount: eventData.total_amount,
                        payment_type: eventData.payment_type,
                    }
                );

                this.events = this.events.map((event) =>
                    event.id === eventData.id
                        ? {
                              ...event,
                              id: eventData.id,
                              title: `Бронь: ${eventData.client_name}`,
                              start: eventData.start_datetime,
                              end: eventData.end_datetime,
                              extendedProps: {
                                  client_name: eventData.client_name,
                                  client_phone: eventData.client_phone,
                                  comment: eventData.comment,
                                  sauna_id: eventData.sauna_id,
                                  prepayment: eventData.prepayment,
                                  total_amount: eventData.total_amount,
                                  payment_type: eventData.payment_type,
                              },
                          }
                        : event
                );

                this.updateCounter++;
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

        async resizeBooking(eventData) {
            return this.updateBooking(eventData);
        },

        async moveBooking(eventData) {
            return this.updateBooking(eventData);
        },
    },

    getters: {
        getAllEvents: (state) => state.events,

        getEventsBySauna: (state) => (saunaId) => {
            return state.events.filter(
                (event) => event.extendedProps.sauna_id === saunaId
            );
        },

        getEventsByDateRange: (state) => (startDate, endDate) => {
            return state.events.filter((event) => {
                const eventStart = new Date(event.start);
                const eventEnd = new Date(event.end);
                const rangeStart = new Date(startDate);
                const rangeEnd = new Date(endDate);
                return eventStart >= rangeStart && eventEnd <= rangeEnd;
            });
        },

        getCurrentEvents: (state) => {
            const now = new Date();
            const startOfDay = new Date(
                now.getFullYear(),
                now.getMonth(),
                now.getDate()
            );
            const endOfDay = new Date(
                now.getFullYear(),
                now.getMonth(),
                now.getDate(),
                23,
                59,
                59
            );

            return state.events
                .filter((event) => {
                    const eventStart = new Date(event.start);
                    return eventStart >= startOfDay && eventStart <= endOfDay;
                })
                .sort((a, b) => {
                    const aCreated = new Date(a.created_at || 0);
                    const bCreated = new Date(b.created_at || 0);
                    return bCreated - aCreated;
                })
                .slice(0, 2);
        },

        getLoadingState: (state) => state.isLoading,
        getError: (state) => state.error,

        getUpdateCounter: (state) => state.updateCounter,
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useCalendarStore, import.meta.hot));
}
