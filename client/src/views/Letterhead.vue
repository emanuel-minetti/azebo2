<template>
  <div>
    <Title :prop-title="title" />
    <div id="intro">
      Hier können Sie die Daten Ihres Kopfbogens kontrollieren und ausdrucken
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Title } from "/src/components";

@Component({
  components: {
    Title,
  },
})
export default class Letterhead extends Vue {
  title = "Kopfbogen";
}
</script>

<style scoped>
#intro {
  font-size: 1.5rem;
  text-align: center;
}
</style>
