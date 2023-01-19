import { Holiday, Saldo, WorkingRule } from "/src/models";
import { FormatterService, GermanKwService } from "/src/services";
import { store } from "/src/store";
import { timesConfig } from "/src/configs";
import WorkingDayPart from "/src/models/WorkingDayPart";

// noinspection JSUnusedGlobalSymbols
export default class WorkingDay {
  private readonly _id: number;
  private readonly _date: Date;
  private readonly _rule?: WorkingRule;
  private _timeOff?: string;
  private _comment?: string;
  private readonly _dayParts: Array<WorkingDayPart> = [];
  private _holiday?: Holiday;
  private _edited: boolean;

  constructor(data?: any) {
    if (data && data.date) {
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

      data.day_parts.forEach((dayPart: any) => {
        this._dayParts.push(new WorkingDayPart(dayPart));
      });

      this._timeOff = data.time_off;
      this._comment = data.comment;
    } else {
      this._id = 0;
      this._date = new Date();
    }
    this._edited = false;
  }

  get date(): Date {
    return this._date;
  }

  // no setter for `date` because it's the primary key and should not be edited

  get begin(): Date | undefined {
    //TODO adapt

    // return this._begin;
    return undefined;
  }

  set begin(value: Date | undefined) {
    //TODO adapt

    // if (value) {
    //   this._begin = value;
    //   this._begin.setUTCSeconds(0);
    // } else {
    //   this._begin = undefined;
    // }
    this._edited = true;
  }

  get end(): Date | undefined {
    //TODO adapt

    //return this._end;
    return undefined;
  }

  set end(value: Date | undefined) {
    //TODO adapt

    // if (value) {
    //   this._end = value;
    //   this._end.setUTCSeconds(0);
    // } else {
    //   this._end = undefined;
    // }
    this._edited = true;
  }

  get break(): Date | undefined {
    //TODO implement `get break()`
    return undefined;
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
      _timeOff: this.timeOff,
      _comment: this.comment,
      _day_parts: this._dayParts,
      _edited: this.edited,
    };
  }

  shortBreakFrom() {
    return new Date(this.begin!.valueOf()
      + timesConfig.breakRequiredFrom * 60 * 60 * 1000
      + 60 * 1000);
  }

  longBreakFrom() {
    return new Date(this.begin!.valueOf()
      + timesConfig.longBreakRequiredFrom * 60 * 60 * 1000
      + 60 * 1000);
  }

  longDayFrom() {
    return new Date(this.begin!.valueOf()
      + timesConfig.longDayFrom * 60 * 60 * 1000
      + timesConfig.longBreakDuration * 60 * 1000);
  }

  public get mobileWorking(): boolean {
    //TODO adapt!
    return false;
  }


  get dayParts(): Array<WorkingDayPart> {
    return this._dayParts;
  }
}
