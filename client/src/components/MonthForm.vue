<template>
  <div id='month-form' class='mx-auto'>
    <b-alert v-if='missing.length' variant='danger' show>
      {{ missingText }}
    </b-alert>
    <b-alert v-if='success' variant='success' show='5'> {{ successText }}</b-alert>
    <b-button
        v-if='!month.finalized'
        variant='primary'
        @click='onSubmit'
    >
      {{ closeButtonText }}
    </b-button>
    <b-button
        v-if='month.closed'
        class='ml-2'
        variant='primary'
        @click='onPrint'
    >
      Monat ausdrucken
    </b-button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { WorkingMonth } from "/src/models";
import { PrintService } from "/src/services";

export default defineComponent({
  name: "MonthForm",
  data() {
    return {
      missing: new Array<string>(),
      month: this.$store.state.workingTime.month as WorkingMonth,
      success: false,
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
    },
    successText() {
      return !this.month.saldo
          ? "Sie haben den Monatsabschluss erfolgreich zurückgenommen."
          : "Sie haben den Monat "
          + this.month.monthDate.toLocaleString('de-DE', { month: 'long' })
          + " erfolgreich abgeschlossen."
          ;
    },
  },
  methods: {
    onSubmit() {
      this.success = false;
      this.$store
          .dispatch("workingTime/closeMonth", this.month)
          .then((data) => {
                if (!data.result.ok) {
                  this.missing = data.result.missing;
                } else {
                  this.success = true;
                  this.month.serverMonth = data.result.month;
                }
              }
          );
    },
    onPrint() {
      PrintService.printMonth(this.month.monthDate)
          .then(data => {
            this.$store.dispatch('workingTime/getMonth', this.month.monthDate)
                .then(() => {
                  window.location.href = window.location.protocol + "//" + window.location.hostname
                      + "/files/" + data.result.file;
                });
          });
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