<script setup>
import { useAppCalendarStore } from "@/stores/appCalendarStore";
import gsap from "gsap";
import { storeToRefs } from "pinia";
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import CreateContent from "./createContent.vue";
import SearchContent from "./searchContent.vue";
import ShowContent from "./showContent.vue";
import UpdateContent from "./updateContent.vue";

const appStore = useAppCalendarStore();
const { modal } = storeToRefs(appStore);

const modalRef = ref(null);
const overlayRef = ref(null);
const contentRef = ref(null);
const isAnimating = ref(false);

const modalComponents = {
    create: CreateContent,
    update: UpdateContent,
    view: ShowContent,
    search: SearchContent,
};

const toggleBodyScroll = (disable) => {
    document.body.style.overflow = disable ? "hidden" : "";
};

const showModal = async () => {
    if (isAnimating.value) return;
    isAnimating.value = true;

    await nextTick();

    if (!overlayRef.value || !contentRef.value) {
        isAnimating.value = false;
        return;
    }

    toggleBodyScroll(true);

    // Установим начальные значения
    gsap.set(overlayRef.value, { opacity: 0 });
    gsap.set(contentRef.value, {
        y: 20,
        opacity: 0,
        scale: 0.95,
    });

    const tl = gsap.timeline({
        defaults: { duration: 0.3, ease: "power2.out" },
        onComplete: () => {
            isAnimating.value = false;
        },
    });

    tl.to(overlayRef.value, {
        opacity: 1,
        duration: 0.2,
    });

    tl.to(
        contentRef.value,
        {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.3,
        },
        "-=0.1"
    );
};

const hideModal = async () => {
    if (isAnimating.value || !modal.value.isOpen) return;
    isAnimating.value = true;

    if (!overlayRef.value || !contentRef.value) {
        appStore.closeModal();
        toggleBodyScroll(false);
        isAnimating.value = false;
        return;
    }

    const tl = gsap.timeline({
        defaults: { duration: 0.2, ease: "power2.in" },
        onComplete: () => {
            appStore.closeModal();
            toggleBodyScroll(false);
            isAnimating.value = false;
        },
    });

    tl.to(contentRef.value, {
        y: 20,
        opacity: 0,
        scale: 0.95,
        duration: 0.2,
    });

    tl.to(
        overlayRef.value,
        {
            opacity: 0,
            duration: 0.2,
        },
        "-=0.1"
    );
};

// Следим за изменением состояния модального окна
watch(
    () => modal.value.isOpen,
    async (newValue, oldValue) => {
        if (newValue && !oldValue) {
            await nextTick();
            showModal();
        }
    }
);

// Обработка клавиши Escape
const handleEscape = (e) => {
    if (e.key === "Escape" && modal.value.isOpen && !isAnimating.value) {
        hideModal();
    }
};

onMounted(() => {
    document.addEventListener("keydown", handleEscape);
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleEscape);
    toggleBodyScroll(false);
});
</script>

<template>
    <div
        v-show="modal.isOpen"
        ref="modalRef"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="hideModal"
    >
        <div class="flex min-h-screen items-center justify-center p-4">
            <div
                ref="overlayRef"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm"
            ></div>

            <div
                ref="contentRef"
                class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-gray-800 border border-gray-700 shadow-xl"
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
                        @click="hideModal"
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
                        @close="hideModal"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>
