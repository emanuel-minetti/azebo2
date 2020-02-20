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
import { Carry } from "@/models";
import { timesConfig } from "@/configs";

@Component
export default class MonthAggregate extends Vue {
  fields = [
    {
      key: "key",
      label: "",
      class: "first_column"
    },
    {
      key: "carry",
      label: "Übertrag"
    },
    {
      key: "month",
      label: "Bisher"
    },
    {
      key: "total",
      label: "Gesamt"
    }
  ];

  get carry(): Carry {
    return this.$store.state.workingTime.carry;
  }

  get month() {
    return this.$store.state.workingTime.month;
  }

  get holidaysLeftString() {
    return this.month.monthNumber <= timesConfig.previousHolidaysValidTo
      ? this.carry.holidays + " (Vorjahr: " + this.carry.holidaysPrevious + ")"
      : this.carry.holidays;
  }

  get holidaysTotalString() {
    let holidays = this.carry.holidays;
    let taken = this.month.takenHolidays;
    if (this.month.monthNumber <= timesConfig.previousHolidaysValidTo) {
      let holidaysPrevious = this.carry.holidaysPrevious;
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
    // TODO discriminate closed and unclosed month (if it's finalized wrong carry is shown!)
    return [
      {
        key: "Saldo",
        carry: this.carry.saldo,
        month: this.$store.getters.saldo,
        total: this.$store.getters.saldoTotal
      },
      {
        key: "Urlaub",
        carry: this.holidaysLeftString,
        month: this.month.takenHolidays,
        total: this.holidaysTotalString
      }
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
