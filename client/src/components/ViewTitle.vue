<template>
  <h1>
    {{ propTitle }}
  </h1>
</template>

<script lang="ts">
import { defineComponent } from "vue";
export default defineComponent({
  props: {
    propTitle: {
      type: String,
      required: true
    },
  }
});
</script>

<style scoped>
h1 {
  text-align: center;
}
</style>
