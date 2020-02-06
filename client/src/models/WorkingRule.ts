import { FormatterService } from "@/services";
import { Saldo } from "@/models/index";

export default class WorkingRule {
  private readonly _id: number;
  private _weekday: number;
  private _calendarWeek: string;
  private _flexTimeBegin?: Date;
  private _flexTimeEnd?: Date;
  private _coreTimeBegin?: Date;
  private _coreTimeEnd?: Date;
  private _target: Saldo;
  private _validFrom: Date;
  private _validTo?: Date;

  constructor(data?: any) {
    if (data) {
      this._id = data.id;
      this._weekday = data.weekday;
      this._calendarWeek = data.calendar_week;
      this._flexTimeBegin = FormatterService.convertToTime(
        1979,
        0,
        1,
        data.flex_time_begin
      );
      this._flexTimeEnd = FormatterService.convertToTime(
        1979,
        0,
        1,
        data.flex_time_end
      );
      this._coreTimeBegin = FormatterService.convertToTime(
        1979,
        0,
        1,
        data.core_time_begin
      );
      this._coreTimeEnd = FormatterService.convertToTime(
        1979,
        0,
        1,
        data.core_time_end
      );
      const targetDate = FormatterService.convertToTime(
        1979,
        0,
        1,
        data.target
      )!;
      const hours = targetDate.getHours();
      const minutes = targetDate.getMinutes();
      const millis = (hours * 3600 + minutes * 60) * 1000;
      this._target = Saldo.createFromMillis(millis);
      this._validFrom = FormatterService.convertToDate(data.valid_from);
      this._validTo = data.valid_to
        ? FormatterService.convertToDate(data.valid_to)
        : undefined;
    } else {
      this._id = 0;
      this._weekday = 0;
      this._calendarWeek = "all";
      this._target = Saldo.createFromMillis(0);
      this._validFrom = new Date();
    }
  }

  get validTo(): Date | undefined {
    return this._validTo;
  }

  get validFrom(): Date {
    return this._validFrom;
  }

  get weekday(): number {
    return this._weekday;
  }

  get target(): Saldo {
    return this._target;
  }

  isCalendarWeek(cw: number): boolean {
    if (this._calendarWeek === "all") {
      return true;
    }
    if (this._calendarWeek === "even" && cw % 2 === 0) {
      return true;
    }
    return this._calendarWeek == "odd" && cw % 2 === 1;
  }
}
