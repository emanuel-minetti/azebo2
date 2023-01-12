/*
 * This is a local configuration file.
 *
 * You should adjust the values of these settings and rename the file to
 * 'times.local.ts'.
 *
 */

const timesConfig = {
  coreTimeBegin: "9:30",
  coreTimeEnd: "14:30",
  coreTimeEndShort: "13:00",
  breakDuration: 30, // half an hour in minutes
  longBreakDuration: 45, // three quarters of an hour in minutes
  breakRequiredFrom: 6, // in hours
  longBreakRequiredFrom: 9.5, // in hours
  longDayFrom: 10, // in hours
  previousHolidaysValidTo: 9, // holidays left from last year are valid to month (1-12)
};

export default timesConfig;
