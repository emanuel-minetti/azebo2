import { Module } from "vuex";
import LoginService from "@/services/LoginService";
import User from "@/models/User";

const UserModule: Module<any, any> = {
  state: {
    user: new User()
  },
  mutations: {
    setUser(state, user: User) {
      state.user = user;
    }
  },
  actions: {
    login({ commit }, credentials) {
      LoginService.login(credentials.username, credentials.password)
        .then(data => commit("setUser", new User(data.user)))
        .catch(() => commit("setUser", new User()));
    }
  }
};

export default UserModule;
