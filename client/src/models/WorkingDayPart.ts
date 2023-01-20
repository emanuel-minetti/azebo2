import Saldo from "/src/models/Saldo";

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

  get begin(): string | null {
    return this._begin;
  }

  get end(): string | null {
    return this._end;
  }

  get mobileWorking(): boolean {
    return this._mobileWorking;
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

  public toJSON() {
    //TODO implement!
    return {

    }
  }
}