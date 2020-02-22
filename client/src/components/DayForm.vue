<template>
  <b-form @submit="onSubmit" @reset="onReset" v-if="show" class="mb-3">
    <fieldset>
      <legend>{{ title }}</legend>
      <b-form-group label="Arbeitsbeginn:" label-for="begin-input">
        <b-form-input
          id="begin-input"
          type="time"
          v-model="form.begin"
          required
          placeholder="Arbeitsbeginn"
          autofocus
        ></b-form-input>
      </b-form-group>
      <b-form-group label="Arbeitsende:" label-for="end-input">
        <b-form-input
          id="end-input"
          type="time"
          v-model="form.end"
          required
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
    </fieldset>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { WorkingDay } from "@/models";

@Component({
  props: {
    propDate: Date
  }
})
export default class DayForm extends Vue {
  form = {
    begin: "",
    end: "",
    timeOff: "",
    comment: "",
    break: false
  };
  show = true;
  // TODO make configurable
  timeOffOptions = [
    { text: "Urlaub", value: "urlaub" },
    { text: "Gleitzeit-Tag", value: "gleitzeit" },
    { text: "AZV-Tag", value: "azv" }
  ];

  get title() {
    let date = this.$props.propDate;
    let title = date.toLocaleDateString("de-DE", { weekday: "long" });
    title += ", den ";
    title += date.toLocaleDateString("de-DE");
    title += " bearbeiten";
    return title;
  }

  onSubmit(evt: Event) {
    evt.preventDefault();
    console.log(JSON.stringify(this.form));
    this.$store.dispatch("setDay", new WorkingDay(this.form));
  }

  onReset(evt: Event) {
    evt.preventDefault();
    // Reset our form values
    this.form.begin = "";
    // Trick to reset/clear native browser form validation state
    this.show = false;
    this.$nextTick(() => {
      this.show = true;
    });
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
