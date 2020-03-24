<template>
  <b-form>
    <!--TODO Should only be shown to new users (See #30)-->
    <b-form-group label="Saldo Übertrag:" label-for="carry-over-input">
      <SaldoInput :prop-saldo.sync="carryOver" :prop-sign="true" />
    </b-form-group>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Saldo } from "@/models";
import SaldoInput from "@/components/SaldoInput.vue";

@Component({
  components: {
    SaldoInput,
  },
})
export default class CarryForm extends Vue {
  // TODO Get Saldo from server!
  carryOver = Saldo.createFromMillis(1000000);
}
</script>

<style scoped>
form {
  width: 30%;
  margin-left: 35%;
  margin-right: 35%;
}

>>> label,
>>> legend {
  font-size: large;
  font-weight: bold;
}
</style>
