import { FormatterService } from "@/services";

export default class Holiday {
  date: Date;
  name: string;

  constructor(data?: any) {
    if (data && data.date && data.name) {
      this.date = FormatterService.convertToDate(data.date);
      this.name = data.name;
    } else {
      this.date = new Date();
      this.name = "";
    }
  }
}
