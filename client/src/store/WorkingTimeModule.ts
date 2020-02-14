import { Module } from "vuex";
import {
  Carry,
  Holiday,
  Saldo,
  WorkingDay,
  WorkingMonth,
  WorkingRule
} from "@/models";
import {
  CarryService,
  HolidayService,
  WorkingRuleService,
  WorkingTimeService
} from "@/services";

const WorkingTimeModule: Module<any, any> = {
  state: {
    month: WorkingMonth,
    holidays: Array<Holiday>(),
    rules: Array<WorkingRule>(),
    carry: Carry
  },
  getters: {
    saldo(state) {
      if (state.month.days) {
        return state.month.days
          .map((day: WorkingDay) => day.saldoTime)
          .reduce(
            (previousValue: Saldo, currentValue: Saldo | undefined) =>
              currentValue
                ? Saldo.getSum(previousValue, currentValue)
                : previousValue,
            Saldo.createFromMillis(0)
          );
      }
      return "";
    },
    saldoTotal(state, getters) {
      if (getters.saldo !== "" && state.carry.saldo) {
        return Saldo.getSum(getters.saldo, state.carry.saldo);
      }
      return "";
    }
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
            state.rules = data.result.map((rule: any) => new WorkingRule(rule));
          })
        )
        .then(() =>
          WorkingTimeService.getMonth(year, monthString).then(data => {
            let workingDays = data.result.map(
              (day: any) => new WorkingDay(day)
            );
            state.month = new WorkingMonth(monthDate, workingDays);
          })
        )
        .then(() =>
          CarryService.getByMonth(year, monthString).then(data => {
            state.carry = new Carry(data.result);
          })
        );
    }
  }
};

export default WorkingTimeModule;
