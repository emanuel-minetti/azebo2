import { Carry, Holiday, WorkingDay, WorkingMonth } from "@/models";
import { timeOffsConfig, timesConfig } from "@/configs";

export default class DayFormValidator {
  private day: WorkingDay;
  public errors: string[] = [];

  constructor(day: WorkingDay) {
    this.day = day;
  }

  beginAfterEnd(): string[] {
    if (!this.day.isEndAfterBegin()) {
      return ["Das Ende der Arbeitszeit muss nach dem Beginn liegen!"];
    }
    return [];
  }

  timeOffWithBeginAndEnd(): string[] {
    if (!this.day.validateTimeOffWithBeginEnd()) {
      return [
        `Bei Verwendung der Bemerkung \u201E${
          timeOffsConfig.find((value) => value.value === this.day.timeOff)!.text
        }" darf kein Arbeitsbeginn und -ende angegeben werden!`,
      ];
    }
    return [];
  }

  moreThanTenHours(): string[] {
    if (this.day.isMoreThanTenHours()) {
      if (this.day.timeOff !== "lang") {
        return [
          "Arbeitstage mit mehr als zehn Stunden Arbeitszeit müssen die " +
            'Bemerkung \u201Eüberlanger Arbeitstag" erhalten',
        ];
      }
    } else {
      if (this.day.timeOff === "lang") {
        return [
          'Die Bemerkung \u201Eüberlanger Arbeitstag" kann nur vergeben werden,' +
            ' falls \u201EAnfang" und \u201EEnde" gesetzt sind und die Ist-Zeit mehr ' +
            "als zehn Stunden beträgt",
        ];
      }
    }
    return [];
  }

  inCoreTime(holidays: Holiday[]): string[] {
    const errors = [];
    if (
      this.day.isBeginAfterCore() &&
      !(this.day.timeOff === "ausgleich" || this.day.timeOff === "zusatz")
    ) {
      errors.push(
        "Der Beginn der Arbeitszeit darf nicht nach dem Beginn der" +
          ' Kernarbeitszeit liegen, oder es muss die Bemerkung \u201EZeitausgleich"' +
          " angegeben werden."
      );
    }
    if (
      this.day.isEndAfterCore(holidays) &&
      !(
        this.day.timeOff === "ausgleich" ||
        this.day.timeOff === "da_krank" ||
        this.day.timeOff === "da_befr" ||
        this.day.timeOff === "zusatz"
      )
    ) {
      errors.push(
        "Das Ende der Arbeitszeit darf nicht vor dem Ende der" +
          ' Kernarbeitszeit liegen, oder es muss die Bemerkung \u201EZeitausgleich"' +
          ' bzw. \u201EDienstabbruch" angegeben werden.'
      );
    }
    if (
      this.day.timeOff === "ausgleich" &&
      !(this.day.isBeginAfterCore() || this.day.isEndAfterCore(holidays))
    ) {
      errors.push(
        'Die Bemerkung \u201EZeitausgleich" ist nur zulässig, falls Arbeitsbeginn' +
          " oder -ende außerhalb der Kernarbeitszeit liegt."
      );
    }
    if (
      (this.day.timeOff === "da_krank" || this.day.timeOff === "da_befr") &&
      !this.day.isEndAfterCore(holidays)
    ) {
      errors.push(
        'Die Bemerkungen "Dienstabbruch (krank)" und' +
          '\u201EDienstabbruch (Dienstbefr.)" sind nur zulässig, falls das' +
          " Arbeitsende außerhalb der Kernarbeitszeit liegt."
      );
    }
    return errors;
  }

  isWorkingDay(): string[] {
    // TODO comment!
    if (
      this.day.hasWorkingTime &&
      (!this.day.isWorkingDay ||
        (this.day.isWorkingDay && !this.day.hasRule)) &&
      this.day.timeOff !== "zusatz"
    ) {
      return [
        "An diesem Tag haben Sie keinen Arbeitstag. Falls Sie trotzdem" +
          " Arbeitszeiten eintragen, müssen Sie die Bemerkung" +
          ' \u201Ezusätzlicher Arbeitstag" hinzufügen.',
      ];
    }
    if (
      this.day.isWorkingDay &&
      this.day.hasRule &&
      this.day.timeOff === "zusatz"
    ) {
      return [
        "An einem regulären Arbeitstag darf die Bemerkung" +
          ' \u201Ezusätzlicher Arbeitstag" nicht angegeben werden',
      ];
    }
    return [];
  }

  negativeRestOfTakenHolidays(carryResult: Carry, month: WorkingMonth) {
    console.log(carryResult.holidays);
    console.log(month.takenHolidays);
    const remainingHolidays =
      month.monthNumber <= timesConfig.previousHolidaysValidTo
        ? carryResult.holidaysPrevious + carryResult.holidays
        : carryResult.holidays;
    if (
      // TODO review!
      remainingHolidays <= month.takenHolidays &&
      this.day.timeOff === "urlaub"
    ) {
      return [
        'Sie können die Bemerkung \u201EUrlaub" nur eintragen,' +
          " wenn Sie noch über Urlaubstage verfügen.",
      ];
    }
    // TODO test for `isWorkingDay` and `hasRule`
    return [];
  }
}
