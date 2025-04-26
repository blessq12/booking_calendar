<script>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { useCalendarStore } from "@/stores/calendarStore";
import { storeToRefs } from "pinia";

export default {
    name: "CalendarModal",
    setup() {
        const appStore = useAppCalendarStore();
        const calendarStore = useCalendarStore();
        const { modal, selectedSauna } = storeToRefs(appStore);

        return {
            appStore,
            calendarStore,
            modal,
            selectedSauna,
        };
    },
    data() {
        return {
            form: {
                client_name: "",
                client_phone: "",
                comment: "",
                start_datetime: "",
                end_datetime: "",
            },
        };
    },
    watch: {
        "modal.isOpen"(isOpen) {
            if (isOpen && this.modal.type === "create") {
                // Инициализация формы для создания
                this.initCreateForm();
            }
        },
    },
    methods: {
        initCreateForm() {
            const { start, end } = this.appStore.view.selectedDateRange;
            this.form = {
                client_name: "",
                client_phone: "",
                comment: "",
                start_datetime: start,
                end_datetime: end,
                sauna_id: this.selectedSauna,
            };
        },

        async handleSubmit() {
            try {
                await this.calendarStore.createBooking(this.form);
                this.appStore.closeModal();
                // Очищаем форму
                this.form = {
                    client_name: "",
                    client_phone: "",
                    comment: "",
                    start_datetime: "",
                    end_datetime: "",
                };
            } catch (error) {
                console.error("Error creating booking:", error);
            }
        },

        async handleDelete() {
            if (!this.modal.data?.id) return;

            if (confirm("Вы уверены, что хотите удалить эту бронь?")) {
                try {
                    await this.calendarStore.deleteBooking(this.modal.data.id);
                    this.appStore.closeModal();
                } catch (error) {
                    console.error("Error deleting booking:", error);
                }
            }
        },
    },
};
</script>

<template>
    <div
        v-if="modal.isOpen"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
        <div
            class="w-full max-w-lg transform bg-gray-800 rounded-xl shadow-2xl border border-gray-700 transition-all"
            @click.stop
        >
            <div class="relative p-6">
                <!-- Заголовок -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-100">
                        {{
                            modal.type === "create"
                                ? "Создание брони"
                                : "Просмотр брони"
                        }}
                    </h3>
                    <button
                        @click="appStore.closeModal"
                        class="p-2 text-gray-400 hover:text-gray-300 rounded-lg hover:bg-gray-700 transition-all duration-200"
                    >
                        <i class="mdi mdi-close text-xl"></i>
                    </button>
                </div>

                <!-- Форма создания -->
                <form
                    v-if="modal.type === 'create'"
                    @submit.prevent="handleSubmit"
                    class="space-y-4"
                >
                    <div class="form-group">
                        <label class="form-label">Имя клиента</label>
                        <div class="relative">
                            <i
                                class="mdi mdi-account absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                v-model="form.client_name"
                                type="text"
                                class="form-input"
                                required
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <div class="relative">
                            <i
                                class="mdi mdi-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                v-model="form.client_phone"
                                type="tel"
                                class="form-input"
                                required
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Комментарий</label>
                        <div class="relative">
                            <i
                                class="mdi mdi-comment-text absolute left-3 top-3 text-gray-400"
                            ></i>
                            <textarea
                                v-model="form.comment"
                                rows="3"
                                class="form-input"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            @click="appStore.closeModal"
                            class="btn-secondary"
                        >
                            <i class="mdi mdi-close"></i>
                            Отмена
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="mdi mdi-check"></i>
                            Создать
                        </button>
                    </div>
                </form>

                <!-- Просмотр брони -->
                <div v-else-if="modal.type === 'view'" class="space-y-4">
                    <div class="space-y-2">
                        <p class="text-gray-400">Клиент</p>
                        <p class="text-white">
                            {{ modal.data?.extendedProps?.client_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <p class="text-gray-400">Телефон</p>
                        <p class="text-white">
                            {{ modal.data?.extendedProps?.client_phone }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <p class="text-gray-400">Время</p>
                        <p class="text-white">
                            {{ new Date(modal.data?.start).toLocaleString() }} -
                            {{ new Date(modal.data?.end).toLocaleString() }}
                        </p>
                    </div>

                    <div
                        class="space-y-2"
                        v-if="modal.data?.extendedProps?.comment"
                    >
                        <p class="text-gray-400">Комментарий</p>
                        <p class="text-white">
                            {{ modal.data.extendedProps.comment }}
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="handleDelete" class="btn-danger">
                            <i class="mdi mdi-delete"></i>
                            Удалить
                        </button>
                        <button
                            @click="appStore.closeModal"
                            class="btn-secondary"
                        >
                            <i class="mdi mdi-close"></i>
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
