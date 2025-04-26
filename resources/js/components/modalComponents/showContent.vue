<script>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { useCalendarStore } from "@/stores/calendarStore";

export default {
    name: "ShowContent",
    props: {
        modalData: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            paymentTypes: {
                cash: "Наличные",
                card: "Картой",
                transfer: "Переводом",
            },
        };
    },

    methods: {
        formatDateTime(date) {
            return new Date(date).toLocaleString("ru-RU", {
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        async handleDelete() {
            if (!this.modalData?.id) return;

            if (confirm("Вы уверены, что хотите удалить эту бронь?")) {
                try {
                    console.log("Deleting booking:", {
                        url: `/api/bookings/${this.modalData.id}`,
                        method: "DELETE",
                        id: this.modalData.id,
                    });

                    await this.calendarStore.deleteBooking(this.modalData.id);
                    this.$emit("close");
                } catch (error) {
                    console.error("Error deleting booking:", error);
                }
            }
        },

        handleEdit() {
            console.log("Editing booking:", {
                id: this.modalData.id,
                data: { ...this.modalData, ...this.modalData.extendedProps },
            });

            this.appStore.modal.type = "update";
            this.appStore.modal.title = "Редактирование брони";
            this.appStore.modal.data = {
                id: this.modalData.id,
                start: this.modalData.start,
                end: this.modalData.end,
                ...this.modalData.extendedProps,
            };
        },
    },

    setup() {
        const calendarStore = useCalendarStore();
        const appStore = useAppCalendarStore();
        return { calendarStore, appStore };
    },
};
</script>

<template>
    <div class="space-y-4">
        <!-- Время -->
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="bg-gray-700/30 rounded-md p-2.5">
                <div class="flex items-center gap-2 text-blue-400 mb-1">
                    <i class="mdi mdi-clock-start"></i>
                    <span>Начало</span>
                </div>
                <p class="text-white">{{ formatDateTime(modalData?.start) }}</p>
            </div>
            <div class="bg-gray-700/30 rounded-md p-2.5">
                <div class="flex items-center gap-2 text-blue-400 mb-1">
                    <i class="mdi mdi-clock-end"></i>
                    <span>Конец</span>
                </div>
                <p class="text-white">{{ formatDateTime(modalData?.end) }}</p>
            </div>
        </div>
        <!-- Информация о клиенте -->
        <div class="bg-gray-700/30 rounded-md p-2.5 space-y-2">
            <div class="flex items-center gap-2 text-sm">
                <i class="mdi mdi-account text-gray-400 w-5"></i>
                <span class="text-gray-300">{{
                    modalData?.extendedProps?.client_name
                }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <i class="mdi mdi-phone text-gray-400 w-5"></i>
                <span class="text-gray-300">{{
                    modalData?.extendedProps?.client_phone
                }}</span>
            </div>
        </div>

        <!-- Информация об оплате -->
        <div class="bg-gray-700/30 rounded-md p-2.5 space-y-2">
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="flex items-center gap-2">
                    <i class="mdi mdi-currency-rub text-green-400 w-5"></i>
                    <span class="text-gray-300"
                        >{{ modalData?.extendedProps?.prepayment || 0 }} ₽</span
                    >
                </div>
                <div class="flex items-center gap-2">
                    <i class="mdi mdi-cash-multiple text-green-400 w-5"></i>
                    <span class="text-gray-300"
                        >{{ modalData?.extendedProps?.price || 0 }} ₽</span
                    >
                </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <i
                    :class="{
                        'mdi mdi-cash':
                            modalData?.extendedProps?.type === 'cash',
                        'mdi mdi-credit-card':
                            modalData?.extendedProps?.type === 'card',
                        'mdi mdi-bank-transfer':
                            modalData?.extendedProps?.type === 'transfer',
                    }"
                    class="text-green-400 w-5"
                ></i>
                <span class="text-gray-300">
                    {{ paymentTypes[modalData?.extendedProps?.type || "cash"] }}
                </span>
            </div>
        </div>

        <!-- Комментарий -->
        <div
            v-if="modalData?.extendedProps?.comment"
            class="bg-gray-700/30 rounded-md p-2.5"
        >
            <div class="flex items-center gap-2 text-sm mb-1">
                <i class="mdi mdi-comment-text text-gray-400"></i>
                <span class="text-gray-400">Комментарий</span>
            </div>
            <p class="text-sm text-gray-300">
                {{ modalData.extendedProps.comment }}
            </p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <button
                @click="handleDelete"
                class="px-3 py-1.5 text-xs bg-white/5 border border-red-500/20 text-red-300 rounded-md hover:bg-red-600 hover:text-white transition-colors"
            >
                <i class="mdi mdi-delete mr-1"></i>
                Удалить
            </button>
            <button
                @click="handleEdit"
                class="px-3 py-1.5 text-xs bg-white/5 border border-orange-500/20 text-orange-300 rounded-md hover:bg-orange-600 hover:text-white transition-colors"
            >
                <i class="mdi mdi-pencil mr-1"></i>
                Редактировать
            </button>
            <button
                @click="$emit('close')"
                class="px-3 py-1.5 text-xs bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors"
            >
                <i class="mdi mdi-close mr-1"></i>
                Закрыть
            </button>
        </div>
    </div>
</template>

<style scoped>
.space-y-6 > * + * {
    margin-top: 1.5rem;
}
</style>
