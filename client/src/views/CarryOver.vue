<template>
  <div>
    <Title prop-title="Übertrage" />
    <div id="intro">
      <div v-if="editing">
        Hier können Sie Ihre Überträge bearbeiten:
        <CarryForm :prop-disabled="false" @submitted="submitted" />
      </div>
      <div v-else>
        Ihre aktuellen Überträge lauten:
        <CarryForm :prop-disabled="true" />
        <b-button variant="primary" @click="setEditing">
          Bearbeiten
        </b-button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { CarryForm, Title } from "/src/components";
import { defineComponent } from "vue";

export default  defineComponent({
  name: "CarryOver",
  components: {
    Title,
    CarryForm,
  },
  data() {
    return {
      editing: false,
    }
  },
  methods: {
    setEditing() {
      this.editing = true;
    },
    submitted() {
      this.editing = false;
    }
  },
});
</script>

<style scoped>
#intro {
  font-size: 1.5rem;
  text-align: center;
}
</style>
