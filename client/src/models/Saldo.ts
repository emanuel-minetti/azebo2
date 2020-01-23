export default class Saldo {
  private _hours: number;
  private _minutes: number;
  private _positive: boolean;

  private constructor(millis?: number, positive = true) {
    if (millis === undefined) {
      this._hours = 0;
      this._minutes = 0;
      this._positive = true;
    } else {
      const totalMinutes = Math.floor(millis / (60 * 1000));
      this._hours = Math.floor(totalMinutes / 60);
      this._minutes = totalMinutes - this._hours * 60;
      this._positive = positive;
    }
  }

  private add(other: Saldo): Saldo {
    if (
      (this._positive && other._positive) ||
      (!this._positive && !other._positive)
    ) {
      this._hours += other._hours;
      this._minutes += other._minutes;
      this.fix();
    } else {
      if (
        this._hours > other._hours ||
        (this._hours === other._hours && this._minutes >= other._minutes)
      ) {
        // `this` is absolut bigger or equal `other`
        this._minutes -= other._minutes;
        this.fix();
      } else {
        // `other` is absolut bigger
        this._minutes = other._minutes - this._minutes;
        this.fix();
        this._positive = !this._positive;
      }
    }
    return this;
  }

  private fix() {
    if (this._minutes < 0) {
      this._minutes += 60;
      this._hours--;
    } else if (this._minutes >= 60) {
      this._minutes -= 60;
      this._hours++;
    }
  }

  private clone(): Saldo {
    const clone = new Saldo();
    clone._hours = this._hours;
    clone._minutes = this._minutes;
    clone._positive = this._positive;
    return clone;
  }

  static create(
    millisOrFromDate: number | Date,
    positiveOrToDate: boolean | Date | undefined
  ): Saldo {
    if (
      typeof millisOrFromDate === "number" &&
      typeof positiveOrToDate === "boolean"
    ) {
      return new Saldo(millisOrFromDate, positiveOrToDate);
    } else if (
      millisOrFromDate instanceof Date &&
      positiveOrToDate instanceof Date
    ) {
      const millis = positiveOrToDate.valueOf() - millisOrFromDate.valueOf();
      const positive = millis >= 0;
      return new Saldo(millis, positive);
    } else throw "Wrong Parameter Types";
  }

  static getSum(first: Saldo, second: Saldo): Saldo {
    const result = first.clone();
    return result.add(second);
  }

  toString(): string {
    if (this._hours === 0 && this._minutes === 0) return "";
    let result = this._positive ? "+" : "-";
    result += this._hours + ":";
    result += this._minutes < 10 ? "0" : "";
    result += this._minutes;
    return result;
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
}
