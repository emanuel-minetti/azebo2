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
import { Carry } from "@/models";

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

  get carry(): Carry {
    return this.$store.state.workingTime.carry;
  }
  get items() {
    return [{ key: "Saldo Vormonat", value: this.carry.saldo }];
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
