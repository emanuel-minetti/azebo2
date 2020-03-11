<template>
  <b-form-input type="text" :value="propValue" />
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";

@Component({
  props: {
    propValue: String
  }
})
export default class SaldoInput extends Vue {}
</script>

<style scoped></style>
