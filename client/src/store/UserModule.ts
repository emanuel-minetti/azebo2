import { Module } from "vuex";
import LoginService from "@/services/LoginService";
import User from "@/models/User";

const UserModule: Module<any, any> = {
  state: {
    user: User
  },
  mutations: {
    setFullName(state, fullName) {
      state.user.fullName = fullName;
    }
  },
  actions: {
    login(context, credentials) {
      LoginService.login(credentials.username, credentials.password)
        .then(data => context.commit("setFullName", data.full_name))
        .catch(() => context.commit("setFullNamee", ""));
    }
  }
};

export default UserModule;
