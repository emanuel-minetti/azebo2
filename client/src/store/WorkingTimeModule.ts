import { Module } from "vuex";
import { Holiday, WorkingDay, WorkingMonth, WorkingRule } from "@/models";
import {
  HolidayService,
  WorkingRuleService,
  WorkingTimeService
} from "@/services";

const WorkingTimeModule: Module<any, any> = {
  state: {
    month: WorkingMonth,
    holidays: Array<Holiday>(),
    rules: Array<WorkingRule>()
  },
  actions: {
    getMonth({ commit, dispatch, state }, monthDate: Date) {
      // make sure holidays are loaded before creating the working days
      const year = monthDate.getFullYear().toString();
      const month = monthDate.getMonth() + 1;
      const monthString = month < 10 ? "0" + month : "" + month;

      return HolidayService.getHolidays(year)
        .then(data => {
          state.holidays = data.result.map((day: any) => new Holiday(day));
        })
        .then(() =>
          WorkingRuleService.getByMonth(year, monthString).then(data => {
            state.rules = data.result.map(
              (rule: any) => new WorkingRule(rule)
            );
          })
        )
        .then(() =>
          WorkingTimeService.getMonth(year, monthString).then(data => {
            let workingDays = data.result.map(
              (day: any) => new WorkingDay(day)
            );
            state.month = new WorkingMonth(monthDate, workingDays);
          })
        );
    }
  }
};

export default WorkingTimeModule;
