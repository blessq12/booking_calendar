<script>
export default {
    name: "Header",
    data() {
        return {
            selectedSauna: null, // Переменная для выбранной сауны
            saunas: [], // Массив сауны
            calendarOptions: {
                initialView: "dayGridMonth", // Начальный вид календаря
            },
        };
    },
    methods: {
        handleSaunaChange() {
            // Логика обработки изменения сауны
        },
        prevPeriod() {
            // Логика для перехода к предыдущему периоду
        },
        nextPeriod() {
            // Логика для перехода к следующему периоду
        },
        goToToday() {
            // Логика для перехода к сегодняшнему дню
        },
        switchView(view) {
            // Логика для переключения вида календаря
        },
    },
};
</script>

<template>
    <div class="bg-gray-900 text-gray-100 py-4">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="bg-gray-800 rounded-xl shadow-2xl p-4">
                <div class="flex flex-col space-y-4">
                    <div class="sm:w-full md:hidden relative">
                        <select
                            v-model="selectedSauna"
                            @change="handleSaunaChange"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 bg-blue-600 text-white focus:ring-2 focus:ring-blue-500/20 transition-all duration-200 hover:bg-blue-700 cursor-pointer appearance-none"
                        >
                            <option value="" disabled>Выберите сауну</option>
                            <option
                                v-for="sauna in saunas"
                                :key="sauna.id"
                                :value="sauna.id"
                                class="bg-gray-800 text-gray-100 py-2"
                            >
                                {{ sauna.name }}
                            </option>
                        </select>
                        <i
                            class="fa fa-home absolute left-3 top-1/2 -translate-y-1/2 text-white/80"
                        ></i>
                        <i
                            class="fa fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-white/80"
                        ></i>
                    </div>
                    <div class="hidden md:block relative">
                        <select
                            v-model="selectedSauna"
                            @change="handleSaunaChange"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 bg-blue-600 text-white focus:ring-2 focus:ring-blue-500/20 transition-all duration-200 hover:bg-blue-700 cursor-pointer appearance-none"
                        >
                            <option value="" disabled>Выберите сауну</option>
                            <option
                                v-for="sauna in saunas"
                                :key="sauna.id"
                                :value="sauna.id"
                                class="bg-gray-800 text-gray-100 py-2"
                            >
                                {{ sauna.name }}
                            </option>
                        </select>
                        <i
                            class="fa fa-home absolute left-3 top-1/2 -translate-y-1/2 text-white/80"
                        ></i>
                        <i
                            class="fa fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-white/80"
                        ></i>
                    </div>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <button
                                @click="prevPeriod"
                                class="p-2 rounded-lg bg-gray-700/50 text-gray-300 hover:bg-gray-600 transition-colors"
                            >
                                <i class="mdi mdi-chevron-left"></i>
                            </button>
                            <button
                                @click="nextPeriod"
                                class="p-2 rounded-lg bg-gray-700/50 text-gray-300 hover:bg-gray-600 transition-colors"
                            >
                                <i class="mdi mdi-chevron-right"></i>
                            </button>
                            <button
                                @click="goToToday"
                                class="px-2 py-1.5 rounded-lg bg-gray-700/50 text-gray-300 hover:bg-gray-600 transition-colors flex gap-2"
                            >
                                <i class="mdi mdi-calendar-today text-lg"></i>
                                <span class="hidden sm:inline">Сегодня</span>
                            </button>
                        </div>
                        <div class="hidden sm:block md:w-1/2"></div>
                        <div
                            class="flex items-center bg-gray-700/50 rounded-lg p-1"
                        >
                            <button
                                @click="switchView('dayGridMonth')"
                                class="px-3 py-1 rounded-md transition-all duration-200 flex items-center gap-2"
                                :class="{
                                    'bg-blue-600 text-white':
                                        calendarOptions.initialView ===
                                        'dayGridMonth',
                                    'text-gray-400 hover:text-gray-200':
                                        calendarOptions.initialView !==
                                        'dayGridMonth',
                                }"
                            >
                                <i class="mdi mdi-calendar-month text-lg"></i>
                                <span class="hidden sm:inline">Месяц</span>
                            </button>
                            <button
                                @click="switchView('timeGridWeek')"
                                class="px-3 py-1 rounded-md transition-all duration-200 flex items-center gap-2"
                                :class="{
                                    'bg-blue-600 text-white':
                                        calendarOptions.initialView ===
                                        'timeGridWeek',
                                    'text-gray-400 hover:text-gray-200':
                                        calendarOptions.initialView !==
                                        'timeGridWeek',
                                }"
                            >
                                <i class="mdi mdi-calendar-week text-lg"></i>
                                <span class="hidden sm:inline">Неделя</span>
                            </button>
                            <button
                                @click="switchView('timeGridDay')"
                                class="px-3 py-1 rounded-md transition-all duration-200 flex items-center gap-2"
                                :class="'bg-blue-600 text-white'"
                            >
                                <i class="mdi mdi-calendar-today text-lg"></i>
                                <span class="hidden sm:inline">День</span>
                            </button>
                        </div>
                    </div>
                    <button
                        class="w-full group px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                    >
                        <i
                            class="mdi mdi-plus text-xl group-hover:rotate-90 transition-transform duration-300"
                        ></i>
                        <span>Создать запись</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
