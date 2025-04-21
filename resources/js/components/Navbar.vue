<script>
export default {
    name: "Navbar",
};
</script>

<template>
    <div class="bg-black/85 w-full py-2 md:py-3">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img
                        src="/images/logo/logo.png"
                        class="w-12 h-12"
                        alt="logo"
                    />
                    <div
                        class="block leading-none hidden md:block text-gray-100"
                    >
                        <span class="block font-semibold">Тепло на белой</span>
                        <span class="block text-sm font-light">
                            Там, где тепло и уют
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        href="tel:+79528018800"
                        class="text-white text-sm font-light bg-gradient-to-br from-red-500 to-orange-500 rounded-full py-2 px-3 md:px-4"
                    >
                        📞
                        <span class="hidden md:inline">+7 (952) 801-88-00</span>
                    </a>
                    <a
                        href="https://yandex.ru/maps/67/tomsk/?ll=84.963102%2C56.494636&mode=routes&rtext=~56.494604%2C84.963255&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D47318889907&z=19.23"
                        target="_blank"
                        class="text-white text-sm font-light bg-gradient-to-br from-red-500 to-orange-500 rounded-full py-2 px-3 md:px-4"
                    >
                        📍
                        <span class="hidden md:inline">Как проехать</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
