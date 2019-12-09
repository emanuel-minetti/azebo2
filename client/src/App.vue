<template>
  <div id="app">
    <header>
      <div id="logo">
        <router-link to="/">
          <img src="./assets/UdK-Logo_lang.jpg"
               alt="The UdK logo with a link to the homepage of this application"/>
        </router-link>
        <h1>Arbeitzeitbogen</h1>
      </div>
      <nav id="nav">
        <router-link to="/">Home</router-link>
        |
        <router-link to="/about">About</router-link>
      </nav>
    </header>
    <router-view/>
  </div>
</template>

<style lang="scss">
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  //text-align: center;
  color: #2c3e50;
}

#logo img {
  height: 10em;
}

#nav {
  padding: 30px;

  a {
    font-weight: bold;
    color: #2c3e50;

    &.router-link-exact-active {
      color: #42b983;
    }
  }
}
</style>
