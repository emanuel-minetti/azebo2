import { Module } from "vuex";
import { User } from "@/models";
import { LoginService } from "@/services";

const UserModule: Module<any, any> = {
  state: {
    user: new User()
  },
  mutations: {
    // is called by login action and by the router if the user is logged in but reloaded the client
    setUser(state, user: User) {
      state.user = user;
    }
  },
  actions: {
    login({ commit }, credentials) {
      return LoginService.login(credentials.username, credentials.password)
        .then(data => {
          commit("setUser", new User(data.user));
          //TODO remove debugging
          console.log("Nanu");
          //return data;
        })
        //.catch(() => commit("setUser", new User()));
    }
  }
};

export default UserModule;
