<template>
  <div id="app">
    <header>
      <div id="logo">
        <router-link to="/">
          <img
            src="./assets/Universitaet_der_Kuenste_Berlin_4c_transparenz.png"
            alt="Das Logo der UdK"
          />
        </router-link>
      </div>
      <div id="site-title">
        <hgroup>
          <h1>Arbeitszeitbogen</h1>
          <h1 v-if='isDev' class='text-danger'>Testserver</h1>
          <h1 v-if="loggedIn">für {{ name }}</h1>
        </hgroup>
      </div>
      <nav id="nav">
        <ul>
          <li>
            <b-dropdown text="Einstellungen" variant="primary">
              <b-dropdown-item :to="{ name: 'carryOver' }">
                Überträge
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'rules' }">
                Arbeitszeit
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'letterhead' }">
                Kopfbogen
              </b-dropdown-item>
            </b-dropdown>
          </li>
          <li>
            <b-dropdown text="Monate" variant="primary" @hide="cancelHide">
              <b-dropdown-item :to="{ name: 'month', params: { month: 1 } }">
                Januar
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 2 } }">
                Februar
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 3 } }">
                März
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 4 } }">
                April
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 5 } }">
                Mai
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 6 } }">
                Juni
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 7 } }">
                Juli
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 8 } }">
                August
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 9 } }">
                September
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 10 } }">
                Oktober
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 11 } }">
                November
              </b-dropdown-item>
              <b-dropdown-item :to="{ name: 'month', params: { month: 12 } }">
                Dezember
              </b-dropdown-item>
              <b-dropdown-divider />
              <b-dropdown-item-button @click="onShowLastYear">{{
                lastYearShowString
              }}</b-dropdown-item-button>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 1, year: lastYearString },
                }"
              >
                Januar {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 2, year: lastYearString },
                }"
              >
                Februar {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 3, year: lastYearString },
                }"
              >
                März {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 4, year: lastYearString },
                }"
              >
                April {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 5, year: lastYearString },
                }"
              >
                Mai {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 6, year: lastYearString },
                }"
              >
                Juni {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 7, year: lastYearString },
                }"
              >
                Juli {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 8, year: lastYearString },
                }"
              >
                August {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 9, year: lastYearString },
                }"
              >
                September {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 10, year: lastYearString },
                }"
              >
                Oktober {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 11, year: lastYearString },
                }"
              >
                November {{ lastYearString }}
              </b-dropdown-item>
              <b-dropdown-item
                v-if="lastYearShown"
                :to="{
                  name: 'month',
                  params: { month: 12, year: lastYearString },
                }"
              >
                Dezember {{ lastYearString }}
              </b-dropdown-item>
            </b-dropdown>
          </li>
          <li>
            <b-button variant="primary" @click="onLogout">
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
import { LoginService } from "/src/services";
import { BvEvent } from "bootstrap-vue";

@Component
export default class App extends Vue {
  copyrightyear = "";
  version = "";
  lastYearShowString = "Vorjahr einblenden";
  lastYearString = new Date().getFullYear() - 1;
  lastYearShown = false;
  noHide = false;

  get name() {
    return this.$store.state.user.user.fullName;
  }

  get isDev() {
    return import.meta.env.DEV;
  }

  get loggedIn() {
    return this.$store.state.user.user.fullName !== "";
  }

  //noinspection JSUnusedGlobalSymbols
  mounted() {
    // copyright and version from `package.json`
    // TODO adapt
    // this.version = config.version;
    // this.copyrightyear = config.copyright;
    this.version = "2.1.8";
    this.copyrightyear = "2019-2023";
  }

  onLogout() {
    LoginService.logout();
    this.$store.state.user.user.fullName = '';
    this.$router.push({ name: "login" });
  }

  onShowLastYear() {
    this.lastYearShown = !this.lastYearShown;
    this.lastYearShowString = this.lastYearShown
      ? "Vorjahr ausblenden"
      : "Vorjahr einblenden";
    this.noHide = true;
  }

  cancelHide(evt: BvEvent) {
    if (this.noHide) {
      evt.preventDefault();
      this.noHide = false;
    }
  }
}
</script>

<!--suppress CssInvalidFunction -->
<style lang="scss">
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

#logo {
  width: 33%;
  display: inline-block;
  text-align: center;

  img {
    height: 3rem;
    margin-top: 32px;
  }
}

#site-title {
  width: 34%;
  display: inline-block;
  text-align: center;

  hgroup {
    padding-left: 10%;
    padding-right: 10%;
    display: inline-block;
    text-align: center;
  }
}

#nav {
  width: 33%;
  display: inline-block;

  ul {
    list-style-type: none;

    li {
      padding: 20px;
      display: inline;

      button {
        margin-top: 10px;
      }
    }
  }

  button,
  a {
    font-weight: bold;
    color: white;
    background-color: #e70036;
  }

  a:hover {
    text-decoration-line: none;
  }

  button:hover {
    background-color: theme-color("text");
    text-decoration-line: none;
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

.form-control:focus,
.custom-select:focus,
.custom-control-input:focus ~ .custom-control-label::before {
  border-color: theme-color("text");
  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075),
    0 0 8px theme-color-level("text", -0.4);
}

footer {
  text-align: center;
}
</style>
