import { WorkingDay, ServerWorkingMonth } from "/src/models";
import { FormatterService } from "/src/services";
import Saldo from "/src/models/Saldo";

export default class WorkingMonth {
  private readonly _monthDate: Date;
  private readonly _days: Array<WorkingDay>;
  private _closed: boolean;
  private _finalized: boolean;
  private _saldo: Saldo | null;


  /**
   * Constructs a new `WorkingMonth` for the given date and merges in the given days.
   * @param serverMonth the month from the api, if any
   * @param days the days to merge in
   */
  constructor(serverMonth: ServerWorkingMonth | {month: string} | null, days: Array<WorkingDay>) {
    const monthDate = serverMonth
      ? FormatterService.convertToDate(serverMonth.month)
      : new Date();
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
    this._closed = serverMonth !== null && 'id' in serverMonth;
    this._finalized = serverMonth !== null
      && 'id' in serverMonth && serverMonth.finalized;
    this._saldo = serverMonth && 'id' in serverMonth
      ? WorkingMonth.createSaldo(
        serverMonth.saldo_hours,
        serverMonth.saldo_minutes,
        serverMonth.saldo_positive
      )
      : null;
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

  get closed(): boolean {
    return this._closed;
  }

  get finalized(): boolean {
    return this._finalized;
  }

  get year(): string {
    return '' + this._monthDate.getFullYear();
  }

  get month(): string {
    return '' + (this._monthDate.getMonth() + 1);
  }

  get saldo(): Saldo | null {
    return this._saldo;
  }
  set serverMonth(month: ServerWorkingMonth) {
    this._closed = true;
    this._finalized = month.finalized;
    this._saldo = WorkingMonth.createSaldo(
      month.saldo_hours, month.saldo_minutes, month.saldo_positive
    );
  }
  private static createSaldo(hours:number, minutes:number, positive:boolean) {
    const millis = (hours * 60 + minutes) * 60 * 1000;
    return Saldo.createFromMillis(millis, positive);
  }
}
