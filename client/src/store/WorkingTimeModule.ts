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
    carry: Carry,
    dayToEdit: WorkingDay
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
  mutations: {
    setDayToEdit(state, date: Date) {
      state.dayToEdit = state.month.getDayByDate(date);
    }
  },
  actions: {
    getMonth({ commit, dispatch, state, rootState }, monthDate: Date) {
      rootState.loading = true;
      // make sure holidays are loaded before creating the working days
      const year = monthDate.getFullYear().toString();
      const month = monthDate.getMonth() + 1;
      const monthString = month < 10 ? "0" + month : "" + month;

      // TODO review
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
          CarryService.getCarryByMonth(year, monthString).then(data => {
            state.carry = new Carry(data.result);
          })
        )
        .then(() => {
          rootState.loading = false;
          return this;
        });
    },

    setDay({ state }, day: WorkingDay) {
      return WorkingTimeService.setDay(day).then(() => {});
    }
  }
};

export default WorkingTimeModule;
