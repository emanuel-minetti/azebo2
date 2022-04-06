export default class GermanKwService {
  public static getGermanKW(date: Date) {
    const millisPerDay = 86400000;
    const thursdayOfDate = new Date(
      date.getTime() + (3 - ((date.getDay() + 6) % 7)) * millisPerDay
    );
    const yearOfThursday = thursdayOfDate.getFullYear();
    const firstThursdayOfYear = new Date(
      new Date(yearOfThursday, 0, 4).getTime() +
        (3 - ((new Date(yearOfThursday, 0, 4).getDay() + 6) % 7)) * millisPerDay
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
