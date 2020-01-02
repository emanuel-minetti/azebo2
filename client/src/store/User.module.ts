import { Module } from "vuex";
import LoginService from "@/services/LoginService";

const User: Module<any, any> = {
  state: {
    fullName: ""
  },
  mutations: {
    setFullName(state, fullName) {
      state.fullName = fullName;
    }
  },
  actions: {
    login(context, credentials) {
      LoginService.login(credentials.username, credentials.password)
        .then(user => context.commit("setFullName", user.full_name))
        .catch(() => context.commit("setFullNamee", ""));
    }
  }
};

export default User;
