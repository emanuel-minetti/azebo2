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
import { Carry, Saldo, WorkingMonth } from "@/models";
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

  get monthSaldoString() {
    if (
      this.$store.getters["workingTime/saldoMobile"] === "" ||
      this.$store.getters["workingTime/saldo"].getMillis() === 0 ||
      this.$store.getters["workingTime/saldoMobile"].getMillis() === 0
    ) {
      return this.$store.getters["workingTime/saldo"];
    } else {
      return (
        this.$store.getters["workingTime/saldo"] +
        " (davon " +
        this.$store.getters["workingTime/saldoMobile"] +
        " = " +
        Saldo.getPercentage(
          this.$store.getters["workingTime/saldo"],
          this.$store.getters["workingTime/saldoMobile"]
        ).toLocaleString("de-DE", {
          maximumFractionDigits: 1,
          minimumFractionDigits: 1,
        }) +
        "% Mobil)"
      );
    }
  }

  get items() {
    return [
      {
        key: "Saldo",
        carryResult: this.carryResult.saldo,
        month: this.monthSaldoString,
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
