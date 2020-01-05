<template>
  <div class="home">
    <Title prop-title="Startseite" />
    Das ist die Homepage
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import Title from "@/components/Title.vue";

@Component({
  components: { Title }
})
export default class Home extends Vue {
  mounted() {
    //TODO wait till JWT is present!
    this.$store.dispatch("getMonth", new Date()).then(() => {});
  }
}
</script>
