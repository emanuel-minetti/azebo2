<template>
  <div>
    <button id='show' class='btn btn-primary mx-auto' @click='showForm = !showForm'>Arbeitszeitregelung hinzufügen</button>
    <div v-if='showForm'>
      <b-form @submit='onSubmit($event)'>
        <label for='validFrom'>Regelungsbeginn:</label>
        <b-form-input id='validFrom' v-model='validFrom' type='date'></b-form-input>
        <label for='validFrom'>Regelungsende (Für "Bis auf Weiteres" bitte leer lassen):</label>
        <b-form-input id='validFrom' v-model='validTo' type='date'></b-form-input>
        <label for='validFrom'>Prozentsatz der vollen Arbeitszeit:</label>
        <b-form-input id='validFrom' v-model='rule.percentage' type='number' step='0.01'></b-form-input>
        <b-form-group label='Vertrag'>
          <b-form-radio v-model='isOfficer' value='false' name='is_officer'>Angestellte*r</b-form-radio>
          <b-form-radio v-model='isOfficer' value='true' name='is_officer'>Beamte*r</b-form-radio>
        </b-form-group>
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
import { WorkingRule } from "/src/models";
import { FormatterService } from "/src/services";

export default defineComponent({
  name: "RulesForm",
  emits: ['formSubmitted'],
  data() {
    return {
      showForm: false,
      rule: new WorkingRule(),
    }
  },
  computed: {
    validFrom: {
      get() {
        return !this.rule.isNew ? FormatterService.toHtmlDateString(this.rule.validFrom) : '';
      },
      set(newValue: string) {
        this.rule.validFrom = FormatterService.convertToDate(newValue);
        //console.log(newValue);
      }
    },
    validTo: {
      get() {
        return FormatterService.toHtmlDateString(this.rule.validTo);
      },
      set(newValue: string) {
        this.rule.validTo = FormatterService.convertToDate(newValue);
      }
    },
    isOfficer: {
      get() {
        return this.rule.isOfficer ? "true" : "false";
      },
      set(newValue: string) {
        this.rule.isOfficer = (newValue === "true");
      }
    },
    weekdays: {
      get() {
          return this.rule.hasWeekdays ? this.rule.weekdays : [];

      },
      set(newValue: Array<number>) {
        this.rule.weekdays = newValue;
      }
    },
  },
  methods: {
    onSubmit(evt: Event) {
      evt.preventDefault();
      let data = {
        "valid_from": FormatterService.toServiceString(this.rule.validFrom),
        "valid_to": FormatterService.toServiceString(this.rule.validTo),
        "percentage": this.rule.percentage,
        "weekdays": this.rule.weekdays,
        "has_weekdays": this.rule.hasWeekdays,
        "is_officer": this.rule.isOfficer,
      }
      this.$store.dispatch('workingTime/setRule', data).then(() => {
        this.$emit('formSubmitted');
        this.showForm = false;
      });
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
