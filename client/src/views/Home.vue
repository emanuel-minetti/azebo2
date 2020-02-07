<template>
  <div class="home">
    <div class="alert-danger" v-if="error" v-html="error" />
    <Title v-bind:prop-title="monthName" />
    <MonthTable />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Title, MonthTable } from "@/components";

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
  //noinspection JSUnusedGlobalSymbols
  mounted() {
    this.$store.dispatch("getMonth", new Date()).catch(reason => {
      this.error =
        "Es gab ein Problem beim Laden der Daten für diesen Monat:<br/>" +
        reason;
    });
  }
}
</script>
