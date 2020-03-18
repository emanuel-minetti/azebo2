<template>
  <div>
    <b-table
      bordered
      striped
      hover
      :items="upperDays"
      primary-key="date"
      :fields="fields"
      :tbody-tr-class="rowClass"
      thead-class="sticky"
      @row-clicked="rowClickHandler"
    />
    <div v-if="formShown" id="lower">
      <DayForm id="form" v-on:submitted="onSubmitted" />
      <b-table
        bordered
        striped
        hover
        :items="lowerDays"
        primary-key="date"
        :fields="fields"
        :tbody-tr-class="rowClass"
        thead-class="sticky"
        @row-clicked="rowClickHandler"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { FormatterService } from "@/services";
import { WorkingDay } from "@/models";
import DayForm from "@/components/DayForm.vue";

@Component({
  components: {
    DayForm
  }
})
export default class MonthTable extends Vue {
  formShown = false;
  dateToEdit = null as null | Date;

  get upperDays(): WorkingDay[] {
    if (this.$store.state.workingTime.month.days) {
      let upperDays = this.$store.state.workingTime.month.days.slice();
      if (this.formShown) {
        upperDays = upperDays.filter(
          (day: WorkingDay) => day.date.valueOf() < this.dateToEdit!.valueOf()
        );
      }
      return upperDays;
    }
    return [];
  }

  get lowerDays(): WorkingDay[] {
    if (this.$store.state.workingTime.month.days && this.formShown) {
      let lowerDays = this.$store.state.workingTime.month.days.slice();
      lowerDays = lowerDays.filter(
        (day: WorkingDay) => day.date.valueOf() > this.dateToEdit!.valueOf()
      );
      return lowerDays;
    }
    return [];
  }

  // specifies the shown columns of the table
  get fields() {
    return [
      {
        key: "date",
        label: "Datum",
        class: "small-column",
        formatter: this.formatDate
      },
      {
        key: "begin",
        label: "Beginn",
        class: "small-column",
        formatter: FormatterService.toGermanTime
      },
      {
        key: "end",
        label: "Ende",
        class: "small-column",
        formatter: FormatterService.toGermanTime
      },
      {
        key: "timeOff",
        label: "Dienstbefreiung"
      },
      {
        key: "comment",
        label: "Kommentar"
      },
      {
        key: "break",
        label: "Pause",
        class: "small-column",
        formatter: this.formatBreak
      },
      {
        key: "totalTime",
        label: "Anwesend",
        class: "small-column"
      },
      {
        key: "actualTime",
        label: "Ist",
        class: "small-column"
      },
      {
        key: "targetTime",
        label: "Soll",
        class: "small-column"
      },
      {
        key: "saldoTime",
        label: "Saldo",
        class: ["small-column", "saldo"]
      }
    ];
  }

  get finalized() {
    return this.$store.state.workingTime.carry.finalized;
  }

  // adds a class for non working days
  rowClass(day: WorkingDay, type: string) {
    if (!day || type !== "row") return;
    if (!day.isWorkingDay) return "not-a-working-day";
  }

  // formats the shown date
  formatDate(date: Date, key: string, day: WorkingDay) {
    const dateString = FormatterService.toLongGermanDate(date);
    return day.isHoliday ? dateString + " " + day.holidayName : dateString;
  }

  // formats the break column
  formatBreak(hasBreak: boolean, key: string, day: WorkingDay): string {
    if (day && !day.hasWorkingTime) return "";
    return hasBreak ? "Ja" : "Nein";
  }

  rowClickHandler(row: any) {
    if (!this.finalized) {
      // store the date ...
      this.dateToEdit = row.date;
      this.$store.commit("setDayToEdit", this.dateToEdit);
      // ... and show the form
      if (!this.formShown) {
        this.formShown = true;
      } else {
        // form was already shown for another day
        this.formShown = false;
        this.$nextTick(() => {
          this.formShown = true;
        });
      }
    }
  }

  onSubmitted() {
    this.formShown = false;
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
div {
  text-align: center;
  width: 90%;
  margin-left: 5%;
}

/deep/ table td {
  vertical-align: middle;
}

/deep/ .sticky th {
  position: sticky;
  top: 0;
  background-color: white;
  background-clip: padding-box;
}

/deep/ .small-column {
  width: 90px;
}

/deep/ .not-a-working-day {
  color: #e70036;
}

/deep/ .saldo {
  border-left: 2px solid #211e1e;
}

#lower {
  width: 100%;
  margin: 0;
}
</style>
