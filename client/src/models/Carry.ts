import { Saldo } from "/src/models/index";

export default class Carry {
  private readonly _id: number;
  private readonly _user_id: number;
  private readonly _year: Date;
  private _saldo: Saldo;
  private _holidaysPrevious: number;
  private _holidays: number;
  private readonly _finalized: boolean;
  private readonly _missing: string[];

  constructor(data?: any) {
    if (data) {
      this._id = data.id;
      this._user_id = data.user_id;
      this._year = new Date(data.year);
      const millis = (data.saldo_hours * 3600 + data.saldo_minutes * 60) * 1000;
      this._saldo = Saldo.createFromMillis(millis, data.saldo_positive);
      this._holidaysPrevious = data.holidays_previous_year;
      this._holidays = data.holidays;
      this._finalized = data.finalized;
      this._missing = data.missing;
    } else {
      this._id = 0;
      this._user_id = 0;
      this._year = new Date();
      this._saldo = Saldo.createFromMillis(0);
      this._holidaysPrevious = 0;
      this._holidays = 0;
      this._finalized = false;
      this._missing = [];
    }
  }

  get saldo(): Saldo {
    return this._saldo;
  }

  get holidaysPrevious(): number {
    return this._holidaysPrevious;
  }

  get holidays(): number {
    return this._holidays;
  }

  get finalized(): boolean {
    return this._finalized;
  }

  set holidays(value: number) {
    this._holidays = value;
  }

  set holidaysPrevious(value: number) {
    this._holidaysPrevious = value;
  }

  set saldo(value: Saldo) {
    this._saldo = value;
  }

  get id() {
    return this._id;
  }

  get year() {
    return this._year;
  }

  get user_id(): number {
    return this._user_id;
  }

  get missing(): string[] {
    return this._missing;
  }

  get hasMissing(): boolean {
    return this._missing.length !== 0;
  }
}
