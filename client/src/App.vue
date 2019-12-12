<template>
    <div id="app">
        <header>
            <div id="logo">
                <router-link to="/">
                    <img src="./assets/UdK-Logo_lang.jpg"
                         alt="The UdK logo with a link to the homepage of this application"/>
                </router-link>
                <hgroup>
                    <h1>Arbeitzeitbogen für</h1>
                    <h1>{{ name }}</h1>
                </hgroup>
            </div>
            <nav id="nav">
                <ul>
                    <li>
                      <button class="btn btn-primary">
                        <router-link to="/">Home</router-link>
                      </button>
                    </li>
                    <li>
                        <router-link to="/about">About</router-link>
                    </li>
                </ul>
            </nav>
        </header>
        <router-view/>
    </div>
</template>

<script lang="ts">
    import {Component, Vue} from "vue-property-decorator";

    @Component
    export default class App extends Vue {
        // TODO get the Name from session
        name = "Ihr Name";
    }
</script>

<style lang="scss">
    #app {
        font-family: "Avenir", Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        //text-align: center;
        color: #2c3e50;
    }

    #logo {
        width: 80%;
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
        width: 20%;
        display: inline-block;

        ul {
          list-style-type: none;
        }

        a {
            font-weight: bold;
            color: #2c3e50;

            &.router-link-exact-active {
                color: #42b983;
            }
        }
    }
</style>
