export default class GermanKwService {
  public static getGermanDay(date: Date) {
    return (date.getDay() + 6) % 7;
  }

  public static getGermanKW(date: Date) {
    const millisPerDay = 86400000;
    const thursdayOfDate = new Date(
      date.getTime() + (3 - this.getGermanDay(date)) * millisPerDay
    );
    const yearOfThursday = thursdayOfDate.getFullYear();
    const firstThursdayOfYear = new Date(
      new Date(yearOfThursday, 0, 4).getTime() +
        (3 - this.getGermanDay(new Date(yearOfThursday, 0, 4))) * millisPerDay
    );
    return Math.floor(
      1 +
        0.5 +
        (thursdayOfDate.getTime() - firstThursdayOfYear.getTime()) /
          millisPerDay /
          7
    );
  }
}
