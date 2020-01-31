import Vue from "vue";
import Vuex from "vuex";
import WorkingTimeModule from "./WorkingTimeModule";
import UserModule from "./UserModule";

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: {},
  mutations: {},
  actions: {},
  modules: {
    workingTime: WorkingTimeModule,
    user: UserModule
  }
});
