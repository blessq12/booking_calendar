import "@mdi/font/css/materialdesignicons.min.css";
import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
import "../css/app.css";
import "./bootstrap";
const toast = useToast({ timeout: 3000, position: "top-right" });

const app = createApp({});
const pinia = createPinia();

app.directive("maska", vMaska);
app.use(pinia);

app.config.globalProperties.$toast = toast;

Object.entries(import.meta.glob("./**/*.vue", { eager: true })).forEach(
    ([path, definition]) => {
        app.component(
            path
                .split("/")
                .pop()
                .replace(/\.\w+$/, ""),
            definition.default
        );
    }
);

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount("#app");
