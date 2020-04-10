<template>
  <div class="home">
    <div v-if="loading" class="d-flex justify-content-center mb-3">
      <b-spinner id="spinner" label="Loading..."></b-spinner>
    </div>
    <div v-else>
      <div class="alert-danger" v-if="error" v-html="error" />
      <Title v-bind:prop-title="monthName" />
      <MonthTable />
      <MonthAggregate />
    </div>
  </div>
</template>

<script lang="ts">
import { Title, MonthTable, MonthAggregate } from "@/components";
import { Component, Vue, Watch } from "vue-property-decorator";
import { Route } from "vue-router";
import { mapState } from "vuex";

@Component({
  components: {
    Title,
    MonthTable,
    MonthAggregate,
  },
  computed: {
    ...mapState(["loading"]),
  },
})
export default class Home extends Vue {
  loading!: boolean;
  error = "";
  get monthName() {
    return this.$store.state.workingTime.month.monthName;
  }

  @Watch("$route")
  routeChanged(to: Route) {
    this.error = "";
    // TODO remove debugging
    let month = new Date("2020-03-31");
    if (to.name === "month") {
      month.setMonth(Number(to.params.month) - 1);
      if (to.params.year) {
        month.setFullYear(Number(to.params.year));
      }
    }
    this.$store.dispatch("workingTime/getMonth", month).catch((reason) => {
      this.error =
        "Es gab ein Problem beim Laden der Daten für diesen Monat:<br/>" +
        reason;
      this.$store.commit("cancelLoading");
    });
  }
  //noinspection JSUnusedGlobalSymbols
  mounted() {
    this.routeChanged(this.$route);
  }
}
</script>

<style scoped>
#spinner {
  width: 4rem;
  height: 4rem;
  margin-bottom: 100px;
}
</style>
