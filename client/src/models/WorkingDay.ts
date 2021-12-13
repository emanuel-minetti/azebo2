import { Holiday, Saldo, WorkingRule } from "@/models";
import { timesConfig } from "@/configs";
import { FormatterService } from "@/services";
import { store } from "@/store";

// noinspection JSUnusedGlobalSymbols
export default class WorkingDay {
  /**
   * The time intervall to subtract for total working time if a break was taken
   */
  private static readonly BREAK_DURATION = Saldo.createFromMillis(
    timesConfig.breakDuration * 60 * 1000,
    false
  );

  private readonly _id: number;
  private readonly _date: Date;
  private readonly _rule?: WorkingRule;

  private _begin?: Date;
  private _end?: Date;
  private _timeOff?: string;
  private _comment?: string;
  private _mobileWorking: boolean;
  private _afternoon: boolean;
  private _afternoonBegin?: Date;
  private _afternoonEnd?: Date;

  private _holiday?: Holiday;
  private _edited: boolean;

  constructor(data?: any) {
    if (
      data &&
      data.date &&
      data.mobile_working != undefined &&
      data.afternoon != undefined
    ) {
      this._id = data.id ? data.id : 0;

      this._date = FormatterService.convertToDate(data.date);

      // @ts-ignore
      const holidays = store.state.workingTime.holidays;
      const year = this.date.getFullYear();
      const monthIndex = this.date.getMonth();
      const date = this.date.getDate();

      holidays.forEach((holiday: Holiday) => {
        if (
          holiday.date.getFullYear() === year &&
          holiday.date.getMonth() === monthIndex &&
          holiday.date.getDate() === date
        ) {
          this._holiday = holiday;
        }
      });

      // Find the working rule for this day if any.
      // @ts-ignore
      const rules = store.state.workingTime.rules;
      for (let i = 0; i < rules.length; i++) {
        const rule: WorkingRule = rules[i];
        if (
          // If this rule has the same weekday and ...
          rule.weekday == this._date.getDay() &&
          // is in the right week and ...
          rule.isCalendarWeek(this.calendarWeek) &&
          // is valid and ...
          rule.validFrom.valueOf() <= this._date.valueOf() &&
          (!rule.validTo || rule.validTo.valueOf() > this._date.valueOf()) &&
          // is not a holiday, ...
          !this.isHoliday
        ) {
          // then *the* rule is found
          this._rule = rule;
          break;
        }
      }

      this._mobileWorking = Boolean(data.mobile_working);
      this._afternoon = Boolean(data.afternoon);

      this._begin = FormatterService.convertToTime(
        year,
        monthIndex,
        date,
        data.begin
      );
      this._end = FormatterService.convertToTime(
        year,
        monthIndex,
        date,
        data.end
      );

      this._timeOff = data.time_off;
      this._comment = data.comment;
      this._afternoonBegin = FormatterService.convertToTime(
        year,
        monthIndex,
        date,
        data.afternoon_begin
      );
      this._afternoonEnd = FormatterService.convertToTime(
        year,
        monthIndex,
        date,
        data.afternoon_end
      );
    } else {
      this._id = 0;
      this._date = new Date();
      this._mobileWorking = false;
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
    } else {
      this._begin = undefined;
    }
    this._edited = true;
  }

  get end(): Date | undefined {
    return this._end;
  }

  set end(value: Date | undefined) {
    if (value) {
      this._end = value;
    } else {
      this._end = undefined;
    }
    this._edited = true;
  }

  get timeOff(): string | undefined {
    return this._timeOff;
  }

  set timeOff(value: string | undefined) {
    if (value) {
      this._timeOff = value;
    } else {
      this._timeOff = undefined;
    }
    this._edited = true;
  }

  get comment(): string | undefined {
    return this._comment;
  }

  set comment(value: string | undefined) {
    if (value) {
      this._comment = value;
    } else {
      this._comment = undefined;
    }
    this._edited = true;
  }

  get mobileWorking(): boolean {
    return this._mobileWorking;
  }

  set mobileWorking(value: boolean) {
    this._mobileWorking = value;
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

  get isHoliday(): boolean {
    return this._holiday !== undefined;
  }

  get holidayName(): string {
    return this._holiday && this._holiday.name ? this._holiday.name : "";
  }

  get edited(): boolean {
    return this._edited;
  }

  /**
   * Returns whether a date is an actual working day.
   */
  get isWorkingDay() {
    return (
      this._date.getDay() !== 0 && this._date.getDay() !== 6 && !this.isHoliday
    );
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

  // TODO adjust!
  /**
   * Returns the total working time minus possible break times.
   */
  get actualTime(): Saldo | undefined {
    if (!this.hasWorkingTime) return undefined;
    return this.mobileWorking
      ? Saldo.getSum(<Saldo>this.totalTime, WorkingDay.BREAK_DURATION)
      : this.totalTime;
  }

  get targetTime(): Saldo | undefined {
    return this._rule ? this._rule.target : undefined;
  }

  get saldoTime(): Saldo | undefined {
    if (this.hasWorkingTime) {
      if (this._rule) {
        const targetSaldo = this.targetTime!.clone();
        targetSaldo.invert();
        return Saldo.getSum(this.actualTime!, targetSaldo);
      } else {
        return this.actualTime;
      }
    }
    return undefined;
  }

  get id(): number {
    return this._id;
  }

  /**
   * Returns the number week in the year for this day.
   */
  private get calendarWeek(): number {
    const d = new Date(
      Date.UTC(
        this._date.getFullYear(),
        this._date.getMonth(),
        this._date.getDate()
      )
    );
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil(((d.valueOf() - yearStart.valueOf()) / 86400000 + 1) / 7);
  }

  public toJSON() {
    return {
      _id: this.id,
      _afternoon: this.afternoon,
      _begin: this.begin,
      _end: this.end,
      _timeOff: this.timeOff,
      _comment: this.comment,
      _mobile_working: this.mobileWorking,
      _afternoonBegin: this.afternoonBegin,
      _afternoonEnd: this.afternoonEnd,
      _edited: this.edited,
    };
  }
}
