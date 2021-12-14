import Vue from "vue";

import {
  BootstrapVue,
  BIcon,
  BIconCircleFill,
  BIconCircle,
} from "bootstrap-vue";

import "./custom-vars.scss";

Vue.use(BootstrapVue);
Vue.component("BIcon", BIcon);
Vue.component("BIconCircleFill", BIconCircleFill);
Vue.component("BIconCircle", BIconCircle);
