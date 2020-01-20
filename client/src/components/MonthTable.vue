<template>
  <div>
    <b-table
      bordered
      striped
      hover
      :items="monthData.days"
      :fields="fields"
      :tbody-tr-class="rowClass"
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { GermanDateFormatter } from "@/services";
import WorkingDay from "@/models/WorkingDay";

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

  rowClass(day: WorkingDay, type: string) {
    if (!day || type !== "row") return;
    if (day.isWorkingDay) return "not-a-working-day";
  }
}
</script>

<style scoped>
div {
  text-align: center;
  width: 90%;
  margin-left: 5%;
}

/*noinspection CssUnusedSymbol*/
>>> .not-a-working-day {
  color: #e70036;
}
</style>
