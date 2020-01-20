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
import WorkingMonth from "@/models/WorkingMonth";

@Component
export default class MonthTable extends Vue {
  get monthData(): WorkingMonth {
    return this.$store.state.workingTime.month;
  }

  get fields() {
    return [
      {
        key: "date",
        label: "Datum",
        class: "small-column",
        formatter: GermanDateFormatter.toLongGermanDate
      },
      {
        key: "begin",
        label: "Beginn",
        class: "small-column",
        formatter: GermanDateFormatter.toGermanTime
      },
      {
        key: "end",
        label: "Ende",
        class: "small-column",
        formatter: GermanDateFormatter.toGermanTime
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
        label: "Pause",
        class: "small-column",
        formatter: (bool: boolean) => (bool ? "Ja" : "Nein")
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
/deep/ .small-column {
  width: 90px;
  vertical-align: middle;
}

/*noinspection CssUnusedSymbol*/
/deep/ .not-a-working-day {
  color: #e70036;
}
</style>
