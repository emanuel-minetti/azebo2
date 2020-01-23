import { WorkingDay } from "@/models/index";

export default class WorkingMonth {
  monthDate: Date;
  days: Array<WorkingDay>;

  constructor(monthDate: Date, days: Array<WorkingDay>) {
    this.monthDate = monthDate;
    this.days = new Array<WorkingDay>();
    // set up array of working days for whole month and merge in the passed `days`
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
    let currentDay = firstOfMonth;
    while (currentDay <= lastOfMonth) {
      let found = days.find(day => day.date.getDate() == currentDay.getDate());
      if (found) {
        this.days.push(found);
      } else {
        this.days.push(
          new WorkingDay({
            date: new Date(currentDay),
            break: false,
            afternoon: false
          })
        );
      }
      currentDay.setDate(currentDay.getDate() + 1);
    }
  }

  get monthName() {
    const options = {
      year: "numeric",
      month: "long"
    };
    return this.monthDate.toLocaleString("de-DE", options);
  }
}
