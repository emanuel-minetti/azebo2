<template>
  <div>
    <Title v-bind:prop-title="title" />
    <div id="intro">
      Hier können Sie Ihre Überträge aus externen Zeiterfassungssystemen
      einstellen:
    </div>
    <CarryForm />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { CarryForm, Title } from "@/components";

@Component({
  components: {
    Title,
    CarryForm,
  },
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
