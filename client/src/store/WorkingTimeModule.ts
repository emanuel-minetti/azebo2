import { Module } from "vuex";
import WorkingDay from "@/models/WorkingDay";
import WorkingTimeService from "@/services/WorkingTimeService";

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
      WorkingTimeService.getMonth(year, monthString).then(data =>
        console.log(data)
      );
    }
  }
};

export default WorkingTimeModule;
