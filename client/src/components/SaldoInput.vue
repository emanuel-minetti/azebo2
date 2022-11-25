<template>
  <div>
    <b-form-input
      type="text"
      :disabled="propDisabled"
      :value="propSaldo.toString(propSign)"
      @focus="onfocus"
    />
    <b-modal
      v-model="modalShow"
      hide-backdrop
      title="Bitte geben Sie den Betrag in Stunden und Minuten (oder nur in Minuten) ein:"
      @ok="handleOk"
    >
      <div class="d-block text-center">
        <b-form>
          <b-form-group label="Stunden" label-for="hour-input">
            <b-form-input
              v-model="hours"
              type="number"
              min="0"
              max="999"
              autofocus
              onfocus="this.select()"
            >
            </b-form-input>
          </b-form-group>
          <b-form-group label="Minuten" label-for="hour-input">
            <b-form-input
              v-model="minutes"
              type="number"
              min="0"
              max="999"
              onfocus="this.select()"
            >
            </b-form-input>
          </b-form-group>
          <b-form-group v-if="propSign">
            <b-form-radio-group v-model="positive">
              <b-form-radio :value="true">Positiv</b-form-radio>
              <b-form-radio :value="false">Negativ</b-form-radio>
            </b-form-radio-group>
          </b-form-group>
        </b-form>
      </div>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from "vue-property-decorator";
import { Saldo } from "/src/models";

@Component({})
export default class SaldoInput extends Vue {
  @Prop({
    default: () => new Saldo(),
  })
  readonly propSaldo!: Saldo;
  @Prop({
    default: false,
  })
  readonly propSign!: boolean;
  @Prop({
    default: false,
  })
  readonly propDisabled!: boolean;
  modalShow = false;
  hours = "";
  minutes = "";
  positive = true;

  onfocus(evt: Event) {
    this.hours = String(this.propSaldo.hours);
    this.minutes = String(this.propSaldo.minutes);
    this.positive = Boolean(this.propSaldo.positive);
    this.modalShow = true;
    let blur = evt.target as HTMLElement;
    blur.blur();
  }

  handleOk() {
    let saldo = Saldo.createFromMillis(
      (Number(this.hours) * 60 + Number(this.minutes)) * 60 * 1000,
      this.positive
    );
    this.$emit("update-saldo", saldo);
  }
}
</script>

<style scoped>
>>> form {
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
