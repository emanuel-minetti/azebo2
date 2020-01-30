import { FormatterService } from "@/services";

export default class Holiday {
  date: Date;
  name: string;

  constructor(data?: any) {
    if (data && data.date && data.name) {
      //TODO Make sure that `this.date` is a `Date` (maybe on server side)
      this.date = FormatterService.convertToDate(data.date);
      this.name = data.name;
    } else {
      this.date = new Date();
      this.name = "";
    }
  }
}
