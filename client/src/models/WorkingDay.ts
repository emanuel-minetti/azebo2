export default class WorkingDay {
  private readonly _date: Date;
  private _begin?: Date;
  private _end?: Date;
  private _timeOff?: string;
  private _comment?: string;
  private _break: boolean;
  private _afternoon: boolean;
  private _afternoonBegin?: Date;
  private _afternoonEnd?: Date;

  private _edited: boolean;

  constructor(data?: any) {
    if (
      data &&
      data.date &&
      data.break != undefined &&
      data.afternoon != undefined
    ) {
      // noinspection SuspiciousTypeOfGuard
      if (!(data.date instanceof Date)) {
        this._date = WorkingDay.dateStringToDate(data.date);
      } else {
        this._date = data.date;
      }

      this._break = data.break;
      this._afternoon = data.afternoon;

      // noinspection SuspiciousTypeOfGuard
      if (data.begin && !(data.begin instanceof Date)) {
        this._begin = this.timeStringToDate(data.begin);
      } else {
        this._begin = data.begin;
      }

      if (data.end && !(data.end instanceof Date)) {
        this._end = this.timeStringToDate(data.end);
      } else {
        this._end = data.end;
      }

      this._timeOff = data.time_off;
      this._comment = data.comment;
      this._afternoonBegin = data.afternoon_begin;
      this._afternoonEnd = data.afternoon_end;
    } else {
      this._date = new Date();
      this._break = false;
      this._afternoon = false;
    }
    this._edited = false;
  }

  get date(): Date {
    return this._date;
  }

  // no setter for `date`

  get begin(): Date | undefined {
    return this._begin;
  }

  set begin(value: Date | undefined) {
    if (value) {
      this._begin = value;
      this._edited = true;
    }
  }

  get end(): Date | undefined {
    return this._end;
  }

  set end(value: Date | undefined) {
    if (value) {
      this._end = value;
      this._edited = true;
    }
  }

  get timeOff(): string | undefined {
    return this._timeOff;
  }

  set timeOff(value: string | undefined) {
    if (value) {
      this._timeOff = value;
      this._edited = true;
    }
  }

  get comment(): string | undefined {
    return this._comment;
  }

  set comment(value: string | undefined) {
    if (value) {
      this._comment = value;
      this._edited = true;
    }
  }

  get break(): boolean {
    return this._break;
  }

  set break(value: boolean) {
    this._break = value;
    this._edited = true;
  }

  get afternoon(): boolean {
    return this._afternoon;
  }

  set afternoon(value: boolean) {
    this._afternoon = value;
    this._edited = true;
  }

  get afternoonBegin(): Date | undefined {
    return this._afternoonBegin;
  }

  set afternoonBegin(value: Date | undefined) {
    if (value) {
      this._afternoonBegin = value;
      this._edited = true;
    }
  }

  get afternoonEnd(): Date | undefined {
    return this._afternoonEnd;
  }

  set afternoonEnd(value: Date | undefined) {
    if (value) {
      this._afternoonEnd = value;
      this._edited = true;
    }
  }

  get edited(): boolean {
    return this._edited;
  }

  private static dateStringToDate(dateString: string): Date {
    const year = Number(dateString.substring(0, 4));
    const month = Number(dateString.substring(5, 7));
    const day = Number(dateString.substring(8, 10));
    return new Date(year, month - 1, day);
  }

  private timeStringToDate(timeString: string): Date {
    const year = this.date.getFullYear();
    const month = this.date.getMonth();
    const day = this.date.getDay();
    const hour = Number(timeString.substring(0, 2));
    const minute = Number(timeString.substring(3, 5));
    return new Date(year, month, day, hour, minute);
  }

  get isWorkingDay() {
    return this._date.getDay() == 0 || this._date.getDay() == 6;
  }
}
