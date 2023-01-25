<template>
  <b-form v-if="show" class="mb-3" @submit="onSubmit" @reset="onReset">
    <fieldset>
      <legend>{{ title }}</legend>
      <div v-if='day.dayParts.length > 1'>
        <b-table
            striped
            hover
            bordered
            :items='tableItems'
            :fields='tableFields'
            primary-key='index'
        >
          <template #cell(mobileWorking)="data">
            <b-icon-circle-fill
                v-if="data.item.mobileWorking"
            ></b-icon-circle-fill>
            <b-icon-circle v-else></b-icon-circle>
          </template>
          <template #cell(action)="data">
            <b-button variant='primary' @click='editPart(data.item.index)'>Bearbeiten</b-button>
            <b-button variant='primary' class='ml-2' @click='deletePart(data.item.index)'>Löschen</b-button>
          </template>
        </b-table>
      </div>
      <div v-if='partToEdit !== -1'>
        <b-form-group label="Arbeitsbeginn:" label-for="begin-input">
          <b-form-input
              id="begin-input"
              v-model="begin"
              type="time"
              placeholder="Arbeitsbeginn"
              autofocus
              @blur="validate"
          ></b-form-input>
          <ul>
            <li v-for='item in compareTimes' :key='item'> {{ item }}</li>
          </ul>
        </b-form-group>
        <b-form-group label="Arbeitsende:" label-for="end-input">
          <b-form-input
              id="end-input"
              v-model="end"
              type="time"
              placeholder="Arbeitsende"
              @blur="validate"
          ></b-form-input>
        </b-form-group>
        <b-form-group label="Mobiles Arbeiten:" label-for="mobile-working-input">
          <b-form-checkbox
              id="mobile-working-input-input"
              v-model="mobileWorking"
              value="true"
              unchecked-value="false"
              class="left"
              @blur="validate"
          ></b-form-checkbox>
        </b-form-group>
      </div>
      <b-button variant="primary" :disabled="errors.length !== 0" @click='editPart(-1)'>
        Arbeitszeit hinzufügen
      </b-button>
      <b-form-group
        label="Bemerkung:"
        label-for="time-off-input"
        size="lg"
        class="left"
      >
        <b-form-select
          id="select"
          v-model="day.timeOff"
          :options="timeOffOptions"
          @change="validate"
        ></b-form-select>
      </b-form-group>
      <b-form-group label="Anmerkung:" label-for="comment-input">
        <b-form-textarea
          id="comment-input"
          v-model="day.comment"
          size="sm"
          @blur="validate"
        ></b-form-textarea>
      </b-form-group>
      <div v-if="errors.length">
        <div v-if="errors.length === 1">
          Bitte korrigieren Sie den folgenden Fehler:
        </div>
        <div v-else>Bitte korrigieren Sie die folgenden Fehler:</div>
        <div v-for="(error, index) in errors" :key="index">
          <b-alert show variant="primary">
            {{ error }}
          </b-alert>
        </div>
      </div>
      <b-button type="submit" variant="primary" :disabled="errors.length !== 0">
        Absenden
      </b-button>
      <b-button type="reset" variant="secondary" class="ml-2">
        Zurücksetzen
      </b-button>
      <b-button
        type="button"
        variant="secondary"
        class="ml-2"
        @click="onCancel"
      >
        Abbrechen
      </b-button>
    </fieldset>
  </b-form>
</template>

<script lang="ts">
import { timeOffsConfig, timesConfig } from "/src/configs";
import { defineComponent } from "vue";
import { WorkingDay } from "/src/models";
import WorkingDayPart from "/src/models/WorkingDayPart";

const localTimeFormatOptions: Intl.DateTimeFormatOptions = {
  hour: "2-digit",
  minute: "2-digit",
};

interface TableRowData {
  index: number;
  begin: string | null;
  end: string | null;
  mobileWorking: boolean;
}

