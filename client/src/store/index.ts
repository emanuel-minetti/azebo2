import Vue from "vue";
import Vuex from "vuex";
import WorkingTimeModule from "./WorkingTimeModule";
import UserModule from "./UserModule";

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: {
    loading: false
  },
  mutations: {},
  actions: {},
  modules: {
    workingTime: WorkingTimeModule,
    user: UserModule
  }
});
