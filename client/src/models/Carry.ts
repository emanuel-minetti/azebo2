import { Saldo } from "@/models/index";

export default class Carry {
  private _saldo: Saldo;
  private _holidaysPrevious: number;
  private _holidays: number;

  constructor(data?: any) {
    if (data) {
      let millis = (data.saldo_hours * 3600 + data.saldo_minutes * 60) * 1000;
      this._saldo = Saldo.createFromMillis(millis, data.saldo_positive);
      this._holidaysPrevious = data.holidays_previous_year;
      this._holidays = data.holidays;
    } else {
      this._saldo = Saldo.createFromMillis(0);
      this._holidaysPrevious = 0;
      this._holidays = 0;
    }
  }

  get saldo(): Saldo {
    return this._saldo;
  }
}
