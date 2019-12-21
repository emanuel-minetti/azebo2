<template>
  <div>
    <Title prop-title="Login" />
    <b-form @submit="onSubmit" @reset="onReset">
      <b-form-group label="Benutzername:" label-for="username">
        <b-form-input
          id="username"
          v-model="form.username"
          placeholder="Ihr Benutzername"
          autofocus
          :state="isUsernameValid"
        />
        <b-form-invalid-feedback id="username">
          Bitte geben Sie einen Benutzernamen an!
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group label="Passwort:" label-for="password">
        <b-form-input
          type="password"
          id="password"
          v-model="form.password"
          placeholder="Ihr Passwort"
          :state="isPasswordValid"
        />
        <b-form-invalid-feedback id="password">
          Bitte geben Sie ein Passwort an!
        </b-form-invalid-feedback>
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
import LoginService from "@/services/LoginService";

@Component({
  components: { Title }
})
export default class Login extends Vue {
  form = {
    username: "",
    password: ""
  };
  show = true;
  submitted = false;

  //TODO make configurable
  get isUsernameValid() {
    const valid =
      this.form.username.trim() !== "" && this.form.username.length <= 30;
    return this.submitted ? valid : null;
  }

  //TODO make configurable
  get isPasswordValid() {
    const valid =
      this.form.password.trim().length >= 4 && this.form.password.length <= 12;
    return this.submitted ? valid : null;
  }

  onSubmit(evt: Event) {
    evt.preventDefault();
    this.submitted = true;
    LoginService.login(this.form.username, this.form.password);
  }

  onReset(evt: Event) {
    evt.preventDefault();
    this.form.username = "";
    this.form.password = "";
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
