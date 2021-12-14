import { WorkingDay } from "@/models";

export default class WorkingMonth {
  monthDate: Date;
  days: Array<WorkingDay>;

  /**
   * Constructs a new `WorkingMonth` for the given date and merges in the given days.
   * @param monthDate the month to create a `WorkingMonth` for
   * @param days the days to merge in
   */
  constructor(monthDate: Date, days: Array<WorkingDay>) {
    this.monthDate = monthDate;
    this.days = new Array<WorkingDay>();
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
    // iterate over the days of the month to setup an array of working days
    const currentDay = firstOfMonth;
    while (currentDay <= lastOfMonth) {
      // look for current day in given `days`
      const found = days.find(
        (day) => day.date.getDate() == currentDay.getDate()
      );
      if (found) {
        // take the given day
        this.days.push(found);
      } else {
        // take fresh created (empty) day
        this.days.push(
          new WorkingDay({
            date: new Date(currentDay),
            mobile_working: false,
            afternoon: false,
          })
        );
      }
      // update current day for next loop iteration
      currentDay.setDate(currentDay.getDate() + 1);
    }
  }

  /**
   * Returns a string representation of this month
   */
  get monthName() {
    const options = {
      year: "numeric",
      month: "2-digit",
    } as const;
    return this.monthDate.toLocaleString("de-DE", options);
  }

  get monthNumber() {
    return this.monthDate.getMonth() + 1;
  }

  get takenHolidays() {
    return this.days.filter((day) => day.timeOff === "urlaub").length;
  }

  getDayByDate(date: Date) {
    return this.days.filter(
      (day) =>
        day.date.getMonth() === date.getMonth() &&
        day.date.getDate() === date.getDate()
    )[0];
  }
}
