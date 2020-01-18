<template>
  <div>
    <b-table :items="monthData.days" :fields="fields"> </b-table>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from "vue-property-decorator";
import { GermanDateFormatter } from "@/services";

@Component
export default class MonthTable extends Vue {
  get monthData() {
    return this.$store.state.workingTime.month;
  }

  get fields() {
    return [
      {
        key: "date",
        label: "Datum",
        formatter: GermanDateFormatter.toGermanDate
      },
      {
        key: "begin",
        label: "Beginn"
      },
      {
        key: "end",
        label: "Ende"
      },
      {
        key: "timeOff",
        label: "Dienstbefreiung"
      },
      {
        key: "comment",
        label: "Kommentar"
      },
      {
        key: "break",
        label: "Pause"
      }
    ];
  }
}
</script>

<style scoped>
div {
  text-align: center;
  width: 90%;
}
</style>
