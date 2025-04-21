<script>
export default {
    name: "Footer",
};
</script>

<template>
    <footer class="bg-black/85 py-6 w-full text-gray-100">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex justify-center items-center">
                <div class="block text-center">
                    <h2 class="text-lg font-bold">Ждем вас в гости</h2>
                    <ul class="flex justify-center items-center gap-4 mt-2">
                        <li>
                            <a
                                href="tel:+79528018800"
                                class="text-xs text-gray-400"
                            >
                                <span class="text-xs text-gray-400">📞</span>
                                +7 (952) 801-88-00
                            </a>
                        </li>
                        <li>
                            <a
                                href="mailto:teplo@teplo-na-beloy.ru"
                                class="text-xs text-gray-400"
                            >
                                <span class="text-xs text-gray-400">📍</span>
                                Белая ул., 2
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-6 mt-4">
                <p class="text-xs text-gray-400 text-center">
                    &copy; 2025 Тепло на белой. Все права защищены.
                </p>
            </div>
        </div>
    </footer>
</template>

<style scoped></style>
