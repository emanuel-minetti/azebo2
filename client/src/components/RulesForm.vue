<template>
  <div>
    <button id='show' class='btn btn-primary mx-auto' @click='showForm = !showForm'>Arbeitszeitregelung hinzufügen</button>
    <div v-if='showForm'>
      <b-form @submit='onSubmit($event)'>
        <label for='validFrom'>Regelungsbeginn:</label>
        <b-form-input id='validFrom' v-model='validFrom' type='date' value=''></b-form-input>
        <label for='validFrom'>Regelungsende (Für "Bis auf Weiteres" bitte leer lassen):</label>
        <b-form-input id='validFrom' v-model='validTo' type='date' value=''></b-form-input>
        <label for='validFrom'>Prozentsatz der vollen Arbeitszeit:</label>
        <b-form-input id='validFrom' v-model='percentage' type='number' value=''></b-form-input>
        <b-form-group label='Wochentage'>
          <b-form-checkbox-group id='weekdays' v-model='weekdays' checked='[]'>
            <b-form-checkbox value='1'>Montag</b-form-checkbox>
            <b-form-checkbox value='2'>Dienstag</b-form-checkbox>
            <b-form-checkbox value='3'>Mittwoch</b-form-checkbox>
            <b-form-checkbox value='4'>Donnerstag</b-form-checkbox>
            <b-form-checkbox value='5'>Freitag</b-form-checkbox>
          </b-form-checkbox-group>
        </b-form-group>
        <button class='btn btn-primary'>Absenden</button>
      </b-form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  data() {
    return {
      showForm: false,
      validFrom: Date,
      validTo: Date,
      percentage: Number,
      weekdays: [],
      //rule: WorkingRule,
    }
  },
  methods: {
    onSubmit(evt: Event) {
      evt.preventDefault();
      let data = {
        "valid_from": this.validFrom,
        "valid_to": this.validTo,
        "percentage": this.percentage,
        "weekdays": this.weekdays,
        "has_weekdays": this.weekdays.length !== 5 && this.weekdays.length !== 0,
      }
      this.$store.dispatch('workingTime/setRule', data);
    },
  },
});
</script>

<style scoped>
form {
  width: 30%;
  margin-left: 35%;
  margin-right: 35%;
}

#show {
  width: auto;
}

>>> label,
>>> legend {
  font-size: large;
  font-weight: bold;
}
</style>
