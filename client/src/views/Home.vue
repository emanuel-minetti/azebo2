<template>
  <div class="home">
    <div class="alert-danger" v-if="error" v-html="error" />
    <Title v-bind:prop-title="monthName" />
    <MonthTable />
    <MonthAggregate />
  </div>
</template>

<script lang="ts">
import { Title, MonthTable, MonthAggregate } from "@/components";
import { Component, Vue, Watch } from "vue-property-decorator";
import { Route } from "vue-router";

@Component({
  components: {
    Title,
    MonthTable,
    MonthAggregate
  }
})
export default class Home extends Vue {
  error = "";
  get monthName() {
    return this.$store.state.workingTime.month.monthName;
  }
  @Watch("$route")
  routeChanged(to: Route) {
    let month = new Date();
    if (to.name === "month") {
      month.setMonth(Number(to.params.month) - 1);
      if (to.params.year) {
        month.setFullYear(Number(to.params.year));
      }
    }
    this.$store.dispatch("getMonth", month).catch(reason => {
      this.error =
        "Es gab ein Problem beim Laden der Daten für diesen Monat:<br/>" +
        reason;
    });
  }
  //noinspection JSUnusedGlobalSymbols
  mounted() {
    this.routeChanged(this.$route);
  }
}
</script>
