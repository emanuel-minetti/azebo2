export default class Saldo {
   private _hours: number;
   private _minutes: number;
   private _positive: boolean;

   //TODO make constructor private and create static methods for (Date, Date) and (milliseconds)
  constructor(value1?: number | Saldo, value2?: number, value3?: boolean) {
    if (value1 === undefined) {
      this._hours = 0;
      this._minutes = 0;
      this._positive = true;
    } else if (value1 instanceof Saldo) {
      this._hours = value1._hours;
      this._minutes = value1._minutes;
      this._positive = value1._positive;
    } else if (value2 === undefined){ // `value1` is number and `value2` is undefined
      const totalMinutes = Math.floor(value1 / (60 * 1000));
      this._hours = Math.floor(totalMinutes / 60);
      this._minutes = totalMinutes - this._hours * 60;
      this._positive = true;
    } else {
      this._hours = value1;
      this._minutes = value2;
      if (value3 === undefined) {
        this._positive = true;
      } else {
        this._positive = value3;
      }
    }
  }

  // get hours(): number {
  //   return this._hours;
  // }
  //
  // get minutes(): number {
  //   return this._minutes;
  // }
  //
  // get positive(): boolean {
  //   return this._positive;
  // }

  private add(other: Saldo): Saldo {
    if ((this._positive && other._positive) || (!this._positive && !other._positive)) {
      this._hours += other._hours;
      this._minutes += other._minutes;
      if (this._minutes > 60) {
        this._minutes -= 60;
        this._hours++;
      }
    } else {
      if ((this._hours > other._hours) || (this._hours === other._hours && this._minutes >= other._minutes)) {
        console.log("Angekommen");
        // `this` is absolut bigger or equal `other`
        this._minutes -= other._minutes;
        if (this._minutes < 0) {
          this._minutes += 60;
          this._hours--;
        }
      } else {
        // `other` is absolut bigger
        this._minutes = other._minutes - this._minutes;
        //TODO remove duplicates!
        if (this._minutes < 0) {
          this._minutes += 60;
          this._hours--;
        }
        this._positive = !this._positive;
      }
    }
    return this;
  }

  static getSum(first: Saldo, second: Saldo): Saldo {
    const result = first.clone();
    return  result.add(second);
  }

  clone(): Saldo {
    const clone = new Saldo();
    clone._hours = this._hours;
    clone._minutes = this._minutes;
    clone._positive = this._positive;
    return clone;
  }

  toString(): string {
    if (this._hours === 0 && this._minutes === 0) return "";
    let result = this._positive ? "+" : "-";
    result += this._hours + ":";
    result += this._minutes < 10 ? "0" : "";
    result += this._minutes;
    return  result;
  }

}
