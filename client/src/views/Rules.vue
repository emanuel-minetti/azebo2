<template>
  <div>
    <Title v-bind:prop-title="title" />
    <div id="intro">
      Hier können Sie Ihre wöchentliche Arbeitszeit einstellen und (optional)
      Sollarbeitszeiten für einzelne Wochentage festlegen
    </div>
    <SettingsForm />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { SettingsForm, Title } from "@/components";

@Component({
  components: {
    Title,
    SettingsForm
  }
})
export default class Rules extends Vue {
  title = "Arbeitszeit";
}
</script>

<style scoped>
#intro {
  font-size: 1.5rem;
  text-align: center;
}
</style>
