<script>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { useCalendarStore } from "@/stores/calendarStore";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";
import { Russian } from "flatpickr/dist/l10n/ru.js";
import { onMounted, ref } from "vue";

export default {
    name: "CreateContent",

    props: {
        modalData: {
            type: Object,
            required: false,
            default: () => ({
                start: new Date(),
                end: new Date(),
            }),
        },
    },

    setup(props, { emit }) {
        const appStore = useAppCalendarStore();
        const calendarStore = useCalendarStore();

        const startTimeInput = ref(null);
        const endTimeInput = ref(null);

        const paymentTypes = [
            { id: "cash", name: "Наличные" },
            { id: "card", name: "Картой" },
            { id: "transfer", name: "Переводом" },
        ];
        console.log(props.modalData);
        const form = ref({
            client_name: "",
            client_phone: "",
            comment: "",
            start_datetime: props.modalData?.start || new Date(),
            end_datetime: props.modalData?.end || new Date(),
            sauna_id: appStore.selectedSauna,
            prepayment: 0,
            total_amount: 0,
            payment_type: "cash",
        });

        onMounted(() => {
            const commonConfig = {
                enableTime: true,
                dateFormat: "d.m.Y H:i",
                locale: Russian,
                minuteIncrement: 60,
                time_24hr: true,
            };

            flatpickr(startTimeInput.value, {
                ...commonConfig,
                defaultDate: form.value.start_datetime,
                onChange: ([date]) => {
                    form.value.start_datetime = date;
                },
            });

            flatpickr(endTimeInput.value, {
                ...commonConfig,
                defaultDate: form.value.end_datetime,
                onChange: ([date]) => {
                    form.value.end_datetime = date;
                },
            });
        });

        const handleSubmit = async () => {
            try {
                await calendarStore.createBooking(form.value);
                emit("close");
            } catch (error) {
                console.error("Error creating booking:", error);
            }
        };

        return {
            form,
            startTimeInput,
            endTimeInput,
            paymentTypes,
            handleSubmit,
        };
    },
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Время -->
        <div class="grid grid-cols-2 gap-3">
            <div class="form-group">
                <label class="block text-xs text-gray-400 mb-1"
                    >Время начала</label
                >
                <div class="relative">
                    <i
                        class="mdi mdi-clock-start absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"
                    ></i>
                    <input
                        ref="startTimeInput"
                        type="text"
                        class="w-full h-9 pl-8 pr-3 bg-gray-700/50 border border-gray-600 rounded-md text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-colors"
                        placeholder="Начало"
                    />
                </div>
            </div>
            <div class="form-group">
                <label class="block text-xs text-gray-400 mb-1"
                    >Время окончания</label
                >
                <div class="relative">
                    <i
                        class="mdi mdi-clock-end absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"
                    ></i>
                    <input
                        ref="endTimeInput"
                        type="text"
                        class="w-full h-9 pl-8 pr-3 bg-gray-700/50 border border-gray-600 rounded-md text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-colors"
                        placeholder="Окончание"
                    />
                </div>
            </div>
        </div>

        <!-- Клиент -->
        <div class="space-y-3">
            <div class="form-group">
                <label class="block text-xs text-gray-400 mb-1"
                    >Имя клиента</label
                >
                <div class="relative">
                    <i
                        class="mdi mdi-account absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"
                    ></i>
                    <input
                        v-model="form.client_name"
                        type="text"
                        class="w-full h-9 pl-8 pr-3 bg-gray-700/50 border border-gray-600 rounded-md text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-colors"
                        required
                        placeholder="Введите имя"
                    />
                </div>
            </div>

            <div class="form-group">
                <label class="block text-xs text-gray-400 mb-1">Телефон</label>
                <div class="relative">
                    <i
                        class="mdi mdi-phone absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"
                    ></i>
                    <input
                        v-model="form.client_phone"
                        type="tel"
                        v-maska
                        data-maska="+7 (###) ###-##-##"
                        class="w-full h-9 pl-8 pr-3 bg-gray-700/50 border border-gray-600 rounded-md text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-colors"
                        required
                        placeholder="+7 (___) ___-__-__"
                    />
                </div>
            </div>
        </div>

        <!-- Оплата -->
        <div class="grid grid-cols-2 gap-3">
            <div class="form-group">
                <label class="block text-xs text-gray-400 mb-1"
                    >Предоплата</label
                >
                <div class="relative">
                    <i
                        class="mdi mdi-currency-rub absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"
                    ></i>
                    <input
                        v-model.number="form.prepayment"
                        type="number"
                        min="0"
                        class="w-full h-9 pl-8 pr-3 bg-gray-700/50 border border-gray-600 rounded-md text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-colors"
                        placeholder="0"
                    />
                </div>
            </div>
            <div class="form-group">
                <label class="block text-xs text-gray-400 mb-1"
                    >Общая сумма</label
                >
                <div class="relative">
                    <i
                        class="mdi mdi-cash-multiple absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"
                    ></i>
                    <input
                        v-model.number="form.total_amount"
                        type="number"
                        min="0"
                        class="w-full h-9 pl-8 pr-3 bg-gray-700/50 border border-gray-600 rounded-md text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-colors"
                        required
                        placeholder="Введите сумму"
                    />
                </div>
            </div>
        </div>

        <!-- Тип оплаты -->
        <div class="form-group">
            <label class="block text-xs text-gray-400 mb-1">Тип оплаты</label>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="type in paymentTypes"
                    :key="type.id"
                    type="button"
                    class="h-9 rounded-md text-sm flex items-center justify-center gap-1.5 border transition-colors"
                    :class="{
                        'bg-blue-500/10 border-blue-500/50 text-blue-400':
                            form.payment_type === type.id,
                        'bg-gray-700/50 border-gray-600 text-gray-400 hover:bg-gray-700 hover:border-gray-500':
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

        <!-- Комментарий -->
        <div class="form-group">
            <label class="block text-xs text-gray-400 mb-1">Комментарий</label>
            <div class="relative">
                <i
                    class="mdi mdi-comment-text absolute left-2.5 top-2.5 text-gray-500 text-sm"
                ></i>
                <textarea
                    v-model="form.comment"
                    rows="3"
                    class="w-full pl-8 pr-3 py-2 bg-gray-700/50 border border-gray-600 rounded-md text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/50 transition-colors"
                    placeholder="Добавьте комментарий..."
                ></textarea>
            </div>
        </div>

        <!-- Кнопки -->
        <div class="flex justify-end items-center gap-2 pt-2">
            <button
                type="button"
                @click="$emit('close')"
                class="h-8 px-3 text-sm border border-gray-600 rounded-md text-gray-400 hover:bg-gray-700/50 hover:border-gray-500 transition-colors"
            >
                Отмена
            </button>
            <button
                type="submit"
                class="h-8 px-3 text-sm bg-blue-500/10 border border-blue-500/50 rounded-md text-blue-400 hover:bg-blue-500/20 transition-colors"
            >
                Создать
            </button>
        </div>
    </form>
</template>

<style scoped></style>
