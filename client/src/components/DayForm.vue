<template>
  <b-form @submit="onSubmit" @reset="onReset" v-if="show" class="mb-3">
    <fieldset>
      <legend>{{ title }}</legend>
      <b-form-group label="Arbeitsbeginn:" label-for="begin-input">
        <b-form-input
          id="begin-input"
          type="time"
          v-model="begin"
          placeholder="Arbeitsbeginn"
          autofocus
        ></b-form-input>
        <div v-html="compareTimes"></div>
      </b-form-group>
      <b-form-group label="Arbeitsende:" label-for="end-input">
        <b-form-input
          id="end-input"
          type="time"
          v-model="end"
          placeholder="Arbeitsende"
        ></b-form-input>
      </b-form-group>
      <b-form-group
        label="Dienstbefreiung:"
        label-for="time-off-input"
        id="select"
      >
        <b-form-select
          id="time-off-input"
          v-model="form.timeOff"
          :options="timeOffOptions"
          class="left"
        ></b-form-select>
      </b-form-group>
      <b-form-group label="Bemerkung:" label-for="comment-input">
        <b-form-textarea id="comment-input" size="sm" v-model="form.comment">
        </b-form-textarea>
      </b-form-group>
      <b-form-group label="Ohne Pause:" label-for="break-input">
        <b-form-checkbox id="break-input" v-model="form.break" class="left">
        </b-form-checkbox>
      </b-form-group>
      <b-button type="submit" variant="primary">Absenden</b-button>
      <b-button type="reset" variant="secondary" class="ml-2">
        Zurücksetzen
      </b-button>
      <b-button
        type="button"
        variant="secondary"
        class="ml-2"
        v-on:click="onCancel"
      >
        Abbrechen
      </b-button>
    </fieldset>
  </b-form>
</template>

<script lang="ts">
import { timesConfig, timeOffsConfig } from "@/configs";
import { Component, Vue } from "vue-property-decorator";
import { WorkingDay } from "@/models";

@Component
export default class DayForm extends Vue {
  show = true;
  timeOffOptions = timeOffsConfig;

  // get a copy of the `WorkingDay`
  form = Object.assign({}, this.$store.state.workingTime.dayToEdit);

  mounted() {
    // scroll form to top
    let target = document.getElementById("form") as HTMLElement;
    // `{ behaviour: "smooth" }` is not working!
    target.scrollIntoView();
  }

  get title() {
    // cast form to `WorkingDay` at runtime
    this.form.__proto__ = WorkingDay.prototype;
    let date = this.form.date;
    let title = date.toLocaleDateString("de-DE", { weekday: "long" });
    title += ", den ";
    title += date.toLocaleDateString("de-DE");
    title += " bearbeiten";
    return title;
  }

  get begin() {
    if (this.form.begin)
      return this.form.begin.toLocaleTimeString("de-DE", {
        hour: "2-digit",
        minute: "2-digit"
      });
    return "";
  }

  get end() {
    if (this.form.end)
      return this.form.end.toLocaleTimeString("de-DE", {
        hour: "2-digit",
        minute: "2-digit"
      });
    return "";
  }

  get compareTimes() {
    if (this.begin !== "") {
      let shortBreak = new Date(this.form.begin!.valueOf());
      shortBreak.setHours(
        shortBreak.getHours() + timesConfig.breakRequiredFrom
      );
      let longBreak = new Date(this.form.begin!.valueOf());
      longBreak.setHours(
        longBreak.getHours() + timesConfig.longBreakRequiredFrom
      );
      let longDay = new Date(this.form.begin!.valueOf());
      longDay.setHours(longDay.getHours() + timesConfig.longDayFrom);
      let result =
        timesConfig.breakDuration +
        " Minuten Pause ab: " +
        shortBreak.toLocaleTimeString("de-DE", {
          hour: "2-digit",
          minute: "2-digit"
        }) +
        "<br />";
      result +=
        timesConfig.longBreakDuration +
        " Minuten Pause ab: " +
        longBreak.toLocaleTimeString("de-DE", {
          hour: "2-digit",
          minute: "2-digit"
        }) +
        "<br /> ";
      result +=
        "10 Stunden erreicht ab: " +
        longDay.toLocaleTimeString("de-DE", {
          hour: "2-digit",
          minute: "2-digit"
        });
      return result;
    }
    return "";
  }

  set begin(value: string) {
    if (value.length > 0) {
      if (!this.form.begin) this.form.begin = new Date();
      this.form.begin.setHours(
        Number(value.substring(0, 2)),
        Number(value.substring(3, 5))
      );
    } else {
      this.form.begin = undefined;
    }
  }

  set end(value: string) {
    if (value.length > 0) {
      if (!this.form.end) this.form.end = new Date();
      this.form.end.setHours(
        Number(value.substring(0, 2)),
        Number(value.substring(3, 5))
      );
    }
  }

  onSubmit(evt: Event) {
    evt.preventDefault();
    this.$store
      .dispatch("setDay", this.form)
      .then(() => this.$store.dispatch("getMonth", this.form.date))
      .then(() => {
        this.$emit("submitted");
      });
  }

  onReset(evt: Event) {
    evt.preventDefault();
    // Reset our form values
    this.form.begin = undefined;
    this.form.end = undefined;
    this.form.timeOff = undefined;
    this.form.comment = undefined;
    this.form.break = false;
    // Trick to reset/clear native browser form validation state
    this.show = false;
    this.$nextTick(() => {
      this.show = true;
    });
  }

  onCancel() {
    this.$emit("submitted");
  }
}
</script>

<style scoped>
form {
  width: 30%;
}

>>> label {
  text-align: left;
}

.left {
  text-align: left;
}

input[type="time"] {
  width: 30%;
}

>>> #select {
  width: 30%;
}
</style>
