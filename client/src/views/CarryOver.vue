<template>
  <div>
    <div v-if="loading"></div>
    <Title v-bind:prop-title="title" />
    <div id="intro">
      Hier können Sie Ihre Überträge aus externen Zeiterfassungssystemen
      übernehmen:
    </div>
    <CarryForm />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { CarryForm, Title } from "@/components";
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
