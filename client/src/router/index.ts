import Vue from "vue";
import VueRouter from "vue-router";
import store from "@/store";
import User from "@/models/User";
import { Home, Login, NotFound } from "@/views";

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "home",
    component: Home
  },
  {
    path: "/login",
    name: "login",
    component: Login
  },
  {
    path: "/about",
    name: "about",
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () =>
      import(/* webpackChunkName: "about" */ "../views/About.vue")
  },
  {
    // route all other request to a 404 page
    path: "*",
    name: "notFound",
    component: NotFound
  }
];

const router = new VueRouter({
  mode: "history",
  base: process.env.BASE_URL,
  routes
});

router.beforeEach((to, from, next) => {
  const publicPages = ["/login"];
  const isPublic = publicPages.includes(to.path);
  let loggedIn;
  if (localStorage.getItem("jwt")) {
    const expires = Number(JSON.parse(<string>localStorage.getItem("expire")));
    const now = Date.now() / 1000;
    loggedIn = expires >= now;
    // If loggedIn, but no user is in the store refresh it from localStorage
    if (loggedIn && !store.state.user.fullName) {
      store.commit(
        "setUser",
        new User(JSON.parse(<string>localStorage.getItem("user")))
      );
    }
  } else {
    loggedIn = false;
  }

  if (!isPublic && !loggedIn) {
    // to redirect after login
    next({
      path: "/login",
      query: {
        redirect: to.path
      }
    });
  }
  next();
});

export default router;
