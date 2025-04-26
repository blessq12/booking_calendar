<script>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { useCalendarStore } from "@/stores/calendarStore";
import gsap from "gsap";
import { storeToRefs } from "pinia";

export default {
    name: "Header",
    inject: ["user"],
    setup() {
        const appStore = useAppCalendarStore();
        const calendarStore = useCalendarStore();
        const { saunas, selectedSauna, view } = storeToRefs(appStore);
        return {
            appStore,
            calendarStore,
            saunas,
            selectedSauna,
            view,
        };
    },
    mounted() {
        // Анимация появления основных блоков
        gsap.from(".calendar-block", {
            duration: 0.6,
            y: 20,
            opacity: 0,
            stagger: 0.2,
            ease: "power2.out",
        });

        // Анимация появления верхнего меню
        gsap.from(".top-menu-item", {
            duration: 0.5,
            y: -20,
            opacity: 0,
            stagger: 0.1,
            ease: "back.out",
            delay: 0.2,
        });
    },
    methods: {
        getLogoutUrl() {
            return window.location.origin + "/auth/logout";
        },

        handleSaunaSelect(saunaId) {
            this.appStore.setSelectedSauna(saunaId);
        },

        handleViewModeChange(mode) {
            // Анимация при смене режима
            gsap.to(".view-mode-container", {
                duration: 0.3,
                scale: 0.95,
                yoyo: true,
                repeat: 1,
                ease: "power2.inOut",
            });
            this.appStore.setViewMode(mode);
        },

        animateHover(target, scale = 1.05) {
            gsap.to(target, {
                duration: 0.3,
                scale: scale,
                ease: "power2.out",
            });
        },

        animateHoverOut(target) {
            gsap.to(target, {
                duration: 0.3,
                scale: 1,
                ease: "power2.out",
            });
        },

        openCreateBookingModal() {
            this.appStore.openModal("create");
        },

        openSearchModal() {
            this.appStore.openModal("search");
        },

        animateTopMenuHover(target) {
            gsap.to(target, {
                duration: 0.3,
                y: -2,
                scale: 1.05,
                ease: "power2.out",
            });
        },

        animateTopMenuHoverOut(target) {
            gsap.to(target, {
                duration: 0.3,
                y: 0,
                scale: 1,
                ease: "power2.out",
            });
        },
    },
};
</script>

