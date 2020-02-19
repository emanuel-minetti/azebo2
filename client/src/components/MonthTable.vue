<template>
  <div>
    <b-table
      bordered
      striped
      hover
      :items="monthData.days"
      primary-key="date"
      :fields="fields"
      :tbody-tr-class="rowClass"
      thead-class="sticky"
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { FormatterService } from "@/services";
import { WorkingDay, WorkingMonth } from "@/models";

@Component
export default class MonthTable extends Vue {
  // `monthData` is being used in the items property of the table
  get monthData(): WorkingMonth {
    return this.$store.state.workingTime.month;
  }

  // specifies the shown columns of th table
  get fields() {
    return [
      {
        key: "date",
        label: "Datum",
        class: "small-column",
        formatter: this.formatDate
      },
      {
        key: "begin",
        label: "Beginn",
        class: "small-column",
        formatter: FormatterService.toGermanTime
      },
      {
        key: "end",
        label: "Ende",
        class: "small-column",
        formatter: FormatterService.toGermanTime
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
        formatter: this.formatBreak
      },
      {
        key: "totalTime",
        label: "Anwesend",
        class: "small-column"
      },
      {
        key: "actualTime",
        label: "Ist",
        class: "small-column"
      },
      {
        key: "targetTime",
        label: "Soll",
        class: "small-column"
      },
      {
        key: "saldoTime",
        label: "Saldo",
        class: ["small-column", "saldo"]
      }
    ];
  }

  // adds a class for non working days
  rowClass(day: WorkingDay, type: string) {
    if (!day || type !== "row") return;
    if (!day.isWorkingDay) return "not-a-working-day";
  }

  // formats the shown date
  formatDate(date: Date, key: string, day: WorkingDay) {
    const dateString = FormatterService.toLongGermanDate(date);
    return day.isHoliday ? dateString + " " + day.holidayName : dateString;
  }

  // formats the break column
  formatBreak(hasBreak: boolean, key: string, day: WorkingDay): string {
    if (day && !day.hasWorkingTime) return "";
    return hasBreak ? "Ja" : "Nein";
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
div {
  text-align: center;
  width: 90%;
  margin-left: 5%;
}

/deep/ table td {
  vertical-align: middle;
}

/deep/ .sticky {
  position: sticky;
  top: 0;
  background-color: white;
}

/deep/ .small-column {
  width: 90px;
}

/deep/ .not-a-working-day {
  color: #e70036;
}

/deep/ .saldo {
  border-left: 2px solid #211e1e;
}
</style>
