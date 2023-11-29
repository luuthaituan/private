<template>
    <v-row no-gutters>
        <v-toolbar title="Holidays">
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
                <v-col cols="4" v-for="(n, i) in 12">
                    <v-card>
                        <v-card-title class="text-center">{{months[i]}}</v-card-title>
                        <v-card-item>
                            <v-date-picker
                                hide-header
                                v-model="days"
                                @update:modelValue="update"
                                :month="i"
                                width="100%"
                                multiple
                            ></v-date-picker>
                        </v-card-item>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </v-row>
</template>

<script>
import _ from "lodash";
import dayjs from "dayjs";
import {reactive} from "vue";

const API_URL = '/api/holidays/';

export default {
    name: 'holidays',
    async setup(props, context) {
        let days = [];

        await axios.get('/sanctum/csrf-cookie');
        await axios.get(API_URL).then(({data}) => {
            _.each(data, (day) => {
                days.push(dayjs(day));
            });
        }).catch(() => {
            context.emit('show-alert', { type: 'error', text: 'Have an error when try to get holiday data.' });
        });

        return reactive({
            days
        });
    },
    data() {
        return {
            months: [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
            ],
        }
    },
    methods: {
        async save() {
            let days = [];
            _.each(this.days, (day) => {
                days.push(dayjs(day).format('YYYY-MM-DD'));
            });

            await axios.get('/sanctum/csrf-cookie');
            await axios.post(API_URL, { 'days': days }).then(({data}) => {
                if (data.success === true) {
                    let days = [];
                    _.each(data.days, (day) => {
                        days.push(dayjs(day));
                    });
                    this.days = days;
                    this.$emit('show-alert', { type: 'success', text: data.message });
                } else {
                    this.$emit('show-alert', { type: 'error', text: data.message });
                }
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: response.data.message });
            });
        },
        update(days) {
            this.days = days;
        },
    },
}
</script>

<style>
.v-date-picker-controls {
    display: none !important;
}
</style>
