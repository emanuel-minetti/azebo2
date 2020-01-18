export default class GermanDateFormatter {
  public static toGermanDate(date: Date) {
    return (
      date.getDate() + "." + (date.getMonth() + 1) + "." + date.getFullYear()
    );
  }
}
