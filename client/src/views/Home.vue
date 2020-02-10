<template>
  <div class="home">
    <div class="alert-danger" v-if="error" v-html="error" />
    <Title v-bind:prop-title="monthName" />
    <MonthTable />
  </div>
</template>

<script lang="ts">
import { Title, MonthTable } from "@/components";
import { Component, Vue, Watch } from "vue-property-decorator";

@Component({
  components: {
    Title,
    MonthTable
  }
})
export default class Home extends Vue {
  error = "";
  get monthName() {
    return this.$store.state.workingTime.month.monthName;
  }
  @Watch("$route")
  routeChanged(to: any) {
    let month = new Date();
    if (to.name === "month") {
      month.setMonth(Number(to.params.id) - 1);
    }
    this.$store.dispatch("getMonth", month).catch(reason => {
      this.error =
        "Es gab ein Problem beim Laden der Daten für diesen Monat:<br/>" +
        reason;
    });
  }
  //noinspection JSUnusedGlobalSymbols
  mounted() {
    let month = new Date();
    if (this.$route.name === "month") {
      month.setMonth(Number(this.$route.params.id) - 1);
    }
    this.$store.dispatch("getMonth", month).catch(reason => {
      this.error =
        "Es gab ein Problem beim Laden der Daten für diesen Monat:<br/>" +
        reason;
    });
  }
}
</script>
