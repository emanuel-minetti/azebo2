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
    <b-form-group
      label="Resturlaub für dieses Jahr:"
      label-for="holidays-input"
    >
      <b-form-input
        type="number"
        min="0"
        max="99"
        :value="getFormHolidays()"
        v-on:blur="setFormHolidays"
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
  showSaldoInput = true;
  private _formSaldo: Saldo | undefined;
  private _formHolidays: number | undefined;

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
  getFormHolidays() {
    if (!this._formHolidays && this.carry.holidays) {
      this._formHolidays = this.carry.holidays;
    }
    return this._formHolidays;
  }
  setFormHolidays(evt: Event) {
    let target = evt.target as HTMLInputElement;
    this._formHolidays = Number(target.value);
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
