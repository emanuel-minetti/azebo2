<template>
  <div>
    <div v-if="loading" class="d-flex justify-content-center mb-3">
      <b-spinner id="spinner" label="Loading..."></b-spinner>
    </div>
    <Title :prop-title="title" />
    <div id="intro">
      <div v-if="editing">
        Hier können Sie Ihre Überträge bearbeiten:
        <CarryForm :prop-disabled="false" @submitted="submitted" />
      </div>
      <div v-else>
        Ihre aktuellen Überträge lauten:
        <CarryForm :prop-disabled="true" />
        <b-button variant="primary" @click="setEditing">
          Bearbeiten
        </b-button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { CarryForm, Title } from "/src/components";
import { mapState } from "vuex";

@Component({
  components: {
    Title,
    CarryForm,
  },
  computed: {
    ...mapState(["loading"]),
  },
})
export default class CarryOver extends Vue {
  loading!: boolean;
  title = "Überträge";
  error = "";
  editing = false;

  setEditing() {
    this.editing = true;
  }
  submitted() {
    this.editing = false;
  }
  // noinspection JSUnusedGlobalSymbols
  mounted() {
    this.$store.dispatch("workingTime/getCarry").catch((reason) => {
      this.error = "Es gab ein Problem beim Laden des Übertrags:<br/>" + reason;
      this.$store.commit("cancelLoading");
    });
  }
}
</script>

<style scoped>
#intro {
  font-size: 1.5rem;
  text-align: center;
}
</style>
