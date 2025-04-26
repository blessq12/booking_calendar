<script>
export default {
    name: "Footer",
};
</script>

<template>
    <div class="bg-gradient-to-b from-gray-900 to-black py-8">
        <div class="container mx-auto px-4">
            <p class="text-center text-gray-500 text-sm">
                &copy; {{ new Date().getFullYear() }} Тепло на белой. Все права
                защищены.
            </p>
        </div>
    </div>
</template>

<style scoped></style>
