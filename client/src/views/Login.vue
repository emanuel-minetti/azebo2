<template>
    <div>
        <Title prop-title="Login" />
        <b-form @submit="onSubmit">
            <b-form-group
                    label="Benutzername:"
                    label-for="username">
                <b-form-input v-model="form.username" placeholder="Ihr Benutzername">
                </b-form-input>
            </b-form-group>
        </b-form>
    </div>
</template>

<script lang="ts">

    import {Component, Vue} from "vue-property-decorator";
    import Title from "@/components/Title.vue";

    @Component({
        components: {Title}
    })
    export default class Login extends Vue {
        form = {
            username: '',
            password: ''
        };

        onSubmit(evt: Event) {
            evt.preventDefault();
        }
    }
</script>

<style scoped>
    form {
        width: 80%;
    }
</style>