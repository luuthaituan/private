<template>
    <main class="login-form">
        <v-container class="fill-height">
            <v-layout class="d-flex align-center justify-center">
                <v-card class="mx-auto pa-12 pb-8" elevation="8" max-width="448" rounded="lg">
                    <v-card class="mb-12" color="surface-variant" variant="tonal" v-if="message !== ''">
                        <v-alert color="error">{{ message }}</v-alert>
                    </v-card>

                    <v-form v-model="valid" ref="form">
                        <div class="text-subtitle-1 text-medium-emphasis">Account</div>
                        <v-text-field density="compact"
                              v-model="auth.email"
                              :type="'email'"
                              :rules="emailRules"
                              required
                              placeholder="Email address"
                              prepend-inner-icon="mdi-email-outline"
                              variant="outlined"
                              clearable
                        ></v-text-field>
                        <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                            Password
                            <a class="text-caption text-decoration-none text-blue" href="#" rel="noopener noreferrer" tabindex="-1">Forgot login password?</a>
                        </div>
                        <v-text-field :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                              v-model="auth.password"
                              :rules="passwordRules"
                              :type="showPassword ? 'text' : 'password'"
                              density="compact"
                              placeholder="Enter your password"
                              prepend-inner-icon="mdi-lock-outline"
                              variant="outlined"
                              @click:append-inner="showPassword = !showPassword"
                              clearable
                        ></v-text-field>
                        <v-card class="mb-12" color="surface-variant" variant="tonal">
                            <v-card-text class="text-medium-emphasis text-caption">
                                Warning: After 3 consecutive failed login attempts, you account will
                                be temporarily locked for three hours. If you must login now, you can
                                also click "Forgot login password?" below to reset the login password.
                            </v-card-text>
                        </v-card>

                        <v-btn type="submit" block class="mb-8" color="blue" size="large" variant="tonal"
                               @click="login" :disabled="processing"
                               :class=" { disabled: !valid }"
                        >
                            {{ processing ? "Please wait" : "Log In" }}
                        </v-btn>
                    </v-form>

                    <v-card-text class="text-center">
                        Don't have an account? <router-link class="text-blue text-decoration-none" :to="{ name:'register' }">Register Now!</router-link>
                    </v-card-text>
                </v-card>
            </v-layout>
        </v-container>
    </main>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: "login",
    data() {
        return {
            auth: {
                email: "",
                password: ""
            },
            message: "",
            validationErrors: {},
            valid: false,
            processing: false,
            showPassword: false,
            passwordRules: [
                (v) => !!v || 'Password is required',
                (v) => v.length >= 8 || 'Password must be at least 8 characters',
            ],
            emailRules: [
                (v) => !!v || 'E-mail is required',
                (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail must be valid'
            ],
        }
    },
    methods: {
        ...mapActions({
            signIn: 'auth/login'
        }),
        async login() {
            this.$refs.form.validate();

            if (!this.valid) {
                return;
            }

            this.processing = true;
            await axios.get('/sanctum/csrf-cookie');
            await axios.post('/login', this.auth).then(({data}) => {
                this.signIn();
            }).catch(({response}) => {
                if (response.status === 422) {
                    this.message = response.data.message;
                    this.validationErrors = response.data.errors;
                } else {
                    this.validationErrors = {}
                    alert(response.data.message);
                }
            }).finally(() => {
                this.processing = false;
            })
        },
    }
}
</script>

<style>
.login-form {
    width: 100%;
    height: 100vh;
}
</style>
