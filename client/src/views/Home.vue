<template>
  <div class="home">
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
  get monthName() {
    return this.$store.state.workingTime.month.monthName;
  }
  //noinspection JSUnusedGlobalSymbols
  mounted() {
    this.$store.dispatch("getMonth", new Date()).then(() => {});
  }
}
</script>
