<template>
  <div>
    <Title prop-title="Arbeitszeit" />
    <div id="intro">
      Hier können Sie Ihre Arbeitszeitregelungen einsehen und neue Regelungen hinzufügen.
    </div>
    <p>
      Eine Arbeitszeitregeung kann nach ihrer Erstellung weder verändert noch gelöscht werden.<br>
    </p>
    <p>
      Sollte sich Ihre Arbeitszeitregelung ändern (z.B. wegen einer Vertragsänderung oder weil Sie an anderen
      Wochentagen arbeiten),<br> erstellen Sie bitte eine neue Regelung mit dem entsprechenden Anfangsdatum.
      (Das Enddatum der bestehenden Regelung wird dabei automatisch angepasst.)
    </p>
    <p>
      Sollten Sie eine Regelung irrtümlich erstellt haben erstellen Sie eine neue Regelung mit <b>demselben</b>
      Anfagsdatum, wie die zu ersetzende.<br> Diese überschreibt dann die alte Regelung. Wenn Sie dies tun, ändern sich
      ihre Soll-Arbeitszeiten für den gesamten Gültigkeitszeitraum und es erscheint ein Vermerk auf Ihrem nächsten
      Ausdruck.
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
