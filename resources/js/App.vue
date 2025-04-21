<script>
import gsap from "gsap";
export default {
    data() {
        return {
            advantages: [
                {
                    icon: "🔥",
                    text: "Березовые дрова",
                },
                {
                    icon: "🏊",
                    text: "Бассейн из парилки на улицу",
                },
                {
                    icon: "🌿",
                    text: "Зона отдыха на свежем воздухе",
                },
                {
                    icon: "🍖",
                    text: "Мангал и всё для гриля",
                },
                {
                    icon: "🕓",
                    text: "Работаем круглосуточно",
                },
            ],
            buttons: [
                {
                    icon: "🔑",
                    text: "Забронировать",
                    link: "https://wa.me/79528018800?text=Хочу забронировать баню на дату [Введите дату]",
                },
                {
                    icon: "📞",
                    text: "Позвонить",
                    link: "tel:+79528018800",
                },
            ],
        };
    },
    mounted() {
        gsap.from(this.$refs.title, {
            opacity: 0,
            y: -20,
            duration: 1,
            ease: "power2.inOut",
        });
        gsap.from(this.$refs.description, {
            opacity: 0,
            y: -20,
            duration: 1.5,
            ease: "power2.inOut",
        });
        gsap.from(this.$refs.advantageItems, {
            opacity: 0,
            x: -20,
            duration: 0.8,
            stagger: 0.5,
            ease: "power2.inOut",
        });
        gsap.from(this.$refs.heroButtons, {
            opacity: 0,
            x: -20,
            scale: 0.9,
            duration: 1,
            stagger: 0.2,
            ease: "power2.inOut",
        });
    },
    methods: {
        hoverButton(button) {
            gsap.to(button, {
                scale: 1.05,
                duration: 0.3,
                ease: "power2.inOut",
            });
        },
        hoverButtonLeave(button) {
            gsap.to(button, {
                scale: 1,
                duration: 0.3,
                ease: "power2.inOut",
            });
        },
    },
};
</script>

<template>
    <Navbar />
    <div
        class="bg-image bg-cover bg-center bg-no-repeat relative"
        style="background-image: url('/images/hero.png')"
    >
        <div
            class="absolute inset-0 bg-gradient-to-br from-black/70 to-red-500/50"
        ></div>
        <div class="mx-auto max-w-7xl px-4 md:px-6 relative z-10">
            <div
                class="flex items-center justify-start py-12 md:py-24 min-h-[calc(100vh-100px)]"
            >
                <div class="block max-w-xl">
                    <h1
                        class="text-white text-2xl md:text-5xl font-bold mb-3"
                        ref="title"
                    >
                        Добро пожаловать в баню, где отдых — это искусство
                    </h1>
                    <p class="text-white text-sm md:text-md" ref="description">
                        Здесь время останавливается, а заботы растворяются в
                        паре.
                    </p>
                    <ul class="block space-y-1 mt-3">
                        <li
                            ref="advantageItems"
                            class="text-white text-sm md:text-md flex items-center gap-2"
                            v-for="advantage in advantages"
                            :key="advantage.text"
                        >
                            <span class="text-white text-sm md:text-md">{{
                                advantage.icon
                            }}</span>
                            <span class="text-white text-sm md:text-md">{{
                                advantage.text
                            }}</span>
                        </li>
                    </ul>
                    <div
                        class="flex items-start gap-3 md:flex-row md:items-center flex-col mt-5"
                    >
                        <a
                            v-for="button in buttons"
                            :key="button.text"
                            ref="heroButtons"
                            :href="button.link"
                            target="_blank"
                            @mouseenter="hoverButton($event.target)"
                            @mouseleave="hoverButtonLeave($event.target)"
                            class="text-white w-full md:w-auto flex items-center justify-center text-md flex items-center gap-2 bg-gradient-to-br from-red-500 to-orange-500 rounded-full py-2 px-4"
                        >
                            {{ button.icon }}
                            {{ button.text }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Footer />
</template>

<style scoped></style>
