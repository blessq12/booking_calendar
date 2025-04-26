<script>
export default {
    name: "CalendarModal",
    data() {
        return {
            showModal: false,
            bookingForm: {
                client_name: "",
                client_phone: "",
                comment: "",
            },
            validationErrors: {
                client_name: [],
                client_phone: [],
                start_datetime: [],
                end_datetime: [],
            },
            isLoading: false,
        };
    },
};
</script>

<template>
    <div
        v-if="showModal"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click="showModal = false"
    >
        <div
            class="w-full max-w-lg transform bg-gray-800 rounded-xl shadow-2xl border border-gray-700 transition-all"
            @click.stop
        >
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-100">
                        Создание брони
                    </h3>
                    <button
                        @click="showModal = false"
                        class="p-2 text-gray-400 hover:text-gray-300 rounded-lg hover:bg-gray-700 transition-all duration-200"
                    >
                        <i class="mdi mdi-close text-xl"></i>
                    </button>
                </div>

                <!-- Форма -->
                <div class="space-y-4">
                    <!-- Имя клиента -->
                    <div class="form-group">
                        <label class="form-label"> Имя клиента </label>
                        <div class="relative">
                            <i
                                class="mdi mdi-account absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                v-model="bookingForm.client_name"
                                type="text"
                                class="form-input"
                                :class="{
                                    error: validationErrors.client_name,
                                }"
                            />
                        </div>
                        <p
                            v-if="validationErrors.client_name"
                            class="error-message"
                        >
                            {{ validationErrors.client_name[0] }}
                        </p>
                    </div>

                    <!-- Телефон -->
                    <div class="form-group">
                        <label class="form-label"> Телефон </label>
                        <div class="relative">
                            <i
                                class="mdi mdi-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                v-model="bookingForm.client_phone"
                                v-maska
                                data-maska="+7 (###) ###-##-##"
                                type="tel"
                                class="form-input"
                                :class="{
                                    error: validationErrors.client_phone,
                                }"
                            />
                        </div>
                        <p
                            v-if="validationErrors.client_phone"
                            class="error-message"
                        >
                            {{ validationErrors.client_phone[0] }}
                        </p>
                    </div>

                    <!-- Время начала -->
                    <div class="form-group">
                        <label class="form-label"> Начало </label>
                        <div class="relative">
                            <i
                                class="mdi mdi-clock-start absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                id="start_datetime"
                                type="text"
                                class="form-input"
                                :class="{
                                    error: validationErrors.start_datetime,
                                }"
                                placeholder="Выберите дату и время начала"
                            />
                        </div>
                        <p
                            v-if="validationErrors.start_datetime"
                            class="error-message"
                        >
                            {{ validationErrors.start_datetime[0] }}
                        </p>
                    </div>

                    <!-- Время окончания -->
                    <div class="form-group">
                        <label class="form-label"> Окончание </label>
                        <div class="relative">
                            <i
                                class="mdi mdi-clock-end absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                id="end_datetime"
                                type="text"
                                class="form-input"
                                :class="{
                                    error: validationErrors.end_datetime,
                                }"
                                placeholder="Выберите дату и время окончания"
                            />
                        </div>
                        <p
                            v-if="validationErrors.end_datetime"
                            class="error-message"
                        >
                            {{ validationErrors.end_datetime[0] }}
                        </p>
                    </div>

                    <!-- Комментарий -->
                    <div class="form-group">
                        <label class="form-label"> Комментарий </label>
                        <div class="relative">
                            <i
                                class="mdi mdi-comment-text absolute left-3 top-3 text-gray-400"
                            ></i>
                            <textarea
                                v-model="bookingForm.comment"
                                rows="3"
                                class="form-input"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showModal = false" class="btn-secondary">
                        <i class="mdi mdi-close"></i>
                        Отмена
                    </button>
                    <button
                        @click="createBooking"
                        :disabled="isLoading"
                        class="btn-primary"
                    >
                        <i class="mdi mdi-check"></i>
                        {{ isLoading ? "Создание..." : "Создать" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
