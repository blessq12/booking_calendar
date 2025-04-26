<script>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { useCalendarStore } from "@/stores/calendarStore";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";
import { Russian } from "flatpickr/dist/l10n/ru.js";

export default {
    name: "BookingForm",
    props: {
        modalData: {
            type: Object,
            required: true,
        },
        mode: {
            type: String,
            default: "create",
            validator: (value) => ["create", "update"].includes(value),
        },
    },

    data() {
        return {
            paymentTypes: [
                { id: "cash", name: "Наличные" },
                { id: "card", name: "Картой" },
                { id: "transfer", name: "Переводом" },
            ],
            form: {
                client_name: "",
                client_phone: "",
                comment: "",
                start_datetime: this.getInitialStartTime(),
                end_datetime: this.getInitialEndTime(),
                sauna_id:
                    this.modalData?.sauna_id || this.appStore.selectedSauna,
                prepayment: 0,
                total_amount: 0,
                payment_type: "cash",
            },
        };
    },

    computed: {
        isUpdate() {
            return this.mode === "update";
        },
        submitButtonText() {
            return this.isUpdate ? "Обновить" : "Создать";
        },
    },

    mounted() {
        this.initFlatpickr();
        if (this.isUpdate && this.modalData) {
            this.initializeFormData();
        }
    },

    methods: {
        getInitialStartTime() {
            if (this.modalData?.start) {
                return this.modalData.start;
            }

            const now = new Date();
            now.setMinutes(0); // Округляем до часа
            return now;
        },

        getInitialEndTime() {
            if (this.modalData?.end) {
                return this.modalData.end;
            }

            const end = new Date(this.getInitialStartTime());
            end.setHours(end.getHours() + 1);
            return end;
        },

        initFlatpickr() {
            const commonConfig = {
                enableTime: true,
                dateFormat: "d.m.Y H:i",
                locale: Russian,
                minuteIncrement: 60, // Шаг в 1 час
                time_24hr: true,
                onChange: ([date], dateStr, instance) => {
                    const field = instance.element.dataset.field;
                    this.form[field] = date;
                },
            };

            flatpickr(this.$refs.startTimeInput, {
                ...commonConfig,
                defaultDate: this.form.start_datetime,
            });

            flatpickr(this.$refs.endTimeInput, {
                ...commonConfig,
                defaultDate: this.form.end_datetime,
            });
        },

        initializeFormData() {
            const data = this.modalData;
            this.form = {
                ...this.form,
                client_name: data.client_name || "",
                client_phone: data.client_phone || "",
                comment: data.comment || "",
                start_datetime: data.start || this.getInitialStartTime(),
                end_datetime: data.end || this.getInitialEndTime(),
                prepayment: data.prepayment || 0,
                total_amount: data.total_amount || 0,
                payment_type: data.payment_type || "cash",
            };
        },

        async handleSubmit() {
            try {
                if (this.isUpdate) {
                    await this.calendarStore.updateBooking({
                        id: this.modalData.id,
                        ...this.form,
                    });
                } else {
                    await this.calendarStore.createBooking(this.form);
                }
                this.$emit("close");
            } catch (error) {
                console.error("Error handling booking:", error);
            }
        },
    },

    setup() {
        const appStore = useAppCalendarStore();
        const calendarStore = useCalendarStore();

        return {
            appStore,
            calendarStore,
        };
    },
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Выбор времени -->
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Время начала</label>
                <div class="relative">
                    <i
                        class="mdi mdi-clock-start absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                    ></i>
                    <input
                        ref="startTimeInput"
                        type="text"
                        data-field="start_datetime"
                        class="form-input pl-10 w-full bg-gray-700 border-gray-600 text-white rounded-lg"
                        placeholder="Выберите время начала"
                    />
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Время окончания</label>
                <div class="relative">
                    <i
                        class="mdi mdi-clock-end absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                    ></i>
                    <input
                        ref="endTimeInput"
                        type="text"
                        data-field="end_datetime"
                        class="form-input pl-10 w-full bg-gray-700 border-gray-600 text-white rounded-lg"
                        placeholder="Выберите время окончания"
                    />
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Имя клиента</label>
            <div class="relative">
                <i
                    class="mdi mdi-account absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                ></i>
                <input
                    v-model="form.client_name"
                    type="text"
                    class="form-input pl-10 w-full bg-gray-700 border-gray-600 text-white rounded-lg"
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
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                    class="form-input pl-10 w-full bg-gray-700 border-gray-600 text-white rounded-lg"
                    required
                />
            </div>
        </div>

        <!-- Блок оплаты -->
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Предоплата</label>
                <div class="relative">
                    <i
                        class="mdi mdi-currency-rub absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                    ></i>
                    <input
                        v-model.number="form.prepayment"
                        type="number"
                        min="0"
                        class="form-input pl-10 w-full bg-gray-700 border-gray-600 text-white rounded-lg"
                    />
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Общая сумма</label>
                <div class="relative">
                    <i
                        class="mdi mdi-cash-multiple absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                    ></i>
                    <input
                        v-model.number="form.total_amount"
                        type="number"
                        min="0"
                        class="form-input pl-10 w-full bg-gray-700 border-gray-600 text-white rounded-lg"
                        required
                    />
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Тип оплаты</label>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="type in paymentTypes"
                    :key="type.id"
                    type="button"
                    class="px-4 py-2 rounded-lg text-sm flex items-center justify-center gap-2"
                    :class="{
                        'bg-blue-600 text-white': form.payment_type === type.id,
                        'bg-gray-700 text-gray-300':
                            form.payment_type !== type.id,
                    }"
                    @click="form.payment_type = type.id"
                >
                    <i
                        :class="{
                            'mdi mdi-cash': type.id === 'cash',
                            'mdi mdi-credit-card': type.id === 'card',
                            'mdi mdi-bank-transfer': type.id === 'transfer',
                        }"
                    ></i>
                    {{ type.name }}
                </button>
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
                    class="form-input pl-10 w-full bg-gray-700 border-gray-600 text-white rounded-lg"
                ></textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button
                type="button"
                @click="$emit('close')"
                class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-all duration-200"
            >
                <i class="mdi mdi-close"></i>
                Отмена
            </button>
            <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200"
            >
                <i class="mdi mdi-check"></i>
                {{ submitButtonText }}
            </button>
        </div>
    </form>
</template>

<style scoped>
:deep(.flatpickr-calendar) {
    background: #1f2937 !important;
    border-color: #374151 !important;
    color: white !important;
}

:deep(.flatpickr-calendar .flatpickr-months),
:deep(.flatpickr-calendar .flatpickr-weekdays) {
    background: #111827 !important;
}

:deep(.flatpickr-calendar .flatpickr-day) {
    color: white !important;
}

:deep(.flatpickr-calendar .flatpickr-day.selected) {
    background: #2563eb !important;
    border-color: #2563eb !important;
}

:deep(.flatpickr-calendar .flatpickr-day:hover) {
    background: #374151 !important;
}

:deep(.flatpickr-calendar .flatpickr-time) {
    background: #1f2937 !important;
    border-top-color: #374151 !important;
}

:deep(.flatpickr-calendar .numInput) {
    background: #374151 !important;
    color: white !important;
    border-color: #4b5563 !important;
}
</style>