<template>
    <div class="bg-gradient-to-t from-gray-900 to-black py-4 text-gray-100">
        <div
            class="mx-auto max-w-7xl px-4 md:px-6 flex justify-between gap-4 items-center"
        >
            <p class="text-xs top-menu-item">Привет, {{ user.name }}</p>
            <div class="flex align-items gap-2">
                <button
                    @click="openCreateBookingModal"
                    @mouseenter="animateTopMenuHover($event.target)"
                    @mouseleave="animateTopMenuHoverOut($event.target)"
                    class="top-menu-item flex justify-center items-center gap-2 text-xs text-white bg-gray-800 px-5 py-2 sm:px-3 sm:py-2 rounded-md cursor-pointer"
                >
                    <i
                        class="mdi mdi-plus text-md font-bold text-green-500"
                    ></i>
                    <span class="hidden sm:inline">Новая запись</span>
                </button>
                <button
                    @click="openSearchModal"
                    @mouseenter="animateTopMenuHover($event.target)"
                    @mouseleave="animateTopMenuHoverOut($event.target)"
                    class="top-menu-item flex justify-center items-center gap-2 text-xs text-white bg-gray-800 px-5 py-2 sm:px-3 sm:py-2 rounded-md cursor-pointer"
                >
                    <i
                        class="mdi mdi-magnify text-md font-bold text-blue-500"
                    ></i>
                    <span class="hidden sm:inline">Поиск</span>
                </button>
            </div>
            <a
                :href="getLogoutUrl()"
                @mouseenter="animateTopMenuHover($event.target)"
                @mouseleave="animateTopMenuHoverOut($event.target)"
                class="top-menu-item text-xs text-white bg-red-800 px-3 py-2 rounded-md cursor-pointer"
                >Выйти</a
            >
        </div>
    </div>
    <div class="bg-gray-900 text-gray-100 py-4">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="bg-gray-800 rounded-xl shadow-2xl p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="col-span-1 calendar-block">
                        <p class="text-xs text-gray-400 mb-2 py-1">
                            Выберите сауну
                        </p>
                        <div class="grid grid-cols-4 md:grid-cols-2 gap-2">
                            <button
                                v-for="sauna in saunas"
                                :key="sauna.id"
                                class="w-full justify-center cursor-pointer border rounded-md p-2 flex items-center gap-2 hover:bg-white/20 transition-all duration-200"
                                :class="{
                                    'bg-blue-900 border-blue-900':
                                        selectedSauna === sauna.id,
                                    'bg-gray-700 border-gray-700':
                                        selectedSauna !== sauna.id,
                                }"
                                @click="handleSaunaSelect(sauna.id)"
                            >
                                <i>🔥</i>
                                <p class="text-xs">{{ sauna.name }}</p>
                            </button>
                        </div>
                    </div>
                    <div class="col-span-1 calendar-block view-mode-container">
                        <p class="text-xs text-gray-400 mb-2 py-1">
                            Отображение:
                        </p>
                        <div class="flex flex-col gap-2">
                            <div class="flex w-full gap-2">
                                <button
                                    v-for="(mode, index) in [
                                        'timeGridDay',
                                        'timeGridWeek',
                                        'dayGridMonth',
                                    ]"
                                    :key="index"
                                    class="w-full cursor-pointer border rounded-md p-2 flex items-center justify-center gap-2 hover:bg-white/20 transition-all duration-200"
                                    :class="{
                                        'bg-blue-900 border-blue-900':
                                            view.mode === mode,
                                        'bg-gray-700 border-gray-700':
                                            view.mode !== mode,
                                    }"
                                    @click="handleViewModeChange(mode)"
                                    @mouseenter="animateHover($event.target)"
                                    @mouseleave="animateHoverOut($event.target)"
                                >
                                    <i
                                        class="mdi mdi-calendar text-blue-500"
                                    ></i>
                                    <p class="text-xs text-gray-200">
                                        {{
                                            mode === "timeGridDay"
                                                ? "День"
                                                : mode === "timeGridWeek"
                                                ? "Неделя"
                                                : "Месяц"
                                        }}
                                    </p>
                                </button>
                            </div>
                            <div class="w-full rounded-full">
                                <button
                                    @click="openSearchModal"
                                    class="w-full rounded-md border border-gray-700 py-2 flex items-center justify-center gap-2 hover:bg-white/20 transition-all duration-200"
                                >
                                    <i
                                        class="mdi mdi-magnify text-blue-500"
                                    ></i>
                                    <p class="text-xs text-gray-200">Поиск</p>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 calendar-block">
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between items-center">
                                <p class="text-xs text-gray-400">
                                    Ближайшие записи
                                </p>
                                <button
                                    class="text-xs text-white bg-blue-500 rounded-md px-2 py-1 cursor-pointer hover:bg-blue-600 transition-all duration-200"
                                >
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>

                            <ul class="flex flex-col gap-2">
                                <li
                                    v-for="event in calendarStore?.getCurrentEvents?.slice(
                                        0,
                                        2
                                    )"
                                    :key="event.id"
                                    class="flex justify-between border border-gray-700 bg-white/10 rounded-md p-3"
                                >
                                    <p class="text-xs font-bold">
                                        {{ event.extendedProps.client_name }}
                                    </p>
                                    <p class="text-xs">
                                        {{
                                            new Date(event.start)
                                                .toLocaleTimeString("ru-RU", {
                                                    hour: "2-digit",
                                                    minute: "2-digit",
                                                })
                                                .slice(0, 5)
                                        }}
                                        -
                                        {{
                                            new Date(event.end)
                                                .toLocaleTimeString("ru-RU", {
                                                    hour: "2-digit",
                                                    minute: "2-digit",
                                                })
                                                .slice(0, 5)
                                        }}
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.calendar-block {
    transform-origin: center;
    backface-visibility: hidden;
}

.top-menu-item {
    transform-origin: center;
    backface-visibility: hidden;
    will-change: transform;
}
</style>
