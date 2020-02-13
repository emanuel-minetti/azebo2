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

  get carry() {
    return this.$store.state.workingTime.carry;
  }
  get items() {
    // TODO discriminate closed and unclosed month
    return [
      {
        key: "Saldo",
        carry: this.carry.saldo,
        month: this.$store.getters.saldo,
        total: this.$store.getters.saldoTotal
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
