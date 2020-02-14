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
    let currentDay = firstOfMonth;
    while (currentDay <= lastOfMonth) {
      // look for current day in given `days`
      let found = days.find(day => day.date.getDate() == currentDay.getDate());
      if (found) {
        // take the given day
        this.days.push(found);
      } else {
        // take fresh created (empty) day
        this.days.push(
          new WorkingDay({
            date: new Date(currentDay),
            break: false,
            afternoon: false
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
      month: "long"
    };
    return this.monthDate.toLocaleString("de-DE", options);
  }

  get monthNumber() {
    return this.monthDate.getMonth() + 1;
  }
}
