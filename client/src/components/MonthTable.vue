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
            v-if="data.item.hasWorkingTime && data.item.mobileWorking"
          ></b-icon-circle-fill>
          <b-icon-circle v-else-if="data.item.hasWorkingTime"></b-icon-circle>
          <div v-else></div>
        </template>
      </b-table>
    </div>
  </div>
</template>

<script lang="ts">
import DayForm from "/src/components/DayForm.vue";
import { defineComponent } from "vue";
import { Saldo, WorkingDay, WorkingMonth } from "/src/models";
import { FormatterService, GermanKwService } from "/src/services";
import { timeOffsConfig } from "/src/configs";


interface TableRowData {
  day: WorkingDay;
  date: Date | null;
  begin: string | null;
  end: string | null;
  break?: Saldo;
  timeOff?: string;
  comment?: string;
  mobileWorking?: boolean;
  hasWorkingTime?: boolean;
  totalTime?: Saldo;
  actualTime?: Saldo;
  targetTime?: Saldo;
  saldoTime?: Saldo;
}

export default defineComponent({
  name: "MonthTable",
  components: {
    DayForm,
  },
  data() {
    return {
      formShown: false,
      dateToEdit: new Date(),
      month: new WorkingMonth(new Date(), []),
    }
  },
  computed: {
    days() {
      let result: Array<TableRowData> = [];
      let row: TableRowData;
      this.month.days.forEach(day => {
        row = {
          day: day as WorkingDay,
          date: day.date,
          targetTime: day.targetTime as Saldo,
          timeOff: day.timeOff,
          comment: day.comment,
          begin: null,
          end: null,
          saldoTime: day.saldoTime as Saldo,
        }
        if (day.dayParts.length === 0) {
          result.push(row);
        }
        else if (day.dayParts.length === 1) {
          row.begin = day.dayParts[0].begin;
          row.end = day.dayParts[0].end;
          row.break = day.dayParts[0].break as Saldo;
          row.mobileWorking = day.dayParts[0].mobileWorking;
          row.hasWorkingTime = true;
          row.totalTime = day.dayParts[0].totalTime as Saldo;
          row.actualTime = day.dayParts[0].actualTime as Saldo;
          result.push(row);
        } else {
          row.totalTime = day.totalTime as Saldo;
          row.break = day.break as Saldo;
          row.hasWorkingTime = true;
          row.actualTime = day.actualTime as Saldo;
          result.push(row);
          let innerRow: TableRowData;
          day.dayParts.forEach(part => {
            innerRow = {
              day: day as WorkingDay,
              date: null,
              begin: part.begin,
              end: part.end,
              break: part.break as Saldo,
              mobileWorking: part.mobileWorking,
              hasWorkingTime: day.hasWorkingTime,
              totalTime: part.totalTime as Saldo,
              actualTime: part.actualTime as Saldo,
            }
            result.push(innerRow);
          });
        }
      });
      return result;
    },
    upperDays() {
      if (!this.formShown) {
        return this.days;
      } else {
         return this.days.filter(row => row.day.date.valueOf() < this.dateToEdit.valueOf());
      }
    },
    lowerDays() {
      if (!this.formShown) {
        return [];
      } else {
        return this.days.filter(row => row.day.date.valueOf() > this.dateToEdit.valueOf());
      }
    },
    finalized() {
      return this.$store.state.workingTime.carry.finalized;
    },
    fields(): any {
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
          formatter: this.formatBeginEnd,
        },
        {
          key: "end",
          label: "Ende",
          class: "small-column",
          formatter: this.formatBeginEnd,
        },
        {
          key: "break",
          label: "Pause",
          class: "small-column",
          formatter: this.formatSaldo,
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
    },
  },
  mounted() {
    this.month = this.$store.state.workingTime.month;
  },
  methods: {
    rowClass(row: TableRowData, type: string) {
      if (!row || type !== "row") return;
      if (!row.day.isCommonWorkingDay) return "not-a-working-day";
    },

    // formats the shown date
    formatDate(date: Date | null, key: string, day: WorkingDay): string {
      if (date) {
        const dateString = FormatterService.toLongGermanDate(date);
        const kwString =
            date.getDay() !== 1
                ? ""
                : " " + GermanKwService.getGermanKW(date) + ". KW";
        return (
            (day.isHoliday ? dateString + " " + day.holidayName : dateString) +
            kwString
        );
      } else {
        return "";
      }
    },
    formatBeginEnd(value: string | null): string {
      return value ? value.substring(0,5): '';
    },
    formatTimeOff(timeOff: string): string {
      const element = timeOffsConfig.find((element) => element.value == timeOff);
      return element ? element.text : "";
    },
    // formats the break column
    formatSaldo(saldo: Saldo, key: string, day: TableRowData): string {
      if (day && !day.hasWorkingTime) return "";
      return saldo.toString(false);
    },
    rowClickHandler(row: WorkingDay) {
      if (!this.finalized && row.date) {
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
    },
    onSubmitted() {
      this.formShown = false;
    },
  },
});
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
