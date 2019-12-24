import Vue from "vue";
import VueRouter from "vue-router";
import Home from "../views/Home.vue";
import NotFound from "../views/NotFound.vue";
import Login from "@/views/Login.vue";

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
  const loggedIn = localStorage.getItem("user"); // evaluate `expires`

  if (!isPublic && !loggedIn) {
    return next({
      path: "/login",
      query: {
        redirect: to.path
      } // to redirect after login
    });
  }
  next();
});

export default router;
