import Vue from "vue";
import Vuex from "vuex";
import WorkingTime from "@/store/WorkingTime.module";
import User from "@/store/User.module";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {},
  mutations: {},
  actions: {},
  modules: {
    workingTime: WorkingTime,
    user: User
  }
});
