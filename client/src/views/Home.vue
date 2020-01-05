<template>
  <div class="home">
    <Title prop-title="Startseite" />
    <table>
      <tr>
        <th>Tag</th>
        <th>Beginn</th>
        <th>Ende</th>
      </tr>
      <tr v-for="day in this.month" v-bind:key="day.id">
        <td>{{ day.date }}</td>
        <td>{{ day.begin }}</td>
        <td>{{ day.end }}</td>
      </tr>
    </table>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import Title from "@/components/Title.vue";

@Component({
  components: { Title }
})
export default class Home extends Vue {
  get month() {
    return this.$store.state.workingTime.month;
  }

  mounted() {
    this.$store.dispatch("getMonth", new Date()).then(() => {});
  }
}
</script>
