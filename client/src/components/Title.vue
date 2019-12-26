<template>
    <h1>
        {{propTitle}}
    </h1>
</template>

<script lang="ts">
    import {Vue, Component} from "vue-property-decorator";

    @Component({
        props: {
            propTitle: String
        }
    })
    export default class Title extends Vue {
    }
</script>

<style scoped>
    h1 {
        text-align: center;
    }

</style>