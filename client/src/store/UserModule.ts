import { Module } from "vuex";
import { User } from "@/models";
import { LoginService } from "@/services";

const UserModule: Module<any, any> = {
  state: {
    user: new User(),
  },
  mutations: {
    // is called by login action and by the router if the user is logged in but reloaded the client
    setUser(state, user: User) {
      state.user = user;
    },
  },
  actions: {
    login({ state }, credentials) {
      return LoginService.login(
        credentials.username,
        credentials.password
      ).then((data) => (state.user = new User(data.user)));
    },
  },
};

export default UserModule;
