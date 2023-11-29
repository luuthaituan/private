<template>
    <v-card>
        <v-card-title>
            <v-text-field
                v-model="search"
                label="Search"
                single-line
                hide-details
            ></v-text-field>
        </v-card-title>
        <v-card-item>
            <v-data-table
                :headers="headers"
                :items="resources"
                :search="search"
                :loading="loading"
                sticky
            >
                <template v-slot:top>
                    <v-toolbar
                        flat
                    >
                        <v-spacer></v-spacer>
                        <v-dialog
                            v-model="newDialog"
                            max-width="500px"
                        >
                            <template v-slot:activator="{ props }">
                                <v-btn
                                    color="primary"
                                    dark
                                    v-bind="props"
                                >
                                    Add New
                                </v-btn>
                            </template>
                            <v-card>
                                <v-card-title>
                                    <span class="text-h5">Resource Information</span>
                                </v-card-title>

                                <v-card-text>
                                    <v-form v-model="valid" ref="form">
                                        <v-container>
                                            <v-row>
                                                <v-text-field
                                                    v-model="editedItem.account"
                                                    :rules="accountRules"
                                                    label="Account"
                                                ></v-text-field>
                                            </v-row>
                                            <v-row>
                                                <v-text-field
                                                    v-model="editedItem.name"
                                                    :rules="nameRules"
                                                    label="Name"
                                                ></v-text-field>
                                            </v-row>
                                            <v-row>
                                                <v-text-field
                                                    v-model="editedItem.email"
                                                    :rules="emailRules"
                                                    label="Email"
                                                ></v-text-field>
                                            </v-row>
                                        </v-container>
                                    </v-form>
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn
                                        color="blue-darken-1"
                                        variant="text"
                                        @click="reset(); newDialog = false"
                                    >
                                        Cancel
                                    </v-btn>
                                    <v-btn
                                        color="blue-darken-1"
                                        variant="text"
                                        @click="save"
                                    >
                                        Save
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                        <v-dialog v-model="deleteDialog" max-width="500px">
                            <v-card>
                                <v-card-title class="text-body-1">Are you sure you want to delete this item?</v-card-title>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue-darken-1" variant="text" @click="reset(); deleteDialog = false;">Cancel</v-btn>
                                    <v-btn color="blue-darken-1" variant="text" @click="deleteItem">OK</v-btn>
                                    <v-spacer></v-spacer>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-icon
                        size="small"
                        class="me-2"
                        @click="editItem(item); newDialog = true;"
                    >
                        mdi-pencil
                    </v-icon>
                    <v-icon
                        size="small"
                        @click="editItem(item); deleteDialog = true;"
                    >
                        mdi-delete
                    </v-icon>
                </template>
            </v-data-table>
        </v-card-item>
    </v-card>
</template>

<script>
import _ from 'lodash';
const API_URL = '/api/resources';

export default {
    name: "resources",
    data () {
        return {
            newDialog: false,
            deleteDialog: false,
            valid: false,
            loading: 'cyan',
            editedIndex: -1,
            editedItem: {
                id: 0,
                account: '',
                name: '',
                email: '',
            },
            defaultItem: {
                id: 0,
                account: '',
                name: '',
                email: '',
            },
            search: '',
            headers: [
                { key: 'account', title: 'Account' },
                { key: 'name', title: 'Name', sortable: false },
                { key: 'email', title: 'Email', sortable: false },
                { key: 'created_at', title: 'Created At' },
                { key: 'updated_at', title: 'Updated At' },
                { key: 'actions', title: 'Actions', sortable: false, align: 'end' },
            ],
            resources: [],
            accountRules: [
                (v) => !!v || 'Account is required',
            ],
            nameRules: [
                (v) => !!v || 'Name is required',
            ],
            emailRules: [
                (v) => !!v || 'E-mail is required',
                (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail must be valid'
            ],
        }
    },
    mounted() {
        this.fetch();
    },
    methods: {
        async fetch() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.get(API_URL).then(({data}) => {
                this.loading = false;
                this.resources = data;
            }).catch(() => {
                this.$emit('show-alert', { type: 'error', text: 'Have an error when try to get resources data.' });
            });
        },
        editItem(item) {
            this.editedIndex = _.findIndex(this.resources, item);
            this.editedItem = Object.assign({}, item);
        },
        async deleteItem() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.delete(API_URL + '/' + this.editedItem.id).then(({data}) => {
                if (data.success) {
                    this.$emit('show-alert', { type: 'success', text: data.message });
                    _.remove(this.resources, (item) => { return item.id === this.editedItem.id });
                }
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: response.data.message });
            }).finally(() => {
                this.reset();
                this.deleteDialog = false;
            });
        },
        async save() {
            this.$refs.form.validate();

            if (!this.valid) {
                return;
            }

            if (this.editedIndex > -1) {
                await this.update();
            } else {
                await this.add();
            }
        },
        async add() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.post(API_URL, this.editedItem).then(({data}) => {
                this.resources.push(data);
                this.newDialog = false;
                this.reset();
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: response.data.message });
            });
        },
        async update() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.put(API_URL + '/' + this.editedItem.id, this.editedItem).then(({data}) => {
                Object.assign(this.resources[this.editedIndex], data);
                this.newDialog = false;
                this.reset();
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: response.data.message });
            });
        },
        reset() {
            this.editedItem = Object.assign({}, this.defaultItem);
            this.editedIndex = -1;
        }
    }
}
</script>
