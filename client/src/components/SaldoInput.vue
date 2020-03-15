<template>
  <div>
    <b-form-input
      type="text"
      :value="syncedSaldo.toString(propSign)"
      v-on:focus="onfocus"
    />
    <b-modal
      hide-backdrop
      v-model="modalShow"
      title="Bitte geben Sie den Betrag in Stunden und Minuten (oder nur in Minuten) ein:"
      @ok="handleOk"
    >
      <div class="d-block text-center">
        <b-form>
          <b-form-group label="Stunden" label-for="hour-input">
            <b-form-input
              type="number"
              min="0"
              max="999"
              v-model="hours"
              autofocus
            >
            </b-form-input>
          </b-form-group>
          <b-form-group label="Minuten" label-for="hour-input">
            <b-form-input type="number" min="0" max="999" v-model="minutes">
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
import { Component, Prop, PropSync, Vue } from "vue-property-decorator";
import { Saldo } from "@/models";

@Component({})
export default class SaldoInput extends Vue {
  @PropSync("propSaldo", {
    type: Saldo,
    default: Saldo.createFromMillis(0)
  })
  syncedSaldo!: Saldo;
  @Prop({
    type: Boolean
  })
  propSign!: boolean;
  modalShow = false;
  hours = "";
  minutes = "";
  positive = true;

  onfocus(evt: Event) {
    this.hours = String(this.syncedSaldo.hours);
    this.minutes = String(this.syncedSaldo.minutes);
    this.modalShow = true;
    let blur = evt.target as HTMLElement;
    blur.blur();
  }

  handleOk() {
    this.syncedSaldo = Saldo.createFromMillis(
      (Number(this.hours) * 60 + Number(this.minutes)) * 60 * 1000,
      this.positive
    );
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
