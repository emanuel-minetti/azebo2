import { WorkingDay } from "@/models";
import { timeOffsConfig } from "@/configs";

export default class DayFormValidator {
  private day: WorkingDay;
  public errors: string[] = [];

  constructor(day: WorkingDay) {
    this.day = day;
  }

  beginAfterEnd(): string[] {
    if (!this.day.validateEndAfterBegin()) {
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

  inCoreTime(): string[] {
    if (this.day.isInCoreTime()) {
    }
    return [];
  }
}
