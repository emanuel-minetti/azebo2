<template>
  <div>
    <Title v-bind:prop-title="title" />
    <div id="intro">
      Hier können Sie einen Übertrag aus einem anderen Zeiterfassungssystem
      einstellen
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
export default class CarryOver extends Vue {
  title = "Überträge";
}
</script>

<style scoped>
#intro {
  font-size: 1.5rem;
  text-align: center;
}
</style>
