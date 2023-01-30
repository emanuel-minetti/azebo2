import Saldo from "/src/models/Saldo";
import { timesConfig } from "/src/configs";

export default class WorkingDayPart {
  private readonly _id: number;
  private _workingDayId: number;
  private _begin: string | null;
  private _end: string | null;
  private _mobileWorking: boolean;

  constructor(data: any) {
    if (data) {
        this._id = data.id;
        this._workingDayId = data.working_day_id;
        this._begin = data.begin ? data.begin  : null;
        this._end = data.end ? data.end  : null;
        this._mobileWorking = data.mobile_working === 1;
    } else {
      this._id = 0;
      this._workingDayId = 0;
      this._begin = null;
      this._end = null;
      this._mobileWorking = false;
    }
  }

  public static dayPartsSorter(
    a: {begin: string | null},
    b: {begin: string | null}) {
      if (!a.begin || !b.begin) return 0;
      if (Number(a.begin.substring(0, 2)) < Number(b.begin.substring(0, 2))) {
        return -1;
      } else if (Number(a.begin.substring(0, 2)) > Number(b.begin.substring(0, 2))) {
        return 1;
      } else {
        if (Number(a.begin.substring(3, 5)) < Number(b.begin.substring(3, 5))) {
          return -1;
        } else if (Number(a.begin.substring(3, 5)) > Number(b.begin.substring(3, 5))) {
          return 1;
        } else {
          return 0;
        }
      }
  }

  set workingDayId(value: number) {
    this._workingDayId = value;
  }

  get begin(): string | null {
    return this._begin;
  }

  set begin(value: string | null) {
    this._begin = value;
  }

  get end(): string | null {
    return this._end;
  }

  set end(value: string | null) {
    this._end = value;
  }

  get mobileWorking(): boolean {
    return this._mobileWorking;
  }

  set mobileWorking(value: boolean) {
    this._mobileWorking = value;
  }

  get totalTime(): Saldo | undefined {
    if (!(this._begin && this._end)) {
      return undefined;
    } else {
      let beginDate = new Date();
      beginDate.setHours(Number(this._begin.substring(0, 2)));
      beginDate.setMinutes(Number(this._begin.substring(3, 5)));
      let endDate = new Date();
      endDate.setHours(Number(this._end.substring(0, 2)));
      endDate.setMinutes(Number(this._end.substring(3, 5)));
      return Saldo.createFromDates(beginDate, endDate);
    }
  }

  get break(): Saldo | undefined {
    if (!this.totalTime) {
      return undefined;
    }
    let result: Saldo;
    const shortBreakFrom = Saldo.createFromMillis(
      timesConfig.breakRequiredFrom * 60 * 60 * 1000 + 60 * 1000);
    const longBreakFrom = Saldo.createFromMillis(
      timesConfig.longBreakRequiredFrom * 60 * 60 * 1000 + 60 * 1000);
    if (!this.totalTime.biggerOrEqualThan(shortBreakFrom)) {
      result = Saldo.createFromMillis(0);
    } else if (!this.totalTime.biggerOrEqualThan(longBreakFrom)) {
      result =  Saldo.createFromMillis(
        timesConfig.breakDuration * 60 * 1000);
    } else {
      result = Saldo.createFromMillis(
        timesConfig.longBreakDuration * 60 * 1000);
    }
    return result.invert();
  }

  get actualTime(): Saldo | undefined {
    if (!this.totalTime) {
      return undefined;
    }
    return Saldo.getSum(this.totalTime, this.break!);
  }

  shortBreakFrom() {
    if (this.begin) {
      const beginDate = new Date();
      beginDate.setHours(Number(this.begin.substring(0, 2)));
      beginDate.setMinutes(Number(this.begin.substring(3, 5)));
      return new Date(beginDate.valueOf()
        + timesConfig.breakRequiredFrom * 60 * 60 * 1000
        + 60 * 1000);
    } else {
      return null;
    }
  }

  longBreakFrom() {
    if (this.begin) {
      const beginDate = new Date();
      beginDate.setHours(Number(this.begin.substring(0, 2)));
      beginDate.setMinutes(Number(this.begin.substring(3, 5)));
      return new Date(beginDate.valueOf()
        + timesConfig.longBreakRequiredFrom * 60 * 60 * 1000
        + 60 * 1000);
    } else {
      return null;
    }
  }

  longDayFrom() {
    if (this.begin) {
      const beginDate = new Date();
      beginDate.setHours(Number(this.begin.substring(0, 2)));
      beginDate.setMinutes(Number(this.begin.substring(3, 5)));
      return new Date(beginDate.valueOf()
        + timesConfig.longDayFrom * 60 * 60 * 1000
        + timesConfig.longBreakDuration * 60 * 1000);
    } else {
      return null;
    }
  }

  // noinspection JSUnusedGlobalSymbols
  public toJSON() {
    return {
      _id: this._id,
      _begin: this.begin ? this.begin.substring(0,5) : null,
      _end: this.end ? this.end.substring(0, 5) : null,
      _mobileWorking: this.mobileWorking
    }
  }

  isEndAfterBegin() {
    if (!this._begin || !this._end) {
      return true;
    }
    const beginHour = Number(this._begin.substring(0, 2));
    const endHour = Number(this._end.substring(0, 2));
    if (beginHour < endHour) {
      return true;
    }
    if (beginHour > endHour) {
      return false;
    }
    const beginMinute = Number(this._begin.substring(3, 5));
    const endMinute = Number(this._end.substring(3, 5));
    return beginMinute < endMinute;
  }
}