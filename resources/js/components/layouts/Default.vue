<template>
    <v-card>
        <v-layout>
            <v-navigation-drawer
                v-model="drawer"
                temporary
            >
                <v-list>
                    <v-list-item
                        :prepend-avatar="avatar()"
                        :title="user.name"
                        :subtitle="user.email"
                    ></v-list-item>
                </v-list>

                <v-divider></v-divider>

                <v-list density="compact" nav>
                    <v-list-item :to="{ name: 'dashboard' }" prepend-icon="mdi-view-dashboard" title="Dashboard" value="dashboard"></v-list-item>
                    <v-list-item :to="{ name: 'resources' }" prepend-icon="mdi-account-group-outline" title="Resources" value="resource"></v-list-item>
                    <v-list-item :to="{ name: 'projects' }" prepend-icon="mdi-folder-multiple" title="Projects" value="project"></v-list-item>
                    <v-list-item :to="{ name: 'holidays' }" prepend-icon="mdi-calendar-edit" title="Holidays" value="holiday"></v-list-item>
                    <v-list-item :to="{ name: 'settings' }" prepend-icon="mdi-cogs" title="Settings" value="settings"></v-list-item>
                </v-list>

                <template v-slot:append>
                    <div class="pa-2">
                        <v-btn block @click="logout">Logout</v-btn>
                    </div>
                </template>
            </v-navigation-drawer>

            <v-layout-item model-value position="bottom" class="text-start" size="88">
                <div class="ma-4">
                    <v-btn icon="mdi-menu" size="large" color="primary" elevation="8" @click.stop="drawer = !drawer"/>
                </div>
            </v-layout-item>

            <v-main>
                <Suspense>
                    <router-view @show-alert="showAlert"/>
                    <template #fallback>
                        <v-progress-linear
                            indeterminate
                            color="cyan"
                        ></v-progress-linear>
                    </template>
                </Suspense>
                <v-snackbar
                    v-model="snackbar"
                    :color="message.type"
                >
                    {{ message.text }}
                </v-snackbar>
            </v-main>
        </v-layout>
    </v-card>
</template>

<script>
import { mapActions } from 'vuex';
import sha256 from 'crypto-js/sha256';

export default {
    name: "default-layout",
    data() {
        return {
            drawer: false,
            snackbar: false,
            message: {},
            user: this.$store.state.auth.user
        }
    },
    methods: {
        ...mapActions({
            signOut: "auth/logout"
        }),
        avatar() {
            return this.user.avatar ? this.user.avatar : 'https://gravatar.com/avatar/' + sha256(this.user.email).toString()
        },
        async logout() {
            await axios.post('/logout').then(({data}) => {
                this.signOut();
                this.$router.push({ name: "login" });
            })
        },
        showAlert(message) {
            this.snackbar = true;
            this.message = message;
        }
    }
}
</script>

<style>
.v-main {
    min-height: calc(100vh);
}
</style>
