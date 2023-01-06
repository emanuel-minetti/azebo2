<template>
  <b-form>
    <b-form-group label="Arbeitszeit:">
    </b-form-group>
  </b-form>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { WorkingRule } from "/src/models";


export default defineComponent({
  data() {
    return {
      rules: Array<WorkingRule>
    }},
  mounted() {
    this.rules = this.$store.state.workingTime.rules;
  }
})
</script>

<style scoped>
form {
  width: 30%;
  margin-left: 35%;
  margin-right: 35%;
}

>>> label,
>>> legend {
  font-size: large;
  font-weight: bold;
}
</style>
