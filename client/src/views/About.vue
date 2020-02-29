<template>
  <div>
    <Title v-bind:prop-title="test" />
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
export default class About extends Vue {
  test = "hallo";
}
</script>
