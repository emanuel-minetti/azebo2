<template>
  <b-form v-if="show" @submit="onSubmit" @reset="onReset">
    <!--TODO Should be shown to non new users with a warning (See #30)-->
    <b-form-group label="Saldo Übertrag:" label-for="carry-over-input">
      <SaldoInput
        v-if="showSaldoInput"
        :prop-disabled="propDisabled"
        :prop-saldo="getFormSaldo()"
        :prop-sign="true"
        @update-saldo="setFormSaldo"
      />
    </b-form-group>
    <b-form-group label="Resturlaub für dieses Jahr:">
      <b-form-input
        type="number"
        min="0"
        max="99"
        :disabled="propDisabled"
        :value="getFormHolidays()"
        @blur="setFormHolidays"
      />
    </b-form-group>
    <b-form-group label="Resturlaub des vergangenen Jahres:">
      <b-form-input
        type="number"
        min="0"
        max="99"
        :value="getFormHolidaysPrevious()"
        :disabled="propDisabled"
        @blur="setFormHolidaysPrevious"
      />
    </b-form-group>
    <div v-if="!propDisabled">
      <b-button type="submit" variant="primary">Absenden</b-button>
      <b-button type="reset" variant="secondary" class="ml-2">
        Zurücksetzen
      </b-button>
      <b-button
        type="button"
        variant="secondary"
        class="ml-2"
        @click="onCancel"
      >
        Abbrechen
      </b-button>
    </div>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Carry, Saldo } from "@/models";
import SaldoInput from "@/components/SaldoInput.vue";
import { mapState } from "vuex";

@Component({
  props: {
    propDisabled: Boolean,
  },
  components: {
    SaldoInput,
  },
  computed: { ...mapState("workingTime", ["carry"]) },
})
export default class CarryForm extends Vue {
  carry!: Carry;
  private _formSaldo: Saldo | undefined;
  private _formHolidays: number | undefined;
  private _formHolidaysPrevious: number | undefined;

  //Fields to support updating the view
  private show = true;
  private showSaldoInput = true;

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
  }

  getFormHolidaysPrevious() {
    if (!this._formHolidaysPrevious && this.carry.holidaysPrevious) {
      this._formHolidaysPrevious = this.carry.holidaysPrevious;
    }
    return this._formHolidaysPrevious;
  }
  setFormHolidaysPrevious(evt: Event) {
    let target = evt.target as HTMLInputElement;
    this._formHolidaysPrevious = Number(target.value);
  }
  onSubmit(evt: Event) {
    evt.preventDefault();
    this.carry.saldo = this._formSaldo ? this._formSaldo : this.carry.saldo;
    this.carry.holidays = this._formHolidays
      ? this._formHolidays
      : this.carry.holidays;
    this.carry.holidaysPrevious = this._formHolidaysPrevious
      ? this._formHolidaysPrevious
      : this.carry.holidaysPrevious;
    this.$store
      .dispatch("workingTime/setCarry", this.carry)
      .then(() => this.$store.dispatch("workingTime/getCarry"))
      .then(() => this.$emit("submitted"));
  }
  onReset(evt: Event) {
    evt.preventDefault();
    this._formSaldo = this.carry.saldo;
    this._formHolidays = this.carry.holidays;
    this._formHolidaysPrevious = this.carry.holidaysPrevious;
    this.show = false;
    this.$nextTick().then(() => (this.show = true));
  }
  onCancel(evt: Event) {
    this.onReset(evt);
    this.$emit("submitted");
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
