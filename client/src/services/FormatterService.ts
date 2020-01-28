export default class FormatterService {
  /**
   * Converts a `Date` to german localized (DD.MM.YYYY) string.
   * @param date the date to convert
   */
  public static toGermanDate(date: Date) {
    const options = {
      year: "numeric",
      month: "numeric",
      day: "numeric"
    };
    return date.toLocaleString("de-DE", options);
  }

  /**
   * Converts a `Date` to german localized (Wochentag, DD.MM.YYYY) string.
   * @param date the date to convert
   */
  public static toLongGermanDate(date: Date) {
    const options = { weekday: "long" };
    const weekday = date.toLocaleString("de-DE", options);
    return weekday + ", " + FormatterService.toGermanDate(date);
  }

  /**
   * Converts a `Date` to german localized (hh:mm) string.
   * @param date the date to convert
   */
  public static toGermanTime(date?: Date) {
    if (date) {
      const options = { hour: "numeric", minute: "numeric" };
      return date.toLocaleString("de-DE", options);
    } else {
      return "";
    }
  }
}
