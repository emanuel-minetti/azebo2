<template>
  <div class="mx-0 mx-lg-auto">
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
    >
      <template #cell(mobile_working)="data">
        <b-icon-circle-fill
          v-if="data.item.hasWorkingTime && data.item.mobileWorking"
        ></b-icon-circle-fill>
        <b-icon-circle v-else-if="data.item.hasWorkingTime"></b-icon-circle>
        <div v-else></div>
      </template>
    </b-table>
    <div v-if="formShown" id="lower">
      <DayForm id="form" @submitted="onSubmitted" />
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
      >
        <template #cell(mobile_working)="data">
          <b-icon-circle-fill
            v-if="data.item.hasWorkingTime && data.item.mobile_working"
          ></b-icon-circle-fill>
          <b-icon-circle v-else-if="data.item.hasWorkingTime"></b-icon-circle>
          <div v-else></div>
        </template>
      </b-table>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { FormatterService, GermanKwService } from "/src/services";
import { WorkingDay } from "/src/models";
import { timeOffsConfig } from "/src/configs";
import DayForm from "/src/components/DayForm.vue";

@Component({
  components: {
    DayForm,
  },
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
        formatter: this.formatDate,
      },
      {
        key: "begin",
        label: "Beginn",
        class: "small-column",
        formatter: FormatterService.toGermanTime,
      },
      {
        key: "end",
        label: "Ende",
        class: "small-column",
        formatter: FormatterService.toGermanTime,
      },
      {
        key: "break",
        label: "Pause",
        class: "small-column",
        formatter: this.formatBreak,
      },
      {
        key: "timeOff",
        label: "Bemerkung",
        formatter: this.formatTimeOff,
      },
      {
        key: "comment",
        label: "Anmerkung",
      },
      {
        key: "mobile_working",
        label: "Mobiles Arbeiten",
        thStyle: { width: "31px" },
      },
      {
        key: "totalTime",
        label: "Anwesend",
        class: "small-column",
      },
      {
        key: "actualTime",
        label: "Ist",
        class: "small-column",
      },
      {
        key: "targetTime",
        label: "Soll",
        class: "small-column",
      },
      {
        key: "saldoTime",
        label: "Saldo",
        class: ["small-column", "saldo"],
      },
    ];
  }

  get finalized() {
    return this.$store.state.workingTime.carry.finalized;
  }

  // adds a class for non-working days
  rowClass(day: WorkingDay, type: string) {
    if (!day || type !== "row") return;
    if (!day.isCommonWorkingDay) return "not-a-working-day";
  }

  // formats the shown date
  formatDate(date: Date, key: string, day: WorkingDay) {
    const dateString = FormatterService.toLongGermanDate(date);
    const kwString =
      date.getDay() !== 1
        ? ""
        : " " + GermanKwService.getGermanKW(date) + ". KW";
    return (
      (day.isHoliday ? dateString + " " + day.holidayName : dateString) +
      kwString
    );
  }

  formatTimeOff(timeOff: string): string {
    const element = timeOffsConfig.find((element) => element.value == timeOff);
    return element ? element.text : "";
  }

  // formats the break column
  formatBreak(break_date: Date, key: string, day: WorkingDay): string {
    if (day && !day.hasWorkingTime) return "";
    return FormatterService.toGermanTime(break_date);
  }

  rowClickHandler(row: WorkingDay) {
    if (!this.finalized) {
      // store the date ...
      this.dateToEdit = row.date;
      this.$store.commit("workingTime/setDayToEdit", this.dateToEdit);
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
