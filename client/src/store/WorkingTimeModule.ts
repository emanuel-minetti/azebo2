import { Module } from "vuex";
import { Holiday, WorkingDay, WorkingMonth, WorkingRule } from "@/models";
import { HolidayService, WorkingTimeService } from "@/services";

const WorkingTimeModule: Module<any, any> = {
  state: {
    month: WorkingMonth,
    holidays: Array<Holiday>(),
    rules: Array<WorkingRule>()
  },
  actions: {
    getMonth({ commit, dispatch, state }, monthDate: Date) {
      // make sure holidays are loaded before creating the working days
      if (state.holidays.length === 0) {
        dispatch("getHolidays", monthDate).then(() => {
          const year = monthDate.getFullYear().toString();
          let month = monthDate.getMonth() + 1;
          const monthString = month < 10 ? "0" + month : "" + month;
          return WorkingTimeService.getMonth(year, monthString).then(data => {
            let workingDays = data.working_days.map(
              (day: any) => new WorkingDay(day)
            );
            state.month = new WorkingMonth(monthDate, workingDays);
          });
        });
      }
    },
    getHolidays({ commit, state }, yearDate: Date) {
      const year = yearDate.getFullYear().toString();
      const holidays = new Array<Holiday>();
      return HolidayService.getHolidays(year).then(data => {
        data.holidays.forEach((element: any) => {
          holidays.push(new Holiday(element));
        });
        state.holidays = holidays;
      });
    },
    getRules({ commit }, monthDate: Date) {
      //TODO implement!
    }
  }
};

export default WorkingTimeModule;
