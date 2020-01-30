import { Module } from "vuex";
import { Holiday, WorkingDay, WorkingMonth } from "@/models";
import { HolidayService, WorkingTimeService } from "@/services";

const WorkingTimeModule: Module<any, any> = {
  state: {
    month: WorkingMonth,
    holidays: Array<Holiday>()
  },
  mutations: {
    setMonth(state, month: WorkingMonth) {
      state.month = month;
    },
    setHolidays(state, holidays) {
      state.holidays = holidays;
    }
  },
  actions: {
    getMonth({ commit, dispatch, state }, monthDate: Date) {
      const year = monthDate.getFullYear().toString();
      let month = monthDate.getMonth() + 1;
      const monthString = month < 10 ? "0" + month : "" + month;
      return WorkingTimeService.getMonth(year, monthString).then(data => {
        let workingDays = data.working_days.map(
          (day: any) => new WorkingDay(day)
        );
        let workingMonth = new WorkingMonth(monthDate, workingDays);
        commit("setMonth", workingMonth);
        if (state.holidays.length === 0) {
          dispatch("getHolidays", monthDate).then(() => {});
        }
      });
    },
    getHolidays({ commit }, yearDate: Date) {
      const year = yearDate.getFullYear().toString();
      return HolidayService.getHolidays(year).then(data => {
        console.log(data);
      });
    }
  }
};

export default WorkingTimeModule;
