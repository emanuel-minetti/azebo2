<template>
  <div class="home">
    <div v-if="loading" class="d-flex justify-content-center mb-3">
      <b-spinner id="spinner" label="Loading..."></b-spinner>
    </div>
    <div v-else>
      <div v-if="error" class="alert-danger" v-html="error" />
      <Title :prop-title="monthName" />
      <MonthTable />
      <MonthAggregate />
      <MonthForm />
    </div>
  </div>
</template>

<script lang="ts">
import { Title, MonthTable, MonthAggregate, MonthForm } from "/src/components";
import { defineComponent } from "vue";
import { Route } from "vue-router";
import { mapState } from "vuex";

export default  defineComponent({
  components: {
    Title,
    MonthTable,
    MonthAggregate,
    MonthForm,
  },
  data() {
    return {
      error: '',
    }
  },
  computed: {
    monthName() {
      const options = {
        year: "numeric",
        month: "2-digit",
      } as const;
      if (this.$store.state.workingTime.month.monthDate) {
        return this.$store.state.workingTime.month.monthDate.toLocaleString("de-DE", options);
      }
      else {
        return '';
      }
    },
    ...mapState(["loading"]),
  },
  watch: {
    $route(to: Route) {
      this.routeChanged(to);
    }
  },
  mounted() {
    this.routeChanged(this.$route);
  },
  methods: {
    routeChanged(to: Route) {
      this.error = "";
      let month = new Date();
      month.setDate(1);
      if (to.name === "month") {
        month.setMonth(Number(to.params.month) - 1);
        if (to.params.year) {
          month.setFullYear(Number(to.params.year));
        }
      }
      this.$store.dispatch("workingTime/getMonth", month).catch((reason) => {
        this.error =
            "Es gab ein Problem beim Laden der Daten für diesen Monat:<br/>" +
            reason;
        this.$store.commit("cancelLoading");
      });
    }
  }
})
</script>

<style scoped>
#spinner {
  width: 4rem;
  height: 4rem;
  margin-bottom: 100px;
}
</style>
