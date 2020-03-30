<template>
  <b-form>
    <!--TODO Should only be shown to new users (See #30)-->
    <b-form-group label="Saldo Übertrag:" label-for="carry-over-input">
      <SaldoInput
        v-if="showSaldoInput"
        :prop-saldo="getFormSaldo()"
        v-on:update-saldo="setFormSaldo"
        :prop-sign="true"
      />
    </b-form-group>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Carry, Saldo } from "@/models";
import SaldoInput from "@/components/SaldoInput.vue";
import { mapState } from "vuex";

@Component({
  components: {
    SaldoInput,
  },
  computed: { ...mapState("workingTime", ["carry"]) },
})
export default class CarryForm extends Vue {
  carry!: Carry;
  _formSaldo: Saldo | undefined;
  showSaldoInput = true;

  getFormSaldo() {
    if (!this._formSaldo && this.carry.saldo) {
      this._formSaldo = this.carry.saldo.clone();
    }
    return this._formSaldo;
  }
  setFormSaldo(saldo: Saldo | undefined) {
    this._formSaldo = saldo;
    this.showSaldoInput = false;
    this.$nextTick().then(() => (this.showSaldoInput = true));
  }
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
