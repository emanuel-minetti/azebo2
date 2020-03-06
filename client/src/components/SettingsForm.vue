<template>
  <b-form>
    <b-form-group label="Arbeitszeit:">
      <b-form-radio-group :options="workingTimeOptions" v-model="relative">
      </b-form-radio-group>
      <div v-if="relative">
        hallo
      </div>
    </b-form-group>
    <b-form-group label="Saldo Übertrag:" label-for="carry-over-input">
      <!--TODO introduce component for saldo -->
      <b-form-input id="carry-over-input" type="time"></b-form-input>
    </b-form-group>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";

@Component
export default class SettingsForm extends Vue {
  relative = true;

  workingTimeOptions = [
    { text: "Vollzeit / anteilige Arbeitszeit", value: true },
    { text: "Feste Wochenarbeitszeit", value: false }
  ];

  onRelationalEntered(evt: Event) {
    console.log("Haloo");
    console.log(evt);
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
