<template>
  <div id="app">
    <header>
      <div id="logo">
        <router-link to="/">
          <img src="./assets/UdK-Logo_lang.jpg" alt="Das Logo der UdK" />
        </router-link>
      </div>
      <div id="site-title">
        <hgroup>
          <h1>Arbeitszeitbogen</h1>
          <h1 v-if="loggedIn">für {{ name }}</h1>
        </hgroup>
      </div>
      <nav id="nav">
        <ul>
          <li>
            <button class="btn btn-primary">
              <router-link to="/">Einstellungen</router-link>
            </button>
          </li>
          <li>
            <b-dropdown text="Monate" variant="primary">
              <b-dropdown-item>Januar</b-dropdown-item>
              <b-dropdown-item>Februar</b-dropdown-item>
              <b-dropdown-item>März</b-dropdown-item>
              <b-dropdown-item>April</b-dropdown-item>
              <b-dropdown-item>Mai</b-dropdown-item>
              <b-dropdown-item>Juni</b-dropdown-item>
              <b-dropdown-item>Juli</b-dropdown-item>
              <b-dropdown-item>August</b-dropdown-item>
              <b-dropdown-item>September</b-dropdown-item>
              <b-dropdown-item>Oktober</b-dropdown-item>
              <b-dropdown-item>November</b-dropdown-item>
              <b-dropdown-item>Dezember</b-dropdown-item>
            </b-dropdown>
          </li>
          <li>
            <b-button variant="primary" v-on:click="onLogout">
              Logout
            </b-button>
          </li>
        </ul>
      </nav>
    </header>
    <router-view />
    <footer>
      &copy; {{ copyrightyear }} Emanuel Minetti, UdK Berlin Version:
      {{ version }}
    </footer>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { LoginService } from "@/services";

const config = require("../package.json");

@Component
export default class App extends Vue {
  copyrightyear = "";
  version = "";

  get name() {
    return this.$store.state.user.user.fullName;
  }

  get loggedIn() {
    return this.$store.state.user.user.fullName !== "";
  }

  //noinspection JSUnusedGlobalSymbols
  mounted() {
    // copyright and version from `package.json`
    this.version = config.version;
    this.copyrightyear = config.copyright;
  }

  onLogout() {
    LoginService.logout();
    this.$router.push({ name: "login" });
  }
}
</script>

<style lang="scss">
// Import custom SASS variable overrides
@import "assets/custom-vars.scss";

// Import Bootstrap and BootstrapVue source SCSS files
@import "~bootstrap/scss/bootstrap.scss";
@import "~bootstrap-vue/src/index.scss";

#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  //text-align: center;
  color: #2c3e50;
}

#logo {
  width: 33%;
  display: inline-block;

  img {
    height: 10em;
  }
}

#site-title {
  width: 33%;
  display: inline-block;
  hgroup {
    padding-left: 10%;
    padding-right: 10%;
    display: inline-block;
    text-align: center;
  }
}

#nav {
  width: 34%;
  display: inline-block;

  ul {
    list-style-type: none;

    li {
      padding: 0 20px;
      display: inline;
    }
  }

  button,
  a {
    font-weight: bold;
    color: white;
  }

  a:hover {
    text-decoration-line: none;
  }

  button:hover {
    background-color: theme-color("text");
  }

  .dropdown-menu {
    background-color: #e70036;

    li {
      padding: 0;

      :hover {
        background-color: #211e1e;
      }
    }
  }
}

.form-control:focus {
  border-color: theme-color("text");
  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075),
    0 0 8px theme-color-level("text", -0.4);
}

footer {
  text-align: center;
}
</style>
