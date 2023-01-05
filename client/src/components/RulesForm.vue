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
    </b-form-group>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";

@Component({})
export default class RulesForm extends Vue {
  workingTimeMode = "full";
  percentage = 100;

  workingTimeOptions = [
    { text: "Vollzeit", value: "full" },
    { text: "Anteilige Arbeitszeit", value: "relative" },
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
