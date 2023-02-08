<template>
<div id='month-form' class='mx-auto'>
  <b-alert v-if='missing.length' variant='danger' show>
    {{ missingText }}
  </b-alert>
  <b-button
      v-if='!month.finalized'
      variant='primary'
      @click='onSubmit'
  >
    {{ closeButtonText }}
  </b-button>
<!--  <b-button v-if='month.closed' class='ml-2' variant='primary'>Monat ausdrucken</b-button>-->
</div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { WorkingMonth } from "/src/models";
export default defineComponent({
  name: "MonthForm",
  emits: ['submitted'],
  data() {
    return {
      missing: new Array<string>(),
      month: this.$store.state.workingTime.month as WorkingMonth,
    };
  },
  computed: {
    closeButtonText() {
      return this.month.closed ? 'Monatsabschluss zurücknehmen' : "Monat abschließen";
    },
    missingText() {
      const missingDates = this.missing.map((miss) => miss + '.' + this.month.month + '.');
      const firstPart = this.missing.length === 1
          ? "Bitte geben Sie für das Datum "
          : "Bitte geben Sie für die Daten ";
      return firstPart + missingDates.join(', ') + " eine Bemerkung oder eine Arbeitszeit an.";
    }
  },
  methods: {
    onSubmit() {
      this.$store
          .dispatch("workingTime/closeMonth", this.month)
          .then((data) => {
                if (!data.result.ok) {
                  this.missing = data.result.missing;
                }
              }
          )
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