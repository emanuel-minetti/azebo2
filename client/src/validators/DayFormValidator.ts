import { WorkingDay } from "@/models";

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
}
