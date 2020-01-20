export default class GermanDateFormatter {
  public static toGermanDate(date: Date) {
    const options = {
      year: "numeric",
      month: "numeric",
      day: "numeric"
    };
    return date.toLocaleString("de-DE", options);
  }

  public static toLongGermanDate(date: Date) {
    const options = { weekday: "long" };
    const weekday = date.toLocaleString("de-DE", options);
    return weekday + ", der " + GermanDateFormatter.toGermanDate(date);
  }

  public static toGermanTime(date?: Date) {
    if (date) {
      const options = { hour: "numeric", minute: "numeric" };
      return date.toLocaleString("de-DE", options);
    } else {
      return "";
    }
  }
}
