import { Holiday, Saldo } from "@/models";
import { timesConfig } from "@/configs";
import { FormatterService } from "@/services";
import { store } from "@/store";

export default class WorkingDay {
  /**
   * The time intervall to subtract for total working time if a break was taken
   */
  private static readonly BREAK_DURATION = Saldo.createFromMillis(
    timesConfig.breakDuration,
    false
  );

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
      this._date = FormatterService.convertToDate(data.date);
      // TODO set holiday
      const holidays = store.state.workingTime.holidays;
      console.log(holidays[0]);
      // holidays.forEach((holiday: Holiday) => {
      //     if (holiday.date.getFullYear() === this._date.getFullYear() && holiday.date.getMonth() === this._date.getMonth() && holiday.date.getDate() === this._date.getDate()) {
      //       console.log("Treffer");
      //     }
      // });

      this._break = Boolean(data.break);
      this._afternoon = Boolean(data.afternoon);

      const year = this.date.getFullYear();
      const monthIndex = this.date.getMonth();
      const day = this.date.getDay();
      this._begin = FormatterService.convertToTime(
        year,
        monthIndex,
        day,
        data.begin
      );
      this._end = FormatterService.convertToTime(
        year,
        monthIndex,
        day,
        data.end
      );

      this._timeOff = data.time_off;
      this._comment = data.comment;
      // this._afternoonBegin = this.convertTime(data.afternoon_begin);
      // this._afternoonEnd = this.convertTime(data.afternoon_end);
      this._afternoonBegin = FormatterService.convertToTime(
        year,
        monthIndex,
        day,
        data.afternoon_begin
      );
      this._afternoonEnd = FormatterService.convertToTime(
        year,
        monthIndex,
        day,
        data.afternoon_end
      );
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

  // no setter for `date` because it's the primary key and should not be edited

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

  /**
   * Returns whether a date is a actual working day.
   */
  get isWorkingDay() {
    return this._date.getDay() == 0 || this._date.getDay() == 6;
  }

  /**
   * Returns whether begin and end are set for this working day.
   */
  get hasWorkingTime(): boolean {
    return this.begin !== undefined && this.end !== undefined;
  }

  /**
   * Returns the time intervall from begin to end if these are set, `undefined` otherwise.
   */
  get totalTime(): Saldo | undefined {
    if (!this.hasWorkingTime) return undefined;
    return Saldo.createFromDates(<Date>this.begin, <Date>this.end);
  }

  /**
   * Returns the total working time minus possible break times.
   */
  get actualTime(): Saldo | undefined {
    if (!this.hasWorkingTime) return undefined;
    return this.break
      ? Saldo.getSum(<Saldo>this.totalTime, WorkingDay.BREAK_DURATION)
      : this.totalTime;
  }
}
