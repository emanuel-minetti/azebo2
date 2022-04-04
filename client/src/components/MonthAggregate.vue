<template>
  <div id="month-aggregate" class="mx-0 mx-lg-auto">
    <b-table-lite
      :caption="capture"
      caption-top
      :fields="fields"
      :items="items"
      bordered
    />
    <b-table-lite
      class="mx-4"
      caption="Kalenderwochen:"
      caption-top
      :fields="weekFields"
      bordered
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Carry, WorkingMonth } from "@/models";
import { timesConfig } from "@/configs";
import { mapState } from "vuex";

@Component({
  computed: { ...mapState("workingTime", ["carryResult", "month"]) },
})
export default class MonthAggregate extends Vue {
  carryResult!: Carry;
  month!: WorkingMonth;
  fields = [
    {
      key: "key",
      label: "",
      class: "first_column",
    },
    {
      key: "carryResult",
      label: "Übertrag",
    },
    {
      key: "month",
      label: "Laufender Monat",
    },
    {
      key: "total",
      label: "Gesamt",
    },
  ];

  weekFields = [
    {
      key: "soso",
      label: "test1",
    },
    {
      key: "soso2",
      label: "test2",
    },
  ];

  get holidaysLeftString() {
    return this.month.monthNumber <= timesConfig.previousHolidaysValidTo
      ? this.carryResult.holidays +
          " (Vorjahr: " +
          this.carryResult.holidaysPrevious +
          ")"
      : this.carryResult.holidays;
  }

  get holidaysTotalString() {
    let holidays = this.carryResult.holidays;
    let taken = this.month.takenHolidays;
    if (this.month.monthNumber <= timesConfig.previousHolidaysValidTo) {
      let holidaysPrevious = this.carryResult.holidaysPrevious;
      if (holidaysPrevious >= taken) {
        holidaysPrevious -= taken;
      } else {
        taken -= holidaysPrevious;
        holidays -= taken;
      }
      return holidays + " (Vorjahr: " + holidaysPrevious + ")";
    }
    return holidays - taken;
  }

  get capture() {
    let capture = "Zusammenfassung: ";
    if (this.carryResult.hasMissing) {
      let missing = this.carryResult.missing;
      capture += "(Eine Darstellung des Übertrags ist nicht möglich, denn ";
      if (missing.length === 1) {
        capture += "der Monat " + missing[0] + " ist ";
      } else {
        capture += "die Monate ";
        missing.forEach((missed, index) => {
          if (index + 1 === missing.length) {
            capture += " und ";
          }
          capture += missed;
          if (index + 3 <= missing.length) {
            capture += ", ";
          }
        });
        capture += " sind ";
      }
      capture += "noch nicht abgeschlossen.)";
    }
    return capture;
  }

  get items() {
    let carryResult = {
      key: "Saldo",
      carryResult: this.carryResult.saldo
        ? this.carryResult.saldo.toString()
        : "",
      month: this.$store.getters["workingTime/saldo"],
      total: this.$store.getters["workingTime/saldoTotal"],
    };
    let holidayResult = {
      key: "Urlaub",
      carryResult: this.holidaysLeftString,
      month: this.month.takenHolidays,
      total: this.holidaysTotalString,
    };
    if (this.carryResult.hasMissing) {
      carryResult.carryResult = "Unbekannt";
      carryResult.total = carryResult.month;
      holidayResult.carryResult = "Unbekannt";
      holidayResult.total = holidayResult.month;
    }
    return [carryResult, holidayResult];
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
#month-aggregate {
  text-align: center;
  width: 90%;
  overflow: hidden;
}

table {
  width: 500px;
  font-size: larger;
  float: left;
}

/deep/ table caption {
  font-weight: bold;
  color: inherit;
}

/deep/ .first_column {
  font-weight: bold;
}

/deep/ table td {
  text-align: center;
}

/deep/ table th {
  text-align: center;
}
</style>
