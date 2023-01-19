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
        this._mobileWorking = data.mobile_working === 'true';
    } else {
      this._id = 0;
      this._workingDayId = 0;
      this._begin = null;
      this._end = null;
      this._mobileWorking = false;
    }
  }

  public toJSON() {
    //TODO implement!
    return {

    }
  }
}