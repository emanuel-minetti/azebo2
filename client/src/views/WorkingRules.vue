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
    <div v-if='rules.length > 0'>
    <b-table
    bordered
    :items='rulesItems'
    primary-key='id'
    :fields='getFields()'
    >
    </b-table>
    <RulesForm />
    </div>
    <div v-else>
      Sie haben noch keine Arbeitszeitregelung
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { RulesForm, Title } from "/src/components";
import { WorkingRule } from "/src/models";
import { FormatterService } from "/src/services";
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
    },
    getFields: function () {
      return [
        {
          key: 'validFrom',
          label: "Regelungsbegin",
          formatter: FormatterService.toGermanDate,
        },
        {
          key: 'validTo',
          label: "Regelungsende",
          formatter: formatValidTo,
        },
        {
          key: 'percentage',
          label: "Prozentsatz der vollen Arbeitszeit",
        },
        {
          key: 'weekdays',
          label: "Wochentage",
          formatter: formatWeekdays,
        },
      ]
    }
  }
});

function formatValidTo(date: Date|null): string {
  return date ? FormatterService.toGermanDate(date) : "Bis auf weiteres";
}

function formatWeekdays(weekdays: Array<Number>): string {
  let result = '';
  if (weekdays.length == 5) {
    result = 'Alle';
  } else {
    weekdays.map(weekday => {
      switch (weekday) {
        case 1: return 'Montag';
        case 2: return 'Dienstag';
        case 3: return 'Mittwoch';
        case 4: return 'Donnerstag';
        case 5: return 'Freitag';
      }
    }).forEach(day => result += day + ", ");
    result = result.substring(0, result.length - 2);
  }
  return result;
}
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
