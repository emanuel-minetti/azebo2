<template>
  <div>
    <b-table-lite
      caption="Zusammenfassung:"
      caption-top
      :fields="fields"
      :items="items"
      borderless
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Carry } from "@/models";

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
    return this.$store.state.workingTime.month.monthNumber;
  }

  get holidayString() {
    return this.month <= Carry.PREVIOUS_HOLIDAYS_VALID_TO_MONTH
      ? this.carry.holidays + " (Vorjahr: " + this.carry.holidaysPrevious + ")"
      : this.carry.holidays;
  }

  get items() {
    // TODO discriminate closed and unclosed month
    return [
      {
        key: "Saldo",
        carry: this.carry.saldo,
        month: this.$store.getters.saldo,
        total: this.$store.getters.saldoTotal
      },
      {
        key: "Urlaub",
        carry: this.holidayString
      }
    ];
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
table {
  font-size: larger;
}

/deep/ .first_column {
  font-weight: bold;
}
</style>
