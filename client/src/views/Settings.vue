<template>
  <div>
    <Title v-bind:prop-title="title" />
  </div>
</template>
<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Title } from "@/components";

@Component({
  components: {
    Title
  }
})
export default class Settings extends Vue {
  title = "Einstellungen";
}
</script>
