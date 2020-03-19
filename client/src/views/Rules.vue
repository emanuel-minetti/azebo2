<template>
  <div>
    <Title v-bind:prop-title="title" />
    <header>
      Hier können Sie Ihre Stammdaten und Ihre Arbeitszeitseinstellungen
      bearbeiten
    </header>
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
header {
  font-size: 1.5rem;
  text-align: center;
}
</style>
