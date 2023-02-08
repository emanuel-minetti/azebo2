import { WorkingDay, ServerWorkingMonth } from "/src/models";
import { FormatterService } from "/src/services";

export default class WorkingMonth {
  monthDate: Date;
  private readonly _days: Array<WorkingDay>;

  /**
   * Constructs a new `WorkingMonth` for the given date and merges in the given days.
   * @param serverMonth the month from the api, if any
   * @param days the days to merge in
   */
  constructor(serverMonth: ServerWorkingMonth | {'month': string}, days: Array<WorkingDay>) {
    console.log(serverMonth);
    const monthDate = FormatterService.convertToDate(serverMonth.month);
    this.monthDate = monthDate;
    this._days = new Array<WorkingDay>();
    // get first and last day of this month
    const firstOfMonth = new Date(
      monthDate.getFullYear(),
      monthDate.getMonth(),
      1
    );
    const lastOfMonth = new Date(
      monthDate.getFullYear(),
      monthDate.getMonth() + 1,
      0
    );
    // iterate over the days of the month to set up an array of working days
    const currentDay = firstOfMonth;
    while (currentDay <= lastOfMonth) {
      // look for current day in given `days`
      const found = days.find(
        (day) => day.date.getDate() == currentDay.getDate()
      );
      if (found) {
        // take the given day
        this._days.push(found);
      } else {
        // take fresh created (empty) day
        this._days.push(
          new WorkingDay({
            date: new Date(currentDay),
            day_parts: [],
            edited: false,
          })
        );
      }
      // update current day for next loop iteration
      currentDay.setDate(currentDay.getDate() + 1);
    }
  }

  get days(): Array<WorkingDay> {
    return this._days;
  }

  // /**
  //  * Returns a string representation of this month
  //  */
  // get monthName() {
  //   const options = {
  //     year: "numeric",
  //     month: "2-digit",
  //   } as const;
  //   return this.monthDate.toLocaleString("de-DE", options);
  // }
  //
  // get monthNumber() {
  //   return this.monthDate.getMonth() + 1;
  // }

  getDayByDate(date: Date) {
    return this._days.filter(
      (day) =>
        day.date.getMonth() === date.getMonth() &&
        day.date.getDate() === date.getDate()
    )[0];
  }
}
