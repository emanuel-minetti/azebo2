<template>
  <b-form v-if="show" class="mb-3" @submit="onSubmit" @reset="onReset">
    <fieldset>
      <legend>{{ title }}</legend>
      <b-form-group label="Arbeitsbeginn:" label-for="begin-input">
        <b-form-input
          id="begin-input"
          v-model="begin"
          type="time"
          placeholder="Arbeitsbeginn"
          autofocus
          @blur="validate"
        ></b-form-input>
      <ul>
        <li v-for='item in compareTimes' :key='item'> {{ item }} </li>
      </ul>
      </b-form-group>
      <b-form-group label="Arbeitsende:" label-for="end-input">
        <b-form-input
          id="end-input"
          v-model="end"
          type="time"
          placeholder="Arbeitsende"
          @blur="validate"
        ></b-form-input>
      </b-form-group>
      <b-form-group
        label="Bemerkung:"
        label-for="time-off-input"
        size="lg"
        class="left"
      >
        <b-form-select
          id="select"
          v-model="day.timeOff"
          :options="timeOffOptions"
          @change="validate"
        ></b-form-select>
      </b-form-group>
      <b-form-group label="Anmerkung:" label-for="comment-input">
        <b-form-textarea
          id="comment-input"
          v-model="day.comment"
          size="sm"
          @blur="validate"
        ></b-form-textarea>
      </b-form-group>
      <b-form-group label="Mobiles Arbeiten:" label-for="mobile-working-input">
        <b-form-checkbox
          id="mobile-working-input-input"
          v-model="day.mobileWorking"
          class="left"
          @blur="validate"
        ></b-form-checkbox>
        <div v-if="errors.length">
          <div v-if="errors.length === 1">
            Bitte korrigieren Sie den folgenden Fehler:
          </div>
          <div v-else>Bitte korrigieren Sie die folgenden Fehler:</div>
          <div v-for="(error, index) in errors" :key="index">
            <b-alert show variant="primary">
              {{ error }}
            </b-alert>
          </div>
        </div>
      </b-form-group>
      <b-button type="submit" variant="primary" :disabled="errors.length !== 0">
        Absenden
      </b-button>
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
    </fieldset>
  </b-form>
</template>

<script lang="ts">
import { timeOffsConfig, timesConfig } from "/src/configs";
import { defineComponent } from "vue";
import { WorkingDay } from "/src/models";
import DayFormValidator from "/src/validators/DayFormValidator";

const localTimeFormatOptions: Intl.DateTimeFormatOptions = {
  hour: "2-digit",
  minute: "2-digit",
};

export default defineComponent({
  name: "DayForm",
  emits: ['submitted'],
  data() {
    return {
      show: true,
      timeOffOptions: timeOffsConfig,
      errors: Array<String>(),
      day: new WorkingDay(),
    }
  },

  computed: {
    title() {
        let date = this.day.date;
        let title = date.toLocaleDateString("de-DE", { weekday: "long" });
        title += ", den ";
        title += date.toLocaleDateString("de-DE");
        title += " bearbeiten";
        return title;
    },

    begin: {
      get() {
        return this.day.begin ? this.day.begin.toLocaleTimeString('de-DE', localTimeFormatOptions) : '';
      },
      set(newValue: string) {
        this.day.begin = new Date();
        this.day.begin.setHours(
          Number(newValue.substring(0, 2)),
          Number(newValue.substring(3, 5)),
        );
      },
    },

    end: {
      get() {
        return this.day.end ? this.day.end.toLocaleTimeString('de-DE', localTimeFormatOptions) : '';
      },
      set(newValue: string) {
        this.day.end = new Date();
        this.day.end.setHours(
            Number(newValue.substring(0, 2)),
            Number(newValue.substring(3, 5)),
        );
      },
    },

    compareTimes(): Array<String> {
      const result = [];
      if (this.begin !== "") {
        const shortBreak = this.day.shortBreakFrom();
        const longBreak = this.day.longBreakFrom();
        const longDay = this.day.longDayFrom();
        result.push(
            timesConfig.breakDuration +
            " Minuten Pause ab: " +
            shortBreak.toLocaleTimeString("de-DE", localTimeFormatOptions));
        result.push(
            timesConfig.longBreakDuration +
            " Minuten Pause ab: " +
            longBreak.toLocaleTimeString("de-DE", localTimeFormatOptions));
        result.push(
            "10 Stunden erreicht ab: " +
            longDay.toLocaleTimeString("de-DE", localTimeFormatOptions));
      }
      return result;
    }
  },

  mounted() {
    // get a copy of the `WorkingDay` to work on
    this.day = Object.assign(
        new WorkingDay(),
        this.$store.state.workingTime.dayToEdit
    ) as WorkingDay;
    // scroll form to top
    let target = document.getElementById("form") as HTMLElement;
    // `{ behaviour: "smooth" }` is not working!
    target.scrollIntoView();
  },

  methods: {
    onSubmit(evt: Event) {
      evt.preventDefault();
      if (this.errors.length === 0) {
        this.$store
            .dispatch("workingTime/setDay", this.day)
            .then(() =>
                this.$store.dispatch("workingTime/getMonth", this.day.date)
            )
            .then(() => {
              this.$emit("submitted");
            });
      }
    },

    onReset(evt: Event) {
      evt.preventDefault();
      // Reset our form values
      this.day.begin = undefined;
      this.day.end = undefined;
      this.day.timeOff = undefined;
      this.day.comment = undefined;
      this.day.mobileWorking = false;
      // Trick to reset/clear native browser form validation state
      this.show = false;
      this.$nextTick(() => {
        this.show = true;
      });
    },

    onCancel() {
      this.$emit("submitted");
    },

    validate() {
      const dfv = new DayFormValidator(
          new WorkingDay(this.day),
          this.$store.state.workingTime.holidays,
          this.$store.state.workingTime.carryResult,
          this.$store.state.workingTime.month
      );
      this.errors = dfv.validate();
      return this.errors.length === 0;
    },
  }
});
</script>

<style scoped>
form {
  width: 50%;
}

>>> label {
  text-align: left;
}

.left {
  text-align: left;
}

input {
  width: 30%;
}
</style>
