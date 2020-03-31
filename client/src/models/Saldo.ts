export default class Saldo {
  private _hours: number;
  private _minutes: number;
  private _positive: boolean;

  /**
   * The constructor.
   * @param millis a time intervall in milliseconds
   * @param positive whether the intervall should be added or subtracted
   */
  public constructor(millis?: number, positive = true) {
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

  /**
   * Private method that actually computes the addition.
   * @param other the saldo to be added
   */
  private add(other: Saldo): Saldo {
    if (
      (this._positive && other._positive) ||
      (!this._positive && !other._positive)
    ) {
      // both summands have the same sign
      this._hours += other._hours;
      this._minutes += other._minutes;
      this.fix();
    } else {
      // both summands have different signs
      if (
        this._hours > other._hours ||
        (this._hours === other._hours && this._minutes >= other._minutes)
      ) {
        // `this` is absolut bigger or equal `other`
        this._hours -= other._hours;
        this._minutes -= other._minutes;
        this.fix();
      } else {
        // `other` is absolut bigger
        this._hours = other._hours - this._hours;
        this._minutes = other._minutes - this._minutes;
        this.fix();
        this._positive = !this._positive;
      }
    }
    return this;
  }

  /**
   * Private helper method that fixes possible over- or underflow
   * in minutes.
   */
  private fix() {
    if (this._minutes < 0) {
      this._minutes += 60;
      this._hours--;
    } else if (this._minutes >= 60) {
      this._minutes -= 60;
      this._hours++;
    }
  }

  /**
   * Clones a Saldo.
   */
  clone(): Saldo {
    const clone = new Saldo();
    clone._hours = this._hours;
    clone._minutes = this._minutes;
    clone._positive = this._positive;
    return clone;
  }

  /**
   * Returns a new saldo by giving the time intervall by an amount of
   * milliseconds.
   * @param millis a time intervall in milliseconds
   * @param positive whether the intervall should be added or subtracted
   */
  static createFromMillis(millis: number, positive?: boolean): Saldo {
    return new Saldo(millis, positive);
  }

  /**
   *  Returns a new saldo by giving a start and end date.
   * @param from the start date
   * @param to the end date
   */
  static createFromDates(from: Date, to: Date): Saldo {
    const millis = to.valueOf() - from.valueOf();
    const positive = millis >= 0;
    return new Saldo(millis, positive);
  }

  /**
   * Returns a new saldo which is the sum of two saldos.
   * @param first first summand
   * @param second second summand
   */
  static getSum(first: Saldo, second: Saldo): Saldo {
    const result = first.clone();
    return result.add(second);
  }

  /**
   * Returns a nicely formatted string representation of a saldo.
   */
  toString(withSign = true): string {
    if (this._hours === 0 && this._minutes === 0) return "0:00";
    let result = withSign ? (this._positive ? "+" : "-") : "";
    result += this._hours + ":";
    result += this._minutes < 10 ? "0" : "";
    result += this._minutes;
    return result;
  }

  get hours(): number {
    return this._hours;
  }

  get minutes(): number {
    return this._minutes;
  }

  get positive(): boolean {
    return this._positive;
  }

  /**
   * Inverts the positiveness of this Saldo.
   */
  invert() {
    this._positive = !this._positive;
  }
}
