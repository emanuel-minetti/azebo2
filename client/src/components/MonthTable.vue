<template>
  <div>
    <b-table :items="monthData.days">

    </b-table>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from "vue-property-decorator";

@Component
export default class MonthTable extends Vue {

  get monthData() {
    return this.$store.state.workingTime.month;
  }
}
</script>

<style scoped>
  div {
    text-align: center;
    width: 90%;
  }
</style>
