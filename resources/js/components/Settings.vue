<template>
    <v-toolbar title="Settings">
        <v-spacer></v-spacer>
        <v-btn
            class="text-none ms-4 text-white"
            color="blue-darken-4"
            prepend-icon="mdi-check"
            rounded="0"
            variant="flat"
            @click="save"
        >
            Save
        </v-btn>
    </v-toolbar>
    <v-container>
        <v-row>
            <v-expansion-panels variant="accordion">
                <v-expansion-panel v-for="setting in settings">
                    <v-expansion-panel-title>
                        <span class="text-subtitle-1">{{setting.title}}</span>
                    </v-expansion-panel-title>
                    <v-expansion-panel-text>

                    </v-expansion-panel-text>
                </v-expansion-panel>
            </v-expansion-panels>
        </v-row>
    </v-container>
</template>

<script>
export default {
    name: "settings",
    setup(props, context) {
        let settings = [
            {
                title: 'Jira Integration',
                fields: [
                    {
                        label: 'Enable',
                        type: 'select',
                        options: [
                            'No',
                            'Yes',
                        ],
                    },
                ],
            },
        ];

        return {
            settings
        }
    },
    data() {
        return {

        }
    },
    methods: {
        async save() {},
    },
}
</script>
