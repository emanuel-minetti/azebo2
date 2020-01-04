export default class WorkingDay {
  date: Date;
  begin?: Date;
  end?: Date;
  timeOff?: string;
  comment?: string;
  break: boolean;
  afternoon: boolean;
  afternoonBeginn?: Date;
  afternoonEnd?: Date;

  constructor(data?: any) {
    if (data && data.date && data.break && data.afternoon) {
      this.date = new Date(data.date);
      this.break = data.break;
      this.afternoon = data.afternoon;
      this.begin = data.begin;
      this.end = data.ent;
      this.timeOff = data.time_off;
      this.comment = data.comment;
      this.afternoonBeginn = data.afternoon_beginn;
      this.afternoonEnd = data.afternoon_end;
    } else {
      this.date = new Date();
      this.break = false;
      this.afternoon = false;
    }
  }
}