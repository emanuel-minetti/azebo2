import { Module } from "vuex";
import { WorkingDay, WorkingMonth } from "@/models";
import { WorkingTimeService } from "@/services";

const WorkingTimeModule: Module<any, any> = {
  state: {
    month: WorkingMonth
  },
  mutations: {
    setMonth(state, month: WorkingMonth) {
      state.month = month;
    }
  },
  actions: {
    getMonth({ commit }, monthDate: Date) {
      const year = monthDate.getFullYear().toString();
      let month = monthDate.getMonth() + 1;
      const monthString = month < 10 ? "0" + month : "" + month;
      return WorkingTimeService.getMonth(year, monthString).then(data => {
        let workingDays = data.working_days.map(
          (day: any) => new WorkingDay(day)
        );
        let workingMonth = new WorkingMonth(monthDate, workingDays);
        commit("setMonth", workingMonth);
      });
    }
  }
};

export default WorkingTimeModule;
