<template>
  <div>
    <Title prop-title="Arbeitszeit" />
    <div id="intro">
      Hier können Sie Ihre Arbeitszeitregelungen einsehen und neue Regelungen hinzufügen.
    </div>
    <p>
      Das ist ein Probetext.
    </p>
    <b-table
    bordered
    :items='rulesItems'
    >
    </b-table>
    <RulesForm />
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { RulesForm, Title } from "/src/components";
import { WorkingRule } from "/src/models";
export default defineComponent({
  components: {
    Title,
    RulesForm
  },
  data() {
    return {
      rules: Array<WorkingRule>
    }},
  mounted() {
    this.rules = this.$store.state.workingTime.rules;
  },
  methods: {
    rulesItems() {
      return this.rules;
    }
  }
});
</script>

<style scoped>
#intro {
  font-size: 1.5rem;
  text-align: center;
}
p {
  text-align: center;
}
</style>
