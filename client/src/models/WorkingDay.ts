import { Holiday, Saldo, WorkingRule } from "/src/models";
import { FormatterService, GermanKwService } from "/src/services";
import { store } from "/src/store";
import { timesConfig } from "/src/configs";

// noinspection JSUnusedGlobalSymbols
export default class WorkingDay {
  private readonly _id: number;
  private readonly _date: Date;
  private readonly _rule?: WorkingRule;
  private readonly _break?: Date;

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
          rule.isWeekday(this._date.getDay()) &&
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
      this._break = FormatterService.convertToTime(
        year,
        monthIndex,
        date,
        data.break
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
      this._begin.setUTCSeconds(0);
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
      this._end.setUTCSeconds(0);
    } else {
      this._end = undefined;
    }
    this._edited = true;
  }

  get break(): Date | undefined {
    if (!this._edited) return this._break;
    if (!this.hasWorkingTime) return undefined;
    const breakRequiredFrom = new Saldo(
      timesConfig.breakRequiredFrom * 60 * 1000
    );
    const longBreakRequiredFrom = new Saldo(
      timesConfig.longBreakRequiredFrom * 60 * 1000
    );
    if (this.totalTime!.biggerThan(longBreakRequiredFrom))
      return new Date(timesConfig.longBreakDuration * 60 * 1000);
    else if (this.totalTime!.biggerThan(breakRequiredFrom))
      return new Date(timesConfig.breakRequiredFrom * 60 * 1000);
    else return undefined;
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
   * Returns whether a date is a common working day.
   */
  get isCommonWorkingDay() {
    return (
      this._date.getDay() !== 0 && this._date.getDay() !== 6 && !this.isHoliday
    );
  }

  /**
   * Returns whether a date is an actual working day meaning this day
   * is a working day for this user.
   */
  get isActualWorkingDay() {
    return (
      this._date.getDay() !== 0 &&
      this._date.getDay() !== 6 &&
      !this.isHoliday &&
      this.hasRule
    );
  }

  /**
   * Returns whether begin and end are set for this working day.
   */
  get hasWorkingTime(): boolean {
    return this.begin !== undefined && this.end !== undefined;
  }

  /**
   * Returns the time intervall from begin to end if these are set,
   * `undefined` otherwise.
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
      ? Saldo.getSum(
          <Saldo>this.totalTime,
          Saldo.createFromMillis(this.break.getMinutes() * 60 * 1000, false)
        )
      : this.totalTime;
  }

  /**
   * Returns the target time for this day.
   */
  get targetTime(): Saldo | undefined {
    if (this._rule && this._timeOff == "gruenhalb") {
      return Saldo.createFromMillis(
        Math.floor(this._rule.target.getMillis() / 2)
      );
    } else if (
      this._rule &&
      !(
        this._timeOff == "urlaub" ||
        this._timeOff == "gleitzeit" ||
        this._timeOff == "azv" ||
        this._timeOff == "gruen" ||
        this._timeOff == "krank" ||
        this._timeOff == "kind" ||
        this._timeOff == "reise" ||
        this._timeOff == "befr" ||
        this._timeOff == "sonder" ||
        this._timeOff == "bildung_url" ||
        this._timeOff == "bildung"
      )
    ) {
      return this._rule.target;
    } else {
      return undefined;
    }
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
    } else {
      if (this._rule && this._timeOff == "gleitzeit") {
        const targetSaldo = this._rule.target.clone();
        targetSaldo.invert();
        return targetSaldo;
      } else {
        return undefined;
      }
    }
  }

  get id(): number {
    return this._id;
  }

  get hasRule() {
    return this._rule !== undefined;
  }

  /**
   * Returns the number week in the year for this day.
   */
  public get calendarWeek(): number {
    return GermanKwService.getGermanKW(this.date);
  }

  public isEndAfterBegin(): boolean {
    if (this.begin && this.end) {
      return (
        FormatterService.toGermanTime(this.begin) <
        FormatterService.toGermanTime(this.end)
      );
    }
    return true;
  }

  public validateTimeOffWithBeginEnd(): boolean {
    return (
      this.timeOff === undefined ||
      this.timeOff === null ||
      this.timeOff === "zusatz" ||
      this.targetTime !== undefined ||
      (this.begin === undefined && this.end === undefined)
    );
  }

  public isMoreThanTenHours(): boolean {
    if (this.actualTime !== undefined) {
      const tenHours = Saldo.createFromMillis(1000 * 60 * 60 * 10);
      return this.actualTime.biggerThan(tenHours);
    }
    return false;
  }

  isBeginAfterCore() {
    if (!this.hasWorkingTime) return false;
    return (
      FormatterService.toGermanTime(this.begin) > timesConfig.coreTimeBegin
    );
  }

  isEndAfterCore(holidays: Holiday[]) {
    if (!this.hasWorkingTime) return false;
    let coreTimeEndString = timesConfig.coreTimeEndShort;
    if (this.date.getDay() !== 5) {
      let nextDayIsHoliday = false;
      const nextDay = new Date(this.date.getTime());
      nextDay.setDate(this.date.getDate() + 1);
      for (const holiday of holidays) {
        if (holiday.date.getTime() == nextDay.getTime()) {
          nextDayIsHoliday = true;
          break;
        }
      }
      if (!nextDayIsHoliday) coreTimeEndString = timesConfig.coreTimeEnd;
    }
    return FormatterService.toGermanTime(this.end) < coreTimeEndString;
  }

  public toJSON() {
    return {
      _id: this.id,
      _date: this.date.toDateString(),
      _afternoon: this.afternoon,
      _begin: this.begin?.toTimeString(),
      _end: this.end?.toTimeString(),
      _timeOff: this.timeOff,
      _comment: this.comment,
      _mobile_working: this.mobileWorking,
      _afternoonBegin: this.afternoonBegin?.toTimeString(),
      _afternoonEnd: this.afternoonEnd?.toTimeString(),
      _edited: this.edited,
    };
  }
}
