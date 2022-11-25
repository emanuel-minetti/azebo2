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
          id="password"
          v-model="form.password"
          type="password"
          placeholder="Ihr Passwort"
          :state="isPasswordValid"
        />
        <b-form-invalid-feedback id="password">
          Bitte geben Sie ein Passwort an!
        </b-form-invalid-feedback>
      </b-form-group>
      <b-alert v-model="loginError" variant="danger" dismissible>
        Benutzername oder Passwort ist ungülig!
      </b-alert>
      <b-button type="submit" variant="primary">Absenden</b-button>
      <b-button id="reset" type="reset" variant="secondary">
        Zurücksetzen
      </b-button>
    </b-form>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Title } from "@/components";

@Component({
  components: { Title },
})
export default class Login extends Vue {
  form = {
    username: "",
    password: "",
  };
  submitted = false;
  loginError = false;

  get isUsernameValid() {
    const valid =
      this.form.username.trim() !== "" && this.form.username.length <= 30;
    return this.submitted ? valid : null;
  }

  get isPasswordValid() {
    const valid =
      this.form.password.trim().length >= 4 && this.form.password.length <= 12;
    return this.submitted ? valid : null;
  }

  onSubmit(evt: Event) {
    evt.preventDefault();
    this.submitted = true;
    const credentials = {
      username: this.form.username,
      password: this.form.password,
    };
    this.$store
      .dispatch("login", credentials)
      .then(() => {
        //"redirect" after successful login
        if (this.$route.query.redirect) {
          this.$router
            .push(this.$route.query.redirect.toString())
            .catch(() => {});
        } else {
          this.$router.push({ name: "home" }).catch(() => {});
        }
      })
      //show error message on failure
      .catch(() => {
        this.loginError = true;
        this.submitted = false;
      });
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
