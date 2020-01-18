export default class WorkingDay {
  private readonly _date: Date;
  private _begin?: Date;
  private _end?: Date;
  private _timeOff?: string;
  private _comment?: string;
  private _break: boolean;
  private _afternoon: boolean;
  private _afternoonBeginn?: Date;
  private _afternoonEnd?: Date;

  private _edited: boolean;

  constructor(data?: any) {
    //TODO `afternoon_begin``isn't shown correctly!
    if (
      data &&
      data.date &&
      data.break != undefined &&
      data.afternoon != undefined
    ) {
      // noinspection SuspiciousTypeOfGuard
      if (!(data.date instanceof Date)) {
        const year = data.date.substring(0, 4);
        const month = data.date.substring(5, 7);
        const day = data.date.substring(8, 10);
        this._date = new Date(Number(year), Number(month) - 1, Number(day));
      } else {
        this._date = data.date;
      }
      this._break = data.break;
      this._afternoon = data.afternoon;
      this._begin = data.begin;
      this._end = data.end;
      this._timeOff = data.time_off;
      this._comment = data.comment;
      this._afternoonBeginn = data.afternoon_beginn;
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

  get afternoonBeginn(): Date | undefined {
    return this._afternoonBeginn;
  }

  set afternoonBeginn(value: Date | undefined) {
    if (value) {
      this._afternoonBeginn = value;
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

  // no setter for `date`
}
