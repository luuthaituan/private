<template>
    <section class="vh-100 login-form">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-dark text-white" style="border-radius: 1rem;">
                        <div class="card-body p-5 text-center">
                            <div class="mb-md-5 mt-md-4 pb-5">
                                <form action="javascript:void(0)" @submit="register" method="post">
                                    <h2 class="fw-bold mb-2 text-uppercase">Register</h2>
                                    <p class="text-white-50 mb-5">Please enter your login and password!</p>
                                    <div class="form-outline form-white mb-4">
                                        <label class="visually-hidden" for="name">Enter name</label>
                                        <input type="text" name="name" v-model="user.name" id="name" placeholder="Enter name" class="form-control form-control-lg" />
                                    </div>
                                    <div class="form-outline form-white mb-4">
                                        <label class="visually-hidden" for="email">Enter email</label>
                                        <input type="email" v-model="user.email" name="email" id="email" class="form-control form-control-lg" placeholder="Enter email" />
                                    </div>
                                    <div class="form-outline form-white mb-4">
                                        <label class="visually-hidden" for="password">Enter password</label>
                                        <input type="password" v-model="user.password" name="password" id="password" class="form-control form-control-lg" placeholder="Enter password" />
                                    </div>
                                    <div class="form-outline form-white mb-4">
                                        <label class="visually-hidden" for="password">Enter password</label>
                                        <input type="password" name="password_confirmation" v-model="user.password_confirmation" id="password_confirmation" placeholder="Enter Password" class="form-control form-control-lg" />
                                    </div>

                                    <button type="submit" :disabled="processing" class="btn btn-primary btn-block">
                                        {{ processing ? "Please wait" : "Register" }}
                                    </button>
                                </form>
                            </div>
                            <p class="mb-0">Already have an account? <router-link :to="{name:'login'}">Login Now!</router-link></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
            validationErrors: {},
            processing: false
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
