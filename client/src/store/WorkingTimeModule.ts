import { Module } from "vuex";
import {
  Carry,
  Holiday,
  Saldo,
  WorkingDay,
  WorkingMonth,
  WorkingRule,
} from "/src/models";
import {
  CarryService, FormatterService,
  HolidayService,
  WorkingRuleService,
  WorkingTimeService,
} from "/src/services";
import { timesConfig } from "/src/configs";

const WorkingTimeModule: Module<any, any> = {
  namespaced: true,
  state: {
    month: WorkingMonth,
    previous: WorkingMonth,
    holidays: Array<Holiday>(),
    rules: Array<WorkingRule>(),
    carryResult: Carry,
    dayToEdit: WorkingDay,
    carry: Carry,
  },
  getters: {
    timeTotal(state) {
      if (state.month.days) {
        return state.month.days.map((day: WorkingDay) => day.actualTime).reduce(
          (previousValue: Saldo, currentValue: Saldo) =>
            currentValue
              ? Saldo.getSum(previousValue, currentValue)
              : previousValue,
          Saldo.createFromMillis(0)
        )
      }
      return Saldo.createFromMillis(0);
    },
    timeMobileTotal(state) {
      if (state.month.days) {
        return state.month.days
          .map((day: WorkingDay) => day.mobileWorking)
          .reduce((prev: Saldo, curr: Saldo) =>
            Saldo.getSum(prev!, curr!),
            Saldo.createFromMillis(0));
      }
      return Saldo.createFromMillis(0);
    },
    saldo(state) {
      if (state.month.days) {
        let saldo = state.month.days
          .map((day: WorkingDay) => day.saldoTime)
          .reduce(
            (previousValue: Saldo, currentValue: Saldo | undefined) =>
              currentValue
                ? Saldo.getSum(previousValue, currentValue)
                : previousValue,
            Saldo.createFromMillis(0)
          );
        if (state.carryResult.saldo) {
          const percentage = state.rules[state.rules.length - 1].percentage;
          const cappingLimitMinutes = timesConfig.cappingLimit * percentage / 100;
          const cappingLimit = Saldo.createFromMillis(cappingLimitMinutes * 60 * 1000);
          cappingLimit.invert();
          const totalSaldo = Saldo.getSum(saldo, state.carryResult.saldo);
          const difference = Saldo.getSum(totalSaldo, cappingLimit);
          if (difference.positive) {
            difference.invert();
            saldo = Saldo.getSum(saldo, difference);
            state.month.cappedSaldo = true;
          }
        }
        return saldo;
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
    // eslint-disable-next-line no-unused-vars
    getMonth({ commit, dispatch, state, rootState }, monthDate: Date) {
      rootState.loading = true;
      monthDate.setDate(1);
      const prevMonthDate = new Date(monthDate);
      prevMonthDate.setDate(0);
      prevMonthDate.setDate(1);
      // make sure holidays are loaded before creating the working day
      const year = monthDate.getFullYear().toString();
      const yearOfPrev = prevMonthDate.getFullYear().toString();
      const month = monthDate.getMonth() + 1;
      const monthOfPrev = prevMonthDate.getMonth() + 1;
      const monthString = month < 10 ? "0" + month : "" + month;
      const monthStringOfPrev =
        monthOfPrev < 10 ? "0" + monthOfPrev : "" + monthOfPrev;
      const params = year + "/" + monthString;
      const paramsOfPrev = yearOfPrev + "/" + monthStringOfPrev;

      // TODO review (see Issue #28)
      return HolidayService.getHolidays(year)
        .then((data) => {
          state.holidays = data.result.map((day: any) => new Holiday(day));
        })
        .then(() =>
          WorkingRuleService.get().then((data) => {
            state.rules = data.result.map((rule: any) => new WorkingRule(rule));
          })
        )
        .then(() =>
          WorkingTimeService.getMonth(params).then((data) => {
            const workingDays = data.result.days.map(
              (day: any) => new WorkingDay(day)
            );
            const month = data.result.month ?
              data.result.month :
              { 'month': FormatterService.toServiceString(monthDate)};
            state.month = new WorkingMonth(month, workingDays);
          })
        )
        .then(() =>
          WorkingTimeService.getMonth(paramsOfPrev).then((data) => {
            const workingDays = data.result.days.map(
              (day: any) => new WorkingDay(day)
            );
            const month = data.result.month ?
              data.result.month : null;
            state.previous = new WorkingMonth(month, workingDays);
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
    getRules({ state, rootState }) {
      rootState.loading = true;
      return WorkingRuleService.get()
        .then((data) => {
          state.rules = data.result.map((rule: any) => new WorkingRule(rule));
        })
        .then(() => {
          rootState.loading = false;
          return this;
        });
    },
    // eslint-disable-next-line no-unused-vars
    setRule({ state }, data: any) {
      return WorkingRuleService.setRule(data).then(() => {});
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
    // eslint-disable-next-line no-unused-vars
    setDay({ state }, day: WorkingDay) {
      return WorkingTimeService.setDay(day).then(() => {});
    },
    // eslint-disable-next-line no-unused-vars
    setCarry({ state }, carry: Carry) {
      return CarryService.setCarry(carry).then(() => {});
    },
    // eslint-disable-next-line no-unused-vars
    closeMonth({ state }, month: WorkingMonth) {
      return WorkingTimeService.closeMonth(month);
    }
  },
};

export default WorkingTimeModule;
