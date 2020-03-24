import Vue from "vue";
import VueRouter from "vue-router";
import { store } from "@/store";
import User from "@/models/User";
import { Home, Login, NotFound } from "@/views";

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "home",
    component: Home,
  },
  {
    path: "/month/:month(\\d+)/:year(\\d+)?",
    name: "month",
    component: Home,
  },
  {
    path: "/login",
    name: "login",
    component: Login,
  },
  {
    path: "/settings/carry-over",
    name: "carryOver",
    component: () =>
      import(/* webpackChunkName: "settings"  */ "../views/CarryOver.vue"),
  },
  {
    path: "/settings/rules",
    name: "rules",
    component: () =>
      import(/* webpackChunkName: "settings"  */ "../views/Rules.vue"),
  },
  {
    path: "/settings/letterhead",
    name: "letterhead",
    component: () =>
      import(/* webpackChunkName: "settings"  */ "../views/Letterhead.vue"),
  },
  {
    // route all other requests to a 404 page
    path: "*",
    name: "notFound",
    component: NotFound,
  },
];

const router = new VueRouter({
  mode: "history",
  base: process.env.BASE_URL,
  routes,
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
    // @ts-ignore
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
        redirect: to.path,
      },
    });
  }
  next();
});

export default router;
