import { Holiday, WorkingDay } from "@/models";
import { timeOffsConfig } from "@/configs";

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
        `Bei Verwendung der Bemerkung "${
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
            'Bemerkung "überlanger Arbeitstag" erhalten',
        ];
      }
    } else {
      if (this.day.timeOff === "lang") {
        return [
          'Die Bemerkung "überlanger Arbeitstag" kann nur vergeben werden,' +
            ' falls "Anfang" und "Ende" gesetzt sind und die Ist-Zeit mehr ' +
            "als zehn Stunden beträgt",
        ];
      }
    }
    return [];
  }

  inCoreTime(holidays: Holiday[]): string[] {
    const errors = [];
    if (this.day.isBeginAfterCore() && this.day.timeOff !== "ausgleich") {
      errors.push(
        "Der Beginn der Arbeitszeit darf nicht nach dem Beginn der" +
          ' Kernarbeitszeit liegen, oder es muss die Bemerkung "Zeitausgleich"' +
          " angegeben werden."
      );
    }
    if (
      this.day.isEndAfterCore(holidays) &&
      !(
        this.day.timeOff === "ausgleich" ||
        this.day.timeOff === "da_krank" ||
        this.day.timeOff === "da_befr"
      )
    ) {
      errors.push(
        "Das Ende der Arbeitszeit darf nicht vor dem Ende der" +
          ' Kernarbeitszeit liegen, oder es muss die Bemerkung "Zeitausgleich"' +
          ' bzw. "Dienstabbruch" angegeben werden.'
      );
    }
    if (
      this.day.timeOff === "ausgleich" &&
      !(this.day.isBeginAfterCore() || this.day.isEndAfterCore(holidays))
    ) {
      errors.push(
        'Die Bemerkung "Zeitausgleich" ist nur zulässig, falls Arbeitsbeginn' +
          " oder -ende außerhalb der Kernarbeitszeit liegt."
      );
    }
    if (
      (this.day.timeOff === "da_krank" || this.day.timeOff === "da_befr") &&
      !this.day.isEndAfterCore(holidays)
    ) {
      errors.push(
        'Die Bemerkungen "Dienstabbruch (krank)" und' +
          '"Dienstabbruch (Dienstbefr.)" sind nur zulässig, falls das' +
          " Arbeitsende außerhalb der Kernarbeitszeit liegt."
      );
    }
    return errors;
  }
}
