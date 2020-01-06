import { Module } from "vuex";
import WorkingDay from "@/models/WorkingDay";
import WorkingTimeService from "@/services/WorkingTimeService";
import WorkingMonth from "@/models/WorkingMonth";

const WorkingTimeModule: Module<any, any> = {
  state: {
    //TODO introduce class 'WorkingMonth'
    month: Array<WorkingDay>()
  },
  mutations: {
    setMonth(state, month: Array<WorkingDay>) {
      state.month = month;
    }
  },
  actions: {
    getMonth({ commit }, monthDate: Date) {
      const year = monthDate.getFullYear().toString();
      let month = monthDate.getMonth() + 1;
      const monthString = month < 10 ? "0" + month : month.toString();
      return WorkingTimeService.getMonth(year, monthString).then(data => {
        //TODO replace `Array<WorkingDay>` with `WorkingMonth`
        let test = new WorkingMonth(monthDate, data.working_days);
        commit("setMonth", data.working_days);
      });
    }
  }
};

export default WorkingTimeModule;
