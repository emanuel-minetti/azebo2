import { Module } from "vuex";
import LoginService from "@/services/LoginService";
import User from "@/models/User";

const UserModule: Module<any, any> = {
  state: {
    user: new User()
  },
  mutations: {
    setFullName(state, user) {
      state.user.fullName = user.given_name + " " + user.name;
    }
  },
  actions: {
    login({ commit }, credentials) {
      LoginService.login(credentials.username, credentials.password)
        .then(data => commit("setFullName", data.user))
        .catch(() => commit("setFullName", new User()));
    }
  }
};

export default UserModule;
