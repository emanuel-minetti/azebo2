<template>
<div id='month-form' class='mx-auto'>
  <b-button v-if='!month.finalized' variant='primary'>{{ closeButtonText }}</b-button>
  <b-button v-if='month.closed' class='ml-2' variant='primary'>Monat ausdrucken</b-button>
</div>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import { WorkingMonth } from "/src/models";
export default defineComponent({
  name: "MonthForm",
  data() {
    return {
      missing: new Array<number>(),
      month: this.$store.state.workingTime.month as WorkingMonth,
    };
  },
  computed: {
    closeButtonText() {
      return this.month.closed ? 'Monatsabschluss zurücknehmen' : "Monat abschließen";
    },
  },
});
</script>

<style scoped>
#month-form {
  text-align: center;
  width: 90%;
  overflow: hidden;
}

</style>