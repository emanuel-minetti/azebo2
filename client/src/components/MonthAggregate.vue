<template>
  <div id="month-aggregate" class="mx-0 mx-lg-auto">
    <b-table-lite
      :caption="capture"
      caption-top
      :fields="fields"
      :items="items"
      bordered
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Carry, WorkingMonth } from "/src/models";
import { mapState } from "vuex";
import { GermanKwService } from "/src/services";

@Component({
  computed: {
    ...mapState("workingTime", ["carryResult", "month", "previous"]),
  },
})
export default class MonthAggregate extends Vue {
  carryResult!: Carry;
  month!: WorkingMonth;
  previous!: WorkingMonth;
  fields = [
    {
      key: "key",
      label: "",
      class: "first_column",
    },
    {
      key: "carryResult",
      label: "Übertrag",
    },
    {
      key: "month",
      label: "Laufender Monat",
    },
    {
      key: "total",
      label: "Gesamt",
    },
  ];

  get items() {
    let carryResult = {
      key: "Arbeitszeit",
      carryResult: null,
      month: this.$store.getters['workingTime/timeTotal'].toString(false)
          + " (Davon Mobil: "
          + this.$store.getters["workingTime/timeMobileTotal"].toString(false)
          + " = "
          + (this.$store.getters["workingTime/timeMobileTotal"].getMillis()
          / this.$store.getters['workingTime/timeTotal'].getMillis() * 100).toFixed(2)
          + "%)",
      total: null
    };
    let carrySaldo = {
      key: "Saldo",
      carryResult: this.carryResult.saldo
        ? this.carryResult.saldo.toString()
        : "",
      month: (this.month.saldo ?? this.$store.getters["workingTime/saldo"])
          + (this.month.cappedSaldo ? " (Gekappt)" : ""),
      total: this.$store.getters["workingTime/saldoTotal"],
    };
    if (this.carryResult.hasMissing) {
      carrySaldo.carryResult = "Unbekannt";
      carrySaldo.total = carrySaldo.month;
    }
    return [carryResult, carrySaldo];
  }

  get capture() {
    let capture = "Zusammenfassung: ";
    if (this.carryResult.hasMissing) {
      let missing = this.carryResult.missing;
      capture += "(Eine Darstellung des Übertrags ist nicht möglich, denn ";
      if (missing.length === 1) {
        capture += "der Monat " + missing[0] + " ist ";
      } else {
        capture += "die Monate ";
        missing.forEach((missed, index) => {
          if (index + 1 === missing.length) {
            capture += " und ";
          }
          capture += missed;
          if (index + 3 <= missing.length) {
            capture += ", ";
          }
        });
        capture += " sind ";
      }
      capture += "noch nicht abgeschlossen.)";
    }
    return capture;
  }

  get kWs(): number[] {
    const firstOfMonth = new Date(this.month.monthDate);
    firstOfMonth.setDate(1);
    const lastOfMonth = new Date(this.month.monthDate);
    lastOfMonth.setMonth(lastOfMonth.getMonth() + 1);
    lastOfMonth.setDate(0);
    let firstKw = GermanKwService.getGermanKW(firstOfMonth);
    let lastKw = GermanKwService.getGermanKW(lastOfMonth);
    if (GermanKwService.getGermanDay(firstOfMonth) >= 5) {
      if (firstKw >= 52) firstKw = 1;
      else firstKw++;
    }
    if (GermanKwService.getGermanDay(lastOfMonth) < 4) lastKw--;
    const result = [];
    if (firstKw >= 52) {
      result.push(firstKw);
      firstKw = 1;
    }
    for (let i = firstKw; i <= lastKw; i++) {
      result.push(i);
    }
    return result;
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
#month-aggregate {
  text-align: center;
  width: 90%;
  overflow: hidden;
}

table {
  width: 650px;
  font-size: larger;
  float: left;
}

/deep/ table caption {
  font-weight: bold;
  color: inherit;
}

/deep/ .first_column {
  font-weight: bold;
}

/deep/ table td {
  text-align: center;
  vertical-align: middle;
}

/deep/ table th {
  text-align: center;
}
</style>
