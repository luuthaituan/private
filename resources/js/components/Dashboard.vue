<template>
    <v-layout>
        <v-row>
            <v-col
                cols="12"
                sm="4"
            >
                <v-sheet
                    class="text-center ma-2"
                    elevation="4"
                    min-height="250"
                    rounded
                >
                    <v-card-text class="text-h6">Pending Tasks</v-card-text>
                    <v-card-item>
                        <v-table
                            density="compact"
                            :loading="pending_tasks.loading"
                        >
                            <thead>
                                <tr>
                                    <th class="text-left">
                                        Title
                                    </th>
                                    <th class="text-left">
                                        Project
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in pending_tasks.data"
                                    :key="item.name"
                                >
                                    <td class="text-left">{{ item.title }}</td>
                                    <td class="text-left">
                                        <router-link :to="{ name: 'project', params: { identifier: item.project.identifier } }">{{ item.project.name }}</router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card-item>
                </v-sheet>
            </v-col>
            <v-col
                cols="12"
                sm="4"
            >
                <v-sheet
                    class="text-center ma-2"
                    elevation="4"
                    min-height="250"
                    rounded
                >
                    <v-card-text class="text-h6">Lowest Workload</v-card-text>
                    <v-card-item>
                        <v-table
                            density="compact"
                            :loading="pending_tasks.loading"
                        >
                            <thead>
                            <tr>
                                <th class="text-left">
                                    Title
                                </th>
                                <th class="text-left">
                                    Project
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in pending_tasks.data"
                                :key="item.name"
                            >
                                <td class="text-left">{{ item.title }}</td>
                                <td class="text-left">
                                    <router-link :to="{ name: 'project', params: { identifier: item.project.identifier } }">{{ item.project.name }}</router-link>
                                </td>
                            </tr>
                            </tbody>
                        </v-table>
                    </v-card-item>
                </v-sheet>
            </v-col>
        </v-row>
    </v-layout>
</template>

<script>
export default {
    name: "dashboard",
    data() {
        return {
            pending_tasks: {
                loading: 'cyan',
                data: []
            },
        }
    },
    mounted() {
        this.getPendingTasks();
    },
    methods: {
        async getPendingTasks() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.get('/api/pending_tasks/').then(({data}) => {
                this.pending_tasks.loading = false;
                this.pending_tasks.data = data;
            }).catch(() => {
                this.$emit('show-alert', { type: 'error', text: 'Have an error when try to get pending tasks data.' });
            });
        },
    },
}
</script>
