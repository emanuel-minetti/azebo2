<template>
  <div>
    <b-form-input
      type="text"
      :value="propValue.toString()"
      v-on:focus="onfocus"
    />
    <b-modal v-model="modalShow">
      <div class="d-block text-center">
        <h3>Hello From My Modal!</h3>
      </div>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from "vue-property-decorator";
import { Saldo } from "@/models";

@Component({})
export default class SaldoInput extends Vue {
  @Prop({
    type: Saldo,
    default: Saldo.createFromMillis(0)
  })
  readonly propValue!: Saldo;
  modalShow = false;

  onfocus(evt: Event) {
    this.modalShow = true;
    let blur = evt.target as HTMLElement;
    blur.blur();
  }
}
</script>

<style scoped></style>
