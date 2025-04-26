<script>
import { useCalendarStore } from "@/stores/calendarStore";
import debounce from "lodash/debounce";
import { ref, watch } from "vue";

export default {
    name: "SearchContent",

    setup() {
        const calendarStore = useCalendarStore();
        const searchQuery = ref("");
        const searchResults = ref([]);
        const isLoading = ref(false);
        const error = ref(null);

        const performSearch = debounce(async (query) => {
            if (!query.trim()) {
                searchResults.value = [];
                return;
            }

            isLoading.value = true;
            error.value = null;

            try {
                axios
                    .get(`/api/search?q=${encodeURIComponent(query.trim())}`)
                    .then((response) => {
                        console.log(response.data);
                        searchResults.value = response.data;
                    })
                    .catch((err) => {
                        console.error("Search error:", err);
                    });
                const response = await fetch(
                    `/api/search?q=${encodeURIComponent(query.trim())}`
                );
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(
                        data.error || "Произошла ошибка при поиске"
                    );
                }

                searchResults.value = Array.isArray(data) ? data : [];
            } catch (err) {
                error.value = err.message || "Произошла ошибка при поиске";
                searchResults.value = [];
                console.error("Search error:", err);
            } finally {
                isLoading.value = false;
            }
        }, 300);

        watch(searchQuery, (newQuery) => {
            performSearch(newQuery);
        });

        const formatDateTime = (date) => {
            return new Date(date).toLocaleString("ru-RU", {
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        };

        return {
            searchQuery,
            searchResults,
            isLoading,
            error,
            formatDateTime,
        };
    },
};
</script>

<template>
    <div class="space-y-4">
        <!-- Поисковая строка -->
        <div class="relative">
            <div class="relative">
                <i
                    class="mdi mdi-magnify absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                ></i>
                <input
                    v-model="searchQuery"
                    type="text"
                    class="w-full pl-10 pr-4 py-2 bg-gray-700/50 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                    placeholder="Поиск по имени, телефону или комментарию..."
                />
            </div>

            <!-- Индикатор загрузки -->
            <div
                v-if="isLoading"
                class="absolute right-3 top-1/2 -translate-y-1/2"
            >
                <div
                    class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"
                ></div>
            </div>
        </div>

        <!-- Сообщение об ошибке -->
        <div
            v-if="error"
            class="p-2 bg-red-500/20 border border-red-600/30 rounded-md text-sm text-red-200"
        >
            {{ error }}
        </div>

        <!-- Результаты поиска -->
        <div v-if="searchResults.length > 0" class="space-y-2">
            <div
                v-for="result in searchResults"
                :key="`${result.type}-${result.id}`"
                class="bg-gray-700/30 rounded-md p-2.5 hover:bg-gray-700/50 transition-colors cursor-pointer"
            >
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <i class="mdi mdi-account text-gray-400"></i>
                            <span class="text-sm text-white">{{
                                result.client_name
                            }}</span>
                            <span
                                v-if="result.type === 'booking'"
                                class="text-xs px-1.5 py-0.5 bg-blue-500/20 text-blue-300 rounded"
                            >
                                Бронирование
                            </span>
                            <span
                                v-else
                                class="text-xs px-1.5 py-0.5 bg-purple-500/20 text-purple-300 rounded"
                            >
                                Клиент
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="mdi mdi-phone text-gray-400"></i>
                            <span class="text-sm text-gray-300">{{
                                result.client_phone
                            }}</span>
                        </div>
                        <div
                            v-if="result.comment"
                            class="flex items-start gap-2 mt-1"
                        >
                            <i
                                class="mdi mdi-comment-text-outline text-gray-400 mt-0.5"
                            ></i>
                            <span class="text-sm text-gray-300">{{
                                result.comment
                            }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-400">
                            {{ formatDateTime(result.start) }}
                        </div>
                        <div class="text-sm text-green-400">
                            {{ result.total_amount }} ₽
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Пустой результат -->
        <div
            v-else-if="searchQuery && !isLoading"
            class="text-center py-4 text-gray-400"
        >
            Ничего не найдено
        </div>
    </div>
</template>

<style scoped></style>
