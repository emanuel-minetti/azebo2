import { FormatterService } from "/src/services";
import { Saldo } from "/src/models/index";

export default class WorkingRule {
  private readonly _id: number;
  private _validFrom: Date;
  private _validTo?: Date;
  private percentage: number;
  private weekdays: number[];

  private readonly targetMillis: number;

  constructor(data?: any) {
    if (data) {
      this._id = data.id;
      this._validFrom = FormatterService.convertToDate(data.valid_from);
      this._validTo = data.valid_to
        ? FormatterService.convertToDate(data.valid_to)
        : undefined;
      this.percentage = data.percentage;
      this.weekdays = data.weekdays;
      this.targetMillis = data.target;
    } else {
      this._id = 0;
      this._validFrom = new Date();
      this.percentage = 100;
      this.weekdays = [1, 2, 3, 4, 5,];
      this.targetMillis = 0;
    }
  }

  get validTo(): Date | undefined {
    return this._validTo;
  }

  get validFrom(): Date {
    return this._validFrom;
  }

  isWeekday(weekday: number): boolean {
    return this.weekdays.findIndex(day => day === weekday) !== -1;
  }

  get target(): Saldo {
    return new Saldo(this.targetMillis);
  }
}
