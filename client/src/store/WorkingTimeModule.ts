import { Module } from "vuex";
import {
  Carry,
  Holiday,
  Saldo,
  WorkingDay,
  WorkingMonth,
  WorkingRule,
} from "@/models";
import {
  CarryService,
  HolidayService,
  WorkingRuleService,
  WorkingTimeService,
} from "@/services";

const WorkingTimeModule: Module<any, any> = {
  namespaced: true,
  state: {
    month: WorkingMonth,
    holidays: Array<Holiday>(),
    rules: Array<WorkingRule>(),
    carryResult: Carry,
    dayToEdit: WorkingDay,
    carry: Carry,
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
      if (getters.saldo !== "" && state.carryResult.saldo) {
        return Saldo.getSum(getters.saldo, state.carryResult.saldo);
      }
      return "";
    },
  },
  mutations: {
    setDayToEdit(state, date: Date) {
      state.dayToEdit = state.month.getDayByDate(date);
    },
  },
  actions: {
    getMonth({ commit, dispatch, state, rootState }, monthDate: Date) {
      rootState.loading = true;
      monthDate.setDate(1);
      // make sure holidays are loaded before creating the working day
      const year = monthDate.getFullYear().toString();
      const month = monthDate.getMonth() + 1;
      const monthString = month < 10 ? "0" + month : "" + month;
      const params = year + "/" + monthString;

      // TODO review (see Issue #28)
      return HolidayService.getHolidays(year)
        .then((data) => {
          state.holidays = data.result.map((day: any) => new Holiday(day));
        })
        .then(() =>
          WorkingRuleService.getByMonth(params).then((data) => {
            state.rules = data.result.map((rule: any) => new WorkingRule(rule));
          })
        )
        .then(() =>
          WorkingTimeService.getMonth(params).then((data) => {
            let workingDays = data.result.map(
              (day: any) => new WorkingDay(day)
            );
            state.month = new WorkingMonth(monthDate, workingDays);
          })
        )
        .then(() =>
          CarryService.getCarryResultByMonth(params).then((data) => {
            state.carryResult = new Carry(data.result);
          })
        )
        .then(() => {
          rootState.loading = false;
          return this;
        });
      //   dispatch("getHolidays", year)
      //     .then(() => dispatch("getWorkingRules", params))
      //     .then(() =>
      //       WorkingTimeService.getMonth(params).then(data => {
      //         let workingDays = data.result.map(
      //           (day: any) => new WorkingDay(day)
      //         );
      //         state.month = new WorkingMonth(monthDate, workingDays);
      //       })
      //     )
      //     .then(() =>
      //       CarryService.getCarryByMonth(year, monthString).then(data => {
      //         state.carry = new Carry(data.result);
      //       })
      //     )
      //     .then(() => {
      //       rootState.loading = false;
      //       return this;
      //     });
      // },
      //
      // getHolidays({ state }, year: string) {
      //   if (!state.holidays.length) {
      //     return HolidayService.getHolidays(year).then(
      //       data =>
      //         (state.holidays = data.result.map((day: any) => new Holiday(day)))
      //     );
      //   }
      //   return Promise.resolve(state.holidays);
      // },
      //
      // getWorkingRules({ state }, params: string) {
      //   if (!state.rules.length) {
      //     return WorkingRuleService.getByMonth(params).then(
      //       data =>
      //         (state.rules = data.result.map(
      //           (rule: any) => new WorkingRule(rule)
      //         ))
      //     );
      //   }
      //   return Promise.resolve(state.rules);
      // },
      //
      // getWorkingDays({ state }, params) {
      //
    },
    getCarry({ state, rootState }) {
      rootState.loading = true;
      return CarryService.getCarry()
        .then((data) => {
          state.carry = new Carry(data.result);
        })
        .then(() => {
          rootState.loading = false;
          return this;
        });
    },
    setDay({ state }, day: WorkingDay) {
      return WorkingTimeService.setDay(day).then(() => {});
    },
  },
};

export default WorkingTimeModule;
