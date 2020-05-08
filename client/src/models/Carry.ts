import { Saldo } from "@/models/index";

export default class Carry {
  get user_id(): number {
    return this._user_id;
  }
  private readonly _id: number;
  private readonly _user_id: number;
  private readonly _year: Date;
  private _saldo: Saldo;
  private _holidaysPrevious: number;
  private _holidays: number;
  private readonly _finalized: boolean;

  constructor(data?: any) {
    if (data) {
      this._id = data.id;
      this._user_id = data.user_id;
      this._year = new Date(data.year);
      let millis = (data.saldo_hours * 3600 + data.saldo_minutes * 60) * 1000;
      this._saldo = Saldo.createFromMillis(millis, data.saldo_positive);
      this._holidaysPrevious = data.holidays_previous_year;
      this._holidays = data.holidays;
      this._finalized = data.finalized;
    } else {
      this._id = 0;
      this._user_id = 0;
      this._year = new Date();
      this._saldo = Saldo.createFromMillis(0);
      this._holidaysPrevious = 0;
      this._holidays = 0;
      this._finalized = false;
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
}
