<template>
    <main class="h-screen register-form">
        <v-container class="fill-height">
            <v-layout class="d-flex align-center justify-center">
                <v-card class="mx-auto pa-12 pb-8 w-50" elevation="8" rounded="lg">
                    <v-card class="mb-12" color="surface-variant" variant="tonal" v-if="message !== ''">
                        <v-alert color="error">{{ message }}</v-alert>
                    </v-card>

                    <v-form v-model="valid" ref="form">
                        <div class="text-subtitle-1 text-medium-emphasis">Name</div>
                        <v-text-field density="compact"
                              v-model="user.name"
                              :rules="nameRules"
                              required
                              placeholder="Name"
                              prepend-inner-icon="mdi-account-details-outline"
                              variant="outlined"
                              clearable
                        ></v-text-field>

                        <v-text-field density="compact"
                              v-model="user.email"
                              :type="'email'"
                              :rules="emailRules"
                              required
                              placeholder="Email address"
                              prepend-inner-icon="mdi-email-outline"
                              variant="outlined"
                              clearable
                        ></v-text-field>

                        <v-text-field :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                              v-model="user.password"
                              :rules="passwordRules"
                              :type="showPassword ? 'text' : 'password'"
                              density="compact"
                              placeholder="Enter your password"
                              prepend-inner-icon="mdi-lock-outline"
                              variant="outlined"
                              @click:append-inner="showPassword = !showPassword"
                              clearable
                        ></v-text-field>

                        <v-text-field :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                              v-model="user.password_confirmation"
                              :rules="passwordRules"
                              :type="showPassword ? 'text' : 'password'"
                              density="compact"
                              placeholder="Enter your password"
                              prepend-inner-icon="mdi-lock-outline"
                              variant="outlined"
                              @click:append-inner="showPassword = !showPassword"
                              clearable
                        ></v-text-field>

                        <v-btn type="submit" block class="mb-8" color="blue" size="large" variant="tonal"
                               @click="register" :disabled="processing"
                               :class=" { disabled: !valid }"
                        >
                            {{ processing ? "Please wait" : "Register" }}
                        </v-btn>
                    </v-form>

                    <v-card-text class="text-center">
                        Already have an account? <router-link class="text-blue text-decoration-none" :to="{name:'login'}">Login Now!</router-link>
                    </v-card-text>
                </v-card>
            </v-layout>
        </v-container>
    </main>
</template>

<script>
import {mapActions} from 'vuex'

export default {
    name: 'register',
    data() {
        return {
            user: {
                name: "",
                email: "",
                password: "",
                password_confirmation: ""
            },
            message: "",
            validationErrors: {},
            valid: false,
            processing: false,
            showPassword: false,
            nameRules: [
                (v) => !!v || 'Name is required',
            ],
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
        async register() {
            this.processing = true;
            await axios.get('/sanctum/csrf-cookie');
            await axios.post('/register', this.user).then(response => {
                this.validationErrors = {};
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
        }
    }
}
</script>
