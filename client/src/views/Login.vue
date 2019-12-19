<template>
  <div>
    <Title prop-title="Login" />
    <b-form @submit="onSubmit" @reset="onReset">
      <b-form-group label="Benutzername:" label-for="username">
        <b-form-input
          id="username"
          v-model="form.username"
          placeholder="Ihr Benutzername"
        >
        </b-form-input>
      </b-form-group>
      <b-form-group label="Passwort:" label-for="password">
        <b-form-input
          id="password"
          v-model="form.password"
          placeholder="Ihr Passwort"
        >
        </b-form-input>
      </b-form-group>
      <b-button type="submit" variant="primary">Absenden</b-button>
      <b-button id="reset" type="reset" variant="secondary">
        Zurücksetzen
      </b-button>
    </b-form>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import Title from "@/components/Title.vue";

@Component({
  components: { Title }
})
export default class Login extends Vue {
  form = {
    username: "",
    password: ""
  };
  show = true;

  onSubmit(evt: Event) {
    evt.preventDefault();
  }

  onReset(evt: Event) {
    evt.preventDefault();
    // Reset form values
    this.form.username = "";
    this.form.password = "";
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
  padding-left: 10%;
  padding-right: 10%;
}

#reset {
  margin-left: 1em;
}
</style>
