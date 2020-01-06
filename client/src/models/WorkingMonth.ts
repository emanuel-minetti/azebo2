import WorkingDay from "@/models/WorkingDay";

export default class WorkingMonth {
  monthDate: Date;
  days: Array<WorkingDay>;

  constructor(monthDate: Date, days: Array<WorkingDay>) {
    this.monthDate = monthDate;
    // TODO set up array of working days for whole month and merge in the passed `days`
    this.days = days;
  }
}
