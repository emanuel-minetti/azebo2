<template>
  <b-form @submit="onSubmit" @reset="onReset" v-if="show">
    <b-form-group label="Arbeitsbeginn:" label-for="begin-input">
      <b-form-input
        id="begin-input"
        v-model="form.begin"
        required
        placeholder="Arbeitsbeginn"
      ></b-form-input>
    </b-form-group>
    <b-button type="submit" variant="primary">Submit</b-button>
    <b-button type="reset" variant="danger">Reset</b-button>
  </b-form>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";

@Component
export default class DayForm extends Vue {
  form = {
    begin: ""
  };
  show = true;

  onSubmit(evt: Event) {
    evt.preventDefault();
    console.log(JSON.stringify(this.form));
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

<style scoped></style>
