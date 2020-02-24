import { Holiday, Saldo, WorkingRule } from "@/models";
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

  private readonly _id: number;
  private readonly _date: Date;
  private readonly _rule?: WorkingRule;

  private _begin?: Date;
  private _end?: Date;
  private _timeOff?: string;
  private _comment?: string;
  private _break: boolean;
  private _afternoon: boolean;
  private _afternoonBegin?: Date;
  private _afternoonEnd?: Date;

  private _holiday?: Holiday;
  private _edited: boolean;

  constructor(data?: any) {
    if (
      data &&
      data.date &&
      data.break != undefined &&
      data.afternoon != undefined
    ) {
      this._id = data.id ? data.id : 0;

      this._date = FormatterService.convertToDate(data.date);

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
      const rules = store.state.workingTime.rules;
      for (let i = 0; i < rules.length; i++) {
        let rule: WorkingRule = rules[i];
        if (
          // If this rule has the same weekday and ...
          rule.weekday == this._date.getDay() &&
          // is in the right week and ..
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

      this._break = Boolean(data.break);
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
    switch (this._timeOff) {
      case "urlaub":
        return "Urlaub";
      case "AZV":
        return "AZV";
      case "krankheit":
        return "Krankheit";
      default:
        return undefined;
    }
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

  /**
   * Returns the total working time minus possible break times.
   */
  get actualTime(): Saldo | undefined {
    if (!this.hasWorkingTime) return undefined;
    return this.break
      ? Saldo.getSum(<Saldo>this.totalTime, WorkingDay.BREAK_DURATION)
      : this.totalTime;
  }

  get targetTime(): Saldo | undefined {
    return this._rule ? this._rule.target : undefined;
  }

  get saldoTime(): Saldo | undefined {
    if (this.hasWorkingTime) {
      if (this._rule) {
        let targetSaldo = this.targetTime!.clone();
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
}
