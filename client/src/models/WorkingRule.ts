import { FormatterService } from "/src/services";
import { Saldo } from "/src/models/index";

export default class WorkingRule {
  private readonly _id: number;
  private _validFrom?: Date;
  private _validTo?: Date;
  private _percentage: number;
  private _isOfficer: boolean
  private _weekdays: number[];
  private _targetMillis: number;
  private _isNew: boolean;

  constructor(data?: any) {
    if (data) {
      this._id = data.id;
      this._validFrom = FormatterService.convertToDate(data.valid_from);
      this._validTo = data.valid_to
        ? FormatterService.convertToDate(data.valid_to)
        : undefined;
      this._percentage = data.percentage;
      this._isOfficer = data.is_officer;
      this._weekdays = data.weekdays;
      this._targetMillis = data.target;
      this._isNew = false;
    } else {
      this._id = 0;
      this._percentage = 100;
      this._isOfficer = false
      this._weekdays = [1, 2, 3, 4, 5,];
      this._targetMillis = 0;
      this._isNew = true;
    }
  }

  get validTo(): Date | null {
    if (typeof this._validTo === "undefined")
      return null;
    return this._validTo;
  }

  set validTo(value: Date | null) {
    if (value) {
      this._validTo = value;
    } else {
      this._validTo = undefined;
    }
  }

  get percentage(): number {
    return this._percentage;
  }

  set percentage(value: number) {
    this._percentage = value;
  }


  get isOfficer(): boolean {
    return this._isOfficer;
  }

  set isOfficer(value: boolean) {
    this._isOfficer = value;
  }

  get weekdays(): number[] {
    return this._weekdays;
  }

  set weekdays(value: number[]) {
    if (value.length === 0) {
      value = [1, 2, 3, 4, 5];
    }
    this._weekdays = value;
  }

  get validFrom(): Date | null {
    if (typeof this._validFrom === "undefined")
      return null;
    return this._validFrom;
  }

  set validFrom(value: Date | null) {
    if (value) {
      this._validFrom = value;
    } else {
      this._validFrom = undefined;
    }
    this._isNew = false;
  }

  isWeekday(weekday: number): boolean {
    return this._weekdays.findIndex(day => day === weekday) !== -1;
  }

  get target(): Saldo {
    return new Saldo(this._targetMillis);
  }

  get hasWeekdays(): boolean {
    return this._weekdays.length !== 5;
  }
  get isNew(): boolean {
    return this._isNew;
  }
}
