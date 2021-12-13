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
        size="lg"
        class="left"
      >
        <b-form-select
          id="select"
          v-model="form.timeOff"
          :options="timeOffOptions"
        ></b-form-select>
      </b-form-group>
      <b-form-group label="Bemerkung:" label-for="comment-input">
        <b-form-textarea id="comment-input" size="sm" v-model="form.comment">
        </b-form-textarea>
      </b-form-group>
      <b-form-group label="Mobiles Arbeiten:" label-for="mobile-working-input">
        <b-form-checkbox
          id="mobile-working-input-input"
          v-model="form.mobile_working"
          class="left"
        >
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

const localTimeFormatOptions: Intl.DateTimeFormatOptions = {
  hour: "2-digit",
  minute: "2-digit",
};

@Component
export default class DayForm extends Vue {
  show = true;
  timeOffOptions = timeOffsConfig;

  // get a copy of the `WorkingDay` to work on
  form = Object.assign(
    new WorkingDay(),
    this.$store.state.workingTime.dayToEdit
  ) as WorkingDay;

  mounted() {
    // scroll form to top
    let target = document.getElementById("form") as HTMLElement;
    // `{ behaviour: "smooth" }` is not working!
    target.scrollIntoView();
  }

  get title() {
    let date = this.form.date;
    let title = date.toLocaleDateString("de-DE", { weekday: "long" });
    title += ", den ";
    title += date.toLocaleDateString("de-DE");
    title += " bearbeiten";
    return title;
  }

  get begin() {
    if (this.form.begin)
      return this.form.begin.toLocaleTimeString(
        "de-DE",
        localTimeFormatOptions
      );
    return "";
  }

  get end() {
    if (this.form.end)
      return this.form.end.toLocaleTimeString("de-DE", localTimeFormatOptions);
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
          minute: "2-digit",
        }) +
        "<br />";
      result +=
        timesConfig.longBreakDuration +
        " Minuten Pause ab: " +
        longBreak.toLocaleTimeString("de-DE", {
          hour: "2-digit",
          minute: "2-digit",
        }) +
        "<br /> ";
      result +=
        "10 Stunden erreicht ab: " +
        longDay.toLocaleTimeString("de-DE", {
          hour: "2-digit",
          minute: "2-digit",
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
      // TODO repair summer time!
      .dispatch("workingTime/setDay", this.form)
      .then(() => this.$store.dispatch("workingTime/getMonth", this.form.date))
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
    this.form.mobile_working = false;
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