export default defineComponent({
  name: "DayForm",
  emits: ['submitted'],
  data() {
    return {
      show: true,
      timeOffOptions: timeOffsConfig,
      errors: Array<String>(),
      day: new WorkingDay(),
      partToEdit: -1,
    }
  },

  computed: {
    title() {
        let date = this.day.date;
        let title = date.toLocaleDateString("de-DE", { weekday: "long" });
        title += ", den ";
        title += date.toLocaleDateString("de-DE");
        title += " bearbeiten";
        return title;
    },
    tableFields() {
      return [
        {
          label: '#',
          key: 'index',
          formatter: this.formatIndex,
        },
        {
          label: "Beginn",
          key: 'begin',
          formatter: this.formatBeginEnd,
        },
        {
          label: "Ende",
          key: 'end',
          formatter: this.formatBeginEnd,
        },
        {
          label: "Mobiles Arbeiten",
          key: 'mobileWorking',
        },
        {
          label: 'Aktion',
          key: 'action',
        }
      ];
    },
    tableItems() {
      let result: Array<TableRowData> = [];
      this.day.dayParts.forEach((part, index) => {
        result.push({
          index: index,
          begin: part.begin,
          end: part.end,
          mobileWorking: part.mobileWorking,
        });
      });
      return result;
    },
    begin: {
      get() {
        return this.partToEdit !== -1 ? (this.day.dayParts[this.partToEdit].begin ?
            this.day.dayParts[this.partToEdit].begin!.substring(0, 5) : '') : '';
      },
      set(newValue: string) {
        this.day.dayParts[this.partToEdit].begin = newValue;
      },
    },

    end: {
      get() {
        return this.partToEdit !== -1 ? (this.day.dayParts[this.partToEdit].end ?
            this.day.dayParts[this.partToEdit].end!.substring(0, 5) : '') : '';
      },
      set(newValue: string) {
        this.day.dayParts[this.partToEdit].end = newValue;
      },
    },

    mobileWorking: {
      get() {
        return this.partToEdit !== -1 ? this.day.dayParts[this.partToEdit].mobileWorking.toString() : '';
      },
      set(newValue: string) {
        this.day.dayParts[this.partToEdit].mobileWorking = newValue === 'true';
      }
    },

    compareTimes(): Array<String> {
      const result = [];
      if (this.begin && this.begin !== "") {
        const shortBreak = this.day.dayParts[this.partToEdit].shortBreakFrom();
        const longBreak = this.day.dayParts[this.partToEdit].longBreakFrom();
        const longDay = this.day.dayParts[this.partToEdit].longDayFrom();
        if (shortBreak) {
          result.push(
              timesConfig.breakDuration +
              " Minuten Pause ab: " +
              shortBreak.toLocaleTimeString("de-DE", localTimeFormatOptions));
        }
        if (longBreak) {
          result.push(
              timesConfig.breakDuration +
              " Minuten Pause ab: " +
              longBreak.toLocaleTimeString("de-DE", localTimeFormatOptions));
        }
        if (longDay) {
          result.push(
              timesConfig.breakDuration +
              " Minuten Pause ab: " +
              longDay.toLocaleTimeString("de-DE", localTimeFormatOptions));
        }
      }
      return result;
    }
  },

  mounted() {
    // get a copy of the `WorkingDay` to work on
    this.day = Object.assign(
        new WorkingDay(),
        this.$store.state.workingTime.dayToEdit
    ) as WorkingDay;
    if (this.day.dayParts.length === 0) {
      this.day.dayParts.push(new WorkingDayPart(['']));
      this.day.dayParts[0].workingDayId = this.day.id;
    }
    if (this.day.dayParts.length === 1) {
      this.partToEdit = 0;
    }
    // scroll form to top
    let target = document.getElementById("form") as HTMLElement;
    // `{ behaviour: "smooth" }` is not working!
    target.scrollIntoView();
  },

  methods: {
    editPart(index: number) {
      if (index === -1) {
        this.day.dayParts.push(new WorkingDayPart({
          'working_day_id': this.day.id,
          'id': 0,
        }));
        index = this.day.dayParts.length - 1;
      }
      this.partToEdit = index;
    },
    deletePart(index: number) {
      this.day.dayParts.splice(index, 1);
      if (this.partToEdit === index) {
        this.partToEdit = -1;
      }
      if (this.day.dayParts.length === 1) {
        this.partToEdit = 0;
      }
    },
    formatIndex(index: string) {
      return (Number(index) + 1).toString();
    },
    formatBeginEnd(value: string | null) {
      return value ? value.substring(0, 5) : '--:--';
    },
    onSubmit(evt: Event) {
      evt.preventDefault();
      console.log(this.day);
      // if (this.errors.length === 0) {
      //   this.$store
      //       .dispatch("workingTime/setDay", this.day)
      //       .then(() =>
      //           this.$store.dispatch("workingTime/getMonth", this.day.date)
      //       )
      //       .then(() => {
      //         this.$emit("submitted");
      //       });
      // }
    },

    onReset(evt: Event) {
      evt.preventDefault();
      // Reset our form values
      this.day.dayParts[this.partToEdit].begin = null;
      this.day.dayParts[this.partToEdit].end = null;
      this.day.timeOff = undefined;
      this.day.comment = undefined;
      this.day.dayParts[this.partToEdit].mobileWorking = false;
      // Trick to reset/clear native browser form validation state
      this.show = false;
      this.$nextTick(() => {
        this.show = true;
      });
    },

    onCancel() {
      this.$emit("submitted");
    },

    validate() {
      // TODO adapt validation!
      return true;
      // const dfv = new DayFormValidator(
      //     new WorkingDay(this.day),
      //     this.$store.state.workingTime.holidays,
      //     this.$store.state.workingTime.carryResult,
      //     this.$store.state.workingTime.month
      // );
      // this.errors = dfv.validate();
      // return this.errors.length === 0;
    },
  }
});
</script>

<style scoped>
form {
  width: 50%;
}

>>> label {
  text-align: left;
}

.left {
  text-align: left;
}

input {
  width: 30%;
}
</style>
