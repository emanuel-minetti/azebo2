<template>
  <b-form>
    <b-form-group label="Arbeitszeit:">
      <b-form-radio-group
        v-model="workingTimeMode"
        :options="workingTimeOptions"
      >
      </b-form-radio-group>
      <div v-if="workingTimeMode === 'relative'">
        <b-form-group label="Anteil an der Wochenarbeitszeit">
          <!-- TODO insert tab-index -->
          <b-form-input v-model="percentage" type="number" min="0" max="100">
          </b-form-input>
        </b-form-group>
      </div>
      <div v-if="workingTimeMode === 'fixed'">
        <b-form-group label="Wochenarbeitszeit in Stunden und Minuten ">
          <!-- TODO insert tab-index -->
          <SaldoInput v-model:prop-saldo="fixed" :prop-sign="false" />
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
    SaldoInput,
  },
})
export default class RulesForm extends Vue {
  workingTimeMode = "full";
  percentage = 100;
  fixed = Saldo.createFromMillis(1000000);

  workingTimeOptions = [
    { text: "Vollzeit", value: "full" },
    { text: "Anteilige Arbeitszeit", value: "relative" },
    { text: "Feste Arbeitszeit", value: "fixed" },
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
