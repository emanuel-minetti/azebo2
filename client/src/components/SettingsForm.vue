<template>
  <b-form>
    <b-form-group label="Arbeitszeit:">
      <b-form-radio-group>
        <b-form-radio value="proportional">
          Vollzeit / Anteilige Wochenarbeitzeit
        </b-form-radio>
        <b-form-radio value="absolute">
          Feste Wochenarbeitszeit
        </b-form-radio>
      </b-form-radio-group>
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
export default class SettingsForm extends Vue {}
</script>

<style scoped>
form {
  width: 30%;
  margin-left: 35%;
  margin-right: 35%;
}

>>> label {
  font-size: large;
  font-weight: bold;
}
</style>
