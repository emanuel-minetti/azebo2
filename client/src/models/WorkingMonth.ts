import { WorkingDay, ServerWorkingMonth } from "/src/models";
import { FormatterService } from "/src/services";

export default class WorkingMonth {
  private readonly _monthDate: Date;
  private readonly _days: Array<WorkingDay>;
  private readonly _closed: boolean;
  private readonly _finalized: boolean;


  /**
   * Constructs a new `WorkingMonth` for the given date and merges in the given days.
   * @param serverMonth the month from the api, if any
   * @param days the days to merge in
   */
  constructor(serverMonth: ServerWorkingMonth | {'month': string}, days: Array<WorkingDay>) {
    const monthDate = FormatterService.convertToDate(serverMonth.month);
    this._monthDate = monthDate;
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
    this._closed = 'id' in serverMonth;
    this._finalized =
      this._closed && (serverMonth as ServerWorkingMonth).finalized;
  }

  get days(): Array<WorkingDay> {
    return this._days;
  }

  getDayByDate(date: Date) {
    return this._days.filter(
      (day) =>
        day.date.getMonth() === date.getMonth() &&
        day.date.getDate() === date.getDate()
    )[0];
  }

  get monthDate(): Date {
    return this._monthDate;
  }
}
