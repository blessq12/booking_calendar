import "@mdi/font/css/materialdesignicons.min.css";
import { vMaska } from "maska/vue";
import { createApp } from "vue";
import "../css/app.css";
import "./bootstrap";
const app = createApp({});

app.directive("maska", vMaska);

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
