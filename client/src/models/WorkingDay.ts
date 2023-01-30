import { Holiday, Saldo, WorkingRule } from "/src/models";
import { FormatterService, GermanKwService } from "/src/services";
import { store } from "/src/store";
import WorkingDayPart from "/src/models/WorkingDayPart";

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

  get break(): Saldo | undefined {
    let result;
    if (!this.hasWorkingTime) {
      result = undefined;
    } else {
      result = this.dayParts.reduce((prev, curr) =>
          Saldo.getSum(prev, curr.break ? curr.break : prev),
        Saldo.createFromMillis(0))
    }
    return result;
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
    if (this._dayParts.length === 0) {
      return false;
    }
    let result = false;
    this._dayParts.forEach(part => {
      if (part.begin || part.end) {
        result = true;
      }
    })
    return result;
  }

  /**
   * Returns the time intervall from begin to end if these are set,
   * `undefined` otherwise.
   */
  get totalTime(): Saldo | undefined {
    if (!this.hasWorkingTime) return undefined;
    return this.dayParts.reduce((prev, curr) =>
      curr.totalTime ? Saldo.getSum(prev, curr.totalTime) : prev,
      Saldo.createFromMillis(0))
  }

  /**
   * Returns the total working time minus possible break times.
   */
  get actualTime(): Saldo | undefined {
    if (!this.hasWorkingTime) return undefined;
    return this.dayParts.reduce((prev, curr) =>
        curr.actualTime ? Saldo.getSum(prev, curr.actualTime) : prev,
      Saldo.createFromMillis(0))
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
        return Saldo.getSum(this.actualTime!, targetSaldo.invert());
      } else {
        return this.actualTime;
      }
    } else {
      if (this._rule && this._timeOff == "gleitzeit") {
        const targetSaldo = this._rule.target.clone();
        return targetSaldo.invert();
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

  // noinspection JSUnusedGlobalSymbols
  /**
   * Returns the number week in the year for this day.
   */
  public get calendarWeek(): number {
    return GermanKwService.getGermanKW(this.date);
  }

  public validateTimeOffWithBeginEnd(): boolean {
    return (
      this.timeOff === undefined ||
      this.timeOff === null ||
      this.timeOff === "zusatz" ||
      this.targetTime !== undefined ||
      !this.hasWorkingTime
    );
  }

  public isMoreThanTenHours(): boolean {
    if (this.actualTime !== undefined) {
      const tenHours = Saldo.createFromMillis(1000 * 60 * 60 * 10);
      return this.actualTime.biggerThan(tenHours);
    }
    return false;
  }

  // noinspection JSUnusedGlobalSymbols
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

  public get mobileWorking(): Saldo {
    let result = Saldo.createFromMillis(0);
    if (this._dayParts.length > 0) {
      result =
        this._dayParts.filter(part => part.mobileWorking && part.actualTime)
          .map(part => part.actualTime)
          .reduce((prev, curr) => Saldo.getSum(prev!, curr!), Saldo.createFromMillis(0))!;
    }
    return result;
  }

  get dayParts(): Array<WorkingDayPart> {
    return this._dayParts;
  }
}
