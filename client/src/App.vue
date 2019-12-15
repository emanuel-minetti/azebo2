<template>
    <div id="app">
        <header>
            <div id="logo">
                <router-link to="/">
                    <img
                            src="./assets/UdK-Logo_lang.jpg"
                            alt="Das Logo der UdK"
                    />
                </router-link>
                <hgroup>
                    <h1>Arbeitszeitbogen für</h1>
                    <h1>{{ name }}</h1>
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
                        <button class="btn btn-primary">
                            <router-link to="/about">Monate</router-link>
                        </button>
                    </li>
                    <li>
                        <button class="btn btn-primary">
                            <router-link to="/about">Logout</router-link>
                        </button>
                    </li>
                </ul>
            </nav>
        </header>
        <router-view/>
        <footer>
            &copy; {{ copyrightyear }} UdK Berlin, Emanuel Minetti
        </footer>
    </div>
</template>

<script lang="ts">
    import {Component, Vue} from "vue-property-decorator";

    @Component
    export default class App extends Vue {
        // TODO get the Name from session
        name = "Ihr Name";
        copyrightyear = "2019"
    }
</script>

<style lang="scss">
    // Import custom SASS variable overrides
    @import 'assets/custom-vars.scss';

    // Import Bootstrap and BootstrapVue source SCSS files
    @import '~bootstrap/scss/bootstrap.scss';
    @import '~bootstrap-vue/src/index.scss';

    #app {
        font-family: "Avenir", Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        //text-align: center;
        color: #2c3e50;
    }

    #logo {
        width: 60%;
        display: inline-block;

        img {
            height: 10em;
        }

        hgroup {
            display: inline-block;
            padding-left: 100px;
            text-align: center;
        }
    }

    #nav {
        width: 40%;
        display: inline-block;

        ul {
            list-style-type: none;

            li {
                padding: 0 20px;
                display: inline;
            }
        }

        a {
            font-weight: bold;
            color: #2c3e50;

            &.router-link-exact-active {
                color: #42b983;
            }
        }
    }

    footer {
        text-align: center;
    }
</style>
