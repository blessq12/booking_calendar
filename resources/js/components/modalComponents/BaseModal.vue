<script setup>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import { storeToRefs } from "pinia";
import CreateContent from "./createContent.vue";
import SearchContent from "./searchContent.vue";
import ShowContent from "./showContent.vue";
import UpdateContent from "./updateContent.vue";

const appStore = useAppCalendarStore();
const { modal } = storeToRefs(appStore);

const modalComponents = {
    create: CreateContent,
    update: UpdateContent,
    view: ShowContent,
    search: SearchContent,
};
</script>

<template>
    <Transition name="modal">
        <div
            v-if="modal.isOpen"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click="appStore.closeModal"
        >
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

                <div
                    class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-gray-800 border border-gray-700 shadow-xl transition-all"
                    @click.stop
                >
                    <!-- Заголовок -->
                    <div
                        class="flex items-center justify-between border-b border-gray-700 px-4 py-3"
                    >
                        <h3 class="text-lg font-medium text-gray-100">
                            {{ modal.title || "Модальное окно" }}
                        </h3>
                        <button
                            @click="appStore.closeModal"
                            class="rounded-md p-1 text-gray-400 hover:text-gray-300 hover:bg-gray-700/50 transition-colors"
                        >
                            <i class="mdi mdi-close text-xl"></i>
                        </button>
                    </div>

                    <!-- Контент -->
                    <div
                        class="px-4 py-4 max-h-[calc(100vh-10rem)] overflow-y-auto"
                    >
                        <component
                            :is="modalComponents[modal.type]"
                            v-if="modal.type && modalComponents[modal.type]"
                            :modal-data="modal.data"
                            @close="appStore.closeModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
