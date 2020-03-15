<template>
  <b-form>
    <!--TODO Should only be shown to new users (See #30)-->
    <b-form-group label="Saldo Übertrag:" label-for="carry-over-input">
      <SaldoInput :prop-saldo.sync="carryOver" :prop-sign="true" />
    </b-form-group>
    <b-form-group label="Arbeitszeit:">
      <b-form-radio-group
        :options="workingTimeOptions"
        v-model="workingTimeMode"
      >
      </b-form-radio-group>
      <div v-if="workingTimeMode === 'relative'">
        <b-form-group label="Anteil an der Wochenarbeitszeit">
          <!-- TODO insert tab-index -->
          <b-form-input type="number" min="0" max="100" v-model="percentage">
          </b-form-input>
        </b-form-group>
      </div>
      <div v-if="workingTimeMode === 'fixed'">
        <b-form-group label="Wochenarbeitszeit in Stunden und Minuten ">
          <!-- TODO insert tab-index -->
          <SaldoInput :prop-saldo.sync="fixed" :prop-sign="false" />
        </b-form-group>
      </div>
    </b-form-group>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import SaldoInput from "@/components/SaldoInput.vue";
import { Saldo } from "@/models";

@Component({
  components: {
    SaldoInput
  }
})
export default class SettingsForm extends Vue {
  workingTimeMode = "full";
  percentage = 100;
  // TODO Get Saldo from server!
  carryOver = Saldo.createFromMillis(1000000);
  fixed = Saldo.createFromMillis(1000000);

  workingTimeOptions = [
    { text: "Vollzeit", value: "full" },
    { text: "Anteilige Arbeitszeit", value: "relative" },
    { text: "Feste Arbeitszeit", value: "fixed" }
  ];
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
