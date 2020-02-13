<template>
  <div>
    <b-table-lite
      caption="Zusammenfassung:"
      caption-top
      :fields="fields"
      :items="items"
      thead-class="hidden_header"
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
      class: "key_column"
    },
    {
      key: "value",
      label: "",
      class: "value_column"
    }
  ];

  get carry() {
    return this.$store.state.workingTime.carry;
  }
  get items() {
    // TODO discriminate closed and unclosed month
    return [
      { key: "Saldo Vormonat", value: this.carry.saldo },
      { key: "Saldo Bisher", value: this.$store.getters.saldo },
      { key: "Saldo Gesamt", value: this.$store.getters.saldoTotal }
    ];
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.hidden_header {
  display: none;
}
.key_column {
  font-weight: bold;
  font-size: 130%;
  width: 15rem;
}
.value_column {
  font-size: 130%;
}
</style>
