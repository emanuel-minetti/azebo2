<template>
  <b-form v-if="show" @submit="onSubmit" @reset="onReset">
    <div v-if="loading" class="d-flex justify-content-center mb-3">
      <b-spinner id="spinner" label="Loading..."></b-spinner>
    </div>
    <div v-if='error.length > 0' class='alert-danger alert'>
      {{ error }}
    </div>
    <!--TODO Should be shown to non new users with a warning (See #30)-->
    <b-form-group label="Saldo Übertrag:" label-for="carry-over-input">
      <SaldoInput
        v-if="showSaldoInput"
        :prop-disabled="propDisabled"
        :prop-saldo="carry.saldo"
        :prop-sign="true"
        @update-saldo="updateSaldo"
      />
    </b-form-group>
    <div v-if="!propDisabled">
      <b-button type="submit" variant="primary">Absenden</b-button>
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
    </div>
  </b-form>
</template>

<script lang="ts">
import { Carry, Saldo } from "/src/models";
import SaldoInput from "/src/components/SaldoInput.vue";
import { defineComponent } from "vue";

export default  defineComponent({
  name: "CarryForm",
  components: {
    SaldoInput
  },
  props: {
    propDisabled: Boolean,
  },
  emits: [
      'submitted',
  ],
  data() {
      return {
        loading: true,
        error: '',
        show: true,
        showSaldoInput: true,
        carry: new Carry(),
      };
  },
  mounted() {
    this.loading = this.$store.state.loading;
    this.$store.dispatch("workingTime/getCarry").then(() =>
        this.carry = this.$store.state.workingTime.carry
    ).catch((reason) => {
      this.error = "Es gab ein Problem beim Laden des Übertrags:<br/>" + reason;
      this.$store.commit("cancelLoading");
    });

  },
  methods: {
    updateSaldo(saldo: Saldo) {
      this.carry.saldo = saldo;
      this.showSaldoInput = false;
      this.$nextTick().then(() => (this.showSaldoInput = true));
    },
    onSubmit(evt: Event) {
      evt.preventDefault();
      this.$store
          .dispatch("workingTime/setCarry", this.carry)
          .then(() => this.$store.dispatch("workingTime/getCarry"))
          .then(() => this.$emit("submitted"));
    },
    onReset(evt: Event) {
      evt.preventDefault();
      this.show = false;
      this.$nextTick().then(() => (this.show = true));
    },
    onCancel(evt: Event) {
      this.onReset(evt);
      this.$emit("submitted");
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

>>> label,
>>> legend {
  font-size: large;
  font-weight: bold;
}
</style>
