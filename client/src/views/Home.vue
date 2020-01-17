<template>
  <div class="home">
    <!--TODO Adjust Title -->
    <Title prop-title="Startseite" />
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
  //noinspection JSUnusedGlobalSymbols
  mounted() {
    this.$store.dispatch("getMonth", new Date()).then(() => {});
  }
}
</script>
