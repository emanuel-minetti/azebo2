export default class GermanKwService {
  private static readonly millisPerDay = 86400000;
  private static readonly daysPerWeek = 7;

  public static getGermanDay(date: Date) {
    return (date.getDay() + 6) % 7;
  }

  public static getGermanKW(date: Date) {
    const thursdayOfDate = new Date(
      date.getTime() + (3 - this.getGermanDay(date)) * this.millisPerDay
    );
    const yearOfThursday = thursdayOfDate.getFullYear();
    const firstThursdayOfYear = new Date(
      new Date(yearOfThursday, 0, 4).getTime() +
        (3 - this.getGermanDay(new Date(yearOfThursday, 0, 4))) *
          this.millisPerDay
    );
    return Math.floor(
      1 +
        0.5 +
        (thursdayOfDate.getTime() - firstThursdayOfYear.getTime()) /
          GermanKwService.millisPerDay /
          this.daysPerWeek
    );
  }

  public static getMondayForKW(kw: number, year: number): Date {
    const result = new Date(year, 0, 1);
    const firstDayOfYear = this.getGermanDay(result);
    if (firstDayOfYear > 4) {
      result.setTime(
        result.getTime() +
          (this.daysPerWeek - firstDayOfYear) * this.millisPerDay
      );
    } else {
      result.setTime(result.getTime() - firstDayOfYear * this.millisPerDay);
    }
    result.setTime(
      result.getTime() + (kw - 1) * this.millisPerDay * this.daysPerWeek
    );
    return result;
  }
}
