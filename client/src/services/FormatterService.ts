export default class FormatterService {
  /**
   * Converts a `Date` to german localized (DD.MM.YYYY) string.
   * @param date the date to convert
   */
  public static toGermanDate(date: Date | null) {
    if (date) {
      const options = {
        year: "numeric",
        month: "2-digit",
        day: "numeric",
      } as const;
      return date.toLocaleString("de-DE", options);
    } else {
      return '';
    }
  }

  /**
   * Converts a `Date` to german localized (Wochentag, DD.MM.YYYY) string.
   * @param date the date to convert
   */
  public static toLongGermanDate(date: Date) {
    const options = { weekday: "long" } as const;
    const weekday = date.toLocaleString("de-DE", options);
    return weekday + ", " + FormatterService.toGermanDate(date);
  }

  /**
   * Converts a `Date` to german localized (hh:mm) string.
   * @param date the date to convert
   */
  public static toGermanTime(date?: Date) {
    if (date) {
      const options = { hour: "numeric", minute: "numeric" } as const;
      return date.toLocaleString("de-DE", options);
    } else {
      return "";
    }
  }

  /**
   * Converts a string representing a date returned by the service into a `Date`.
   * If a `Date` is given as an argument it is immediately returned.
   * @param dateString the string to convert
   */
  public static convertToDate(dateString: string | Date): Date {
    if (typeof dateString === "string") {
      const year = Number(dateString.substring(0, 4));
      const month = Number(dateString.substring(5, 7));
      const day = Number(dateString.substring(8, 10));
      return new Date(year, month - 1, day);
    }
    // an instance of `Date` was given so return it
    return dateString;
  }

  /**
   * Converts a string representing a time returned by a service into a `Date`.
   * If a `Date` is given as an argument it is immediately returned.
   *
   * @param year the year of the returned time
   * @param monthIndex the month index (0 -11) of the returned time
   * @param day the day of the returned time
   * @param timeString the string to convert
   */
  public static convertToTime(
    year: number,
    monthIndex: number,
    day: number,
    timeString?: string | Date
  ): Date | undefined {
    if (typeof timeString === "undefined" || timeString === null)
      return undefined;
    if (typeof timeString === "string") {
      const hour = Number(timeString.substring(0, 2));
      const minute = Number(timeString.substring(3, 5));
      return new Date(year, monthIndex, day, hour, minute);
    }
    // an instance of `Date` was given so return it
    return timeString;
  }
}
