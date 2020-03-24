<template>
  <div id="month-aggregate">
    <b-table-lite
      caption="Zusammenfassung:"
      caption-top
      :fields="fields"
      :items="items"
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
//@Component
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
      label: "Bisher",
    },
    {
      key: "total",
      label: "Gesamt",
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

  get items() {
    return [
      {
        key: "Saldo",
        carryResult: this.carryResult.saldo,
        month: this.$store.getters["workingTime/saldo"],
        total: this.$store.getters["workingTime/saldoTotal"],
      },
      {
        key: "Urlaub",
        carryResult: this.holidaysLeftString,
        month: this.month.takenHolidays,
        total: this.holidaysTotalString,
      },
    ];
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
#month-aggregate {
  padding-left: 40px;
}

table {
  width: 500px;
  font-size: larger;
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
