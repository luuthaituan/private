<template>
    <v-row no-gutters>
        <v-toolbar>
            <template v-slot:prepend>
                <v-btn icon="mdi-arrow-left" :to="{ name: 'projects' }"></v-btn>
            </template>
            <v-btn icon="mdi-checkbox-marked-circle-plus-outline" class="ms-5"
                title="Add New"
                @click="newDialog = true"
            ></v-btn>
            <v-btn icon="mdi-reload" v-bind:class="{ syncing: syncing }" class="ms-5 sync-jira"
               title="Sync"
               @click="reload"
            ></v-btn>
            <v-divider
                class="mx-3 align-self-center"
                length="24"
                thickness="2"
                vertical
            ></v-divider>
            <v-btn icon="mdi-account-multiple-plus-outline"
               @click="allocateDialog = true"
            ></v-btn>
            <v-divider
                class="mx-3 align-self-center"
                length="24"
                thickness="2"
                vertical
            ></v-divider>
            <v-label>View Type: </v-label>
            <v-btn icon="mdi-account-group-outline" class="btn-view-type" v-bind:class="{'active': view_type === 'resources'}" @click="view_type = 'resources'"></v-btn>
            <v-btn icon="mdi-file-tree" class="btn-view-type" v-bind:class="{'active': view_type === 'tasks'}" @click="view_type = 'tasks'"></v-btn>
        </v-toolbar>
        <template v-if="view_type === 'resources'">
            <ResourcesView :tasks="tasks" :allocated_resources="allocated_resources" @on-task-dblclick="onTaskDblClick"/>
        </template>
        <template v-else-if="view_type === 'tasks'">
            <TasksView :tasks="tasks" @on-task-dblclick="onTaskDblClick"/>
        </template>
        <v-dialog
            v-model="newDialog"
            max-width="600px"
            v-bind:no-click-animation="true"
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Task Information</span>
                </v-card-title>

                <v-card-text>
                    <v-form v-model="valid" ref="form">
                        <v-container>
                            <v-row>
                                <v-text-field
                                    label="Title"
                                    v-model="editedItem.title"
                                    :rules="rules.title"
                                    required
                                ></v-text-field>
                            </v-row>
                            <v-row>
                                <v-text-field
                                    label="Jira ID"
                                    v-model="editedItem.jira_id"
                                ></v-text-field>
                            </v-row>
                            <v-row>
                                <v-menu v-model="startDateCalendar"
                                    :close-on-content-click="false"
                                    transition="scale-transition"
                                    offset-y
                                    full-width
                                >
                                    <template v-slot:activator="{ props }">
                                        <v-text-field
                                            label="Start Date"
                                            placeholder="YYYY-MM-DD"
                                            v-model="editedItem.start_date"
                                            v-bind="props"
                                            prepend-inner-icon="mdi-calendar-blank-outline"
                                            readonly
                                        >
                                        </v-text-field>
                                    </template>
                                    <v-date-picker
                                        v-model="editedItem.start_date"
                                        @update:modelValue="startDateCalendar = false; formatStartDate()"
                                        hide-header
                                    ></v-date-picker>
                                </v-menu>
                            </v-row>
                            <v-row>
                                <span class="text-subtitle-1">ETA</span>
                            </v-row>
                            <v-row v-for="(child, index) in editedItem.children">
                                <v-row>
                                    <v-col cols="12" sm="4">
                                        <v-select
                                            label="Type"
                                            v-model="child.title"
                                            :items="eta_types"
                                            item-title="name"
                                            item-value="id"
                                            :rules="rules.type"
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="2">
                                        <v-text-field
                                            label="Duration"
                                            v-model="child.duration"
                                            :rules="rules.duration"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="2">
                                        <v-text-field
                                            label="Progress"
                                            v-model="child.progress"
                                            :rules="rules.progress"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="3">
                                        <v-select
                                            label="Assignee(s)"
                                            v-model="child.assignees"
                                            :items="getAllocatedResources()"
                                            :item-props="resourceProps"
                                            :rules="rules.assignees"
                                            item-title="name"
                                            item-value="id"
                                            multiple
                                            chips
                                            required
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="1">
                                        <v-icon
                                            size="small"
                                            icon="mdi-delete"
                                            @click="removeETA(index)"
                                        ></v-icon>
                                    </v-col>
                                </v-row>
                            </v-row>
                            <v-row class="text-center">
                                <v-col>
                                    <v-btn icon="mdi-plus" size="small" @click="addETA"></v-btn>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="newDialog = false; resetEditedItem();"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        v-if="editedItem.id !== 0"
                        color="red"
                        variant="text"
                        @click="deleteDialog = true"
                    >
                        Delete
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
                    <v-btn color="blue-darken-1" variant="text" @click="deleteDialog = false">Cancel</v-btn>
                    <v-btn color="blue-darken-1" variant="text" @click="deleteDialog = false; remove()">OK</v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="allocateDialog"
          max-width="500px"
          v-bind:no-click-animation="true"
          persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Allocate</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-select
                                label="Resource(s)"
                                v-model="allocateResources"
                                :items="getAvailableResources()"
                                :item-props="resourceProps"
                                item-title="name"
                                item-value="id"
                                multiple
                                chips
                            ></v-select>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="allocateDialog = false"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="allocate"
                    >
                        Save
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<script>
import {reactive} from 'vue'
import {useRoute} from "vue-router";
import ResourcesView from "./gantt-charts/ResourcesView.vue";
import TasksView from "./gantt-charts/TasksView.vue";
import _ from "lodash";
import dayjs from "dayjs";

export default {
    name: "project",
    components: {ResourcesView, TasksView},
    async setup(props, context) {
        const route = useRoute();
        await axios.get('/sanctum/csrf-cookie');
        const identifier = route.params.identifier;
        let project = {},
            tasks = [],
            allocated_resources = [],
            available_resources = [];

        await axios.get('/api/gantt/' + identifier).then(({data}) => {
            tasks = data.tasks;
            project = data.project;
            allocated_resources = data.allocated_resources;
            available_resources = data.available_resources;
        }).catch(() => {
            context.emit('show-alert', { type: 'error', text: 'Have an error when try to get project data.' });
        });
        document.title = project.name;

        return reactive({
            project: { id: project.id, name: project.name },
            tasks,
            allocated_resources,
            available_resources,
        })
    },
    data() {
        return {
            newDialog: false,
            deleteDialog: false,
            allocateDialog: false,
            startDateCalendar: false,
            valid: false,
            syncing: false,
            view_type: 'resources',
            eta_types: [
                {
                    'id': 'conduct_brd',
                    'name': 'Conduct BRD'
                },
                {
                    'id': 'dev',
                    'name': 'Develop'
                },
                {
                    'id': 'test',
                    'name': 'Test'
                },
            ],
            editedItem: {
                id: 0,
                title: '',
                jira_id: null,
                start_date: null,
                children: [],
                project_id: this.project.id,
            },
            defaultItem: {
                id: 0,
                title: '',
                jira_id: null,
                start_date: null,
                children: [],
                project_id: this.project.id,
            },
            defaultETA: {
                id: 0,
                title: '',
                duration: 0,
                progress: 0,
                assignees: [],
            },
            allocateResources: [],
            rules: {
                title: [
                    (v) => !!v || 'Title is required',
                ],
                duration: [
                    (v) => /^[0-9]+$/.test(v) || 'Duration must be valid',
                    (v) => v > 0 || 'Duration must be greater than 0',
                ],
                progress: [
                    (v) => /^[0-9]+$/.test(v) || 'Progress must be valid',
                ],
                type: [
                    (v) => v.length > 0 || 'Type is required',
                ],
                assignees: [
                    (v) => v.length > 0 || 'Assignee(s) is required',
                ]
            },
        }
    },
    methods: {
        onTaskDblClick(task) {
            this.edit(_.cloneDeep(this.tasks[task.real_id]));
        },
        addETA() {
            this.editedItem.children.push(_.cloneDeep(this.defaultETA));
        },
        removeETA(index) {
            this.editedItem.children.splice(index, 1);
        },
        formatStartDate() {
            if (!this.editedItem.start_date) {
                return '';
            }

            this.editedItem.start_date = dayjs(this.editedItem.start_date).format('YYYY-MM-DD');
        },
        resourceProps (item) {
            return {
                title: item.account,
                subtitle: item.name,
            }
        },
        getAvailableResources() {
            return _.values(this.available_resources);
        },
        getAllocatedResources() {
            return _.values(this.allocated_resources);
        },
        edit(item) {
            this.editedItem = item;
            this.newDialog = true;
        },
        async save() {
            this.$refs.form.validate();

            if (!this.valid) {
                return;
            }

            if (this.editedItem.id !== 0) {
                await this.update();
            } else {
                await this.add();
            }
        },
        async add() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.post('/api/tasks/', this.editedItem).then(({data}) => {
                this.tasks[data.id] = data;
                this.newDialog = false;
                this.resetEditedItem();
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: 'Have an error when try to add task to this project.' });
            });
        },
        async update() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.put('/api/tasks/' + this.editedItem.id, this.editedItem).then(({data}) => {
                this.tasks[this.editedItem.id] = data;
                this.newDialog = false;
                this.resetEditedItem();
            }).catch(() => {
                this.$emit('show-alert', { type: 'error', text: 'Have an error when try to update task information.' });
            });
        },
        async remove() {
            await axios.get('/sanctum/csrf-cookie');
            await axios.delete('/api/tasks/' + this.editedItem.id).then(({data}) => {
                if (data.success) {
                    this.$emit('show-alert', { type: 'success', text: data.message });
                    delete this.tasks[this.editedItem.id];
                }
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: response.data.message });
            }).finally(() => {
                this.resetEditedItem();
                this.newDialog = false;
            });
        },
        async allocate() {
            let data = {
                project: this.project,
                resources: this.allocateResources,
            }

            await axios.get('/sanctum/csrf-cookie');
            await axios.post('/api/allocations/', data).then(({data}) => {
                if (data.success) {
                    _.each(this.allocateResources, (resource) => {
                        this.allocated_resources[resource] = this.available_resources[resource];
                        _.remove(this.available_resources, (item) => { return item.id === resource.id });
                    });
                    this.$emit('show-alert', { type: 'success', text: data.message });
                }

                this.allocateResources = [];
                this.allocateDialog = false;
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: response.data.message });
            });
        },
        resetEditedItem() {
            this.editedItem = Object.assign({}, this.defaultItem);
        },
        async reload() {
            this.syncing = true;
            await axios.get('/sanctum/csrf-cookie');
            await axios.put('/api/tasks/sync/' + this.project.id).then(({data}) => {
                _.each(data, (task) => {
                    this.tasks[task.id] = task;
                });
            }).catch(({response}) => {
                this.$emit('show-alert', { type: 'error', text: response.data.message });
            });

            this.syncing = false;
        },
    }
}
</script>

<style>
.btn-view-type,
.gstc__chart-timeline-items-row-item {
    border-radius: 4px;
}

.btn-view-type.active {
    background: rgba(0, 0, 0, 0.1);
}

.gantt-wrapper {
    width: 100%;
    min-height: calc(100% - 64px);
}

.sg-table-row .v-avatar {
    margin-right: 10px;
}

.sg-timeline-body {
    border-bottom: #efefef 1px solid;
}

.sg-table-row:last-child .sg-table-body-cell {
    border-bottom: 0;
}

.sg-task {
    border-radius: 6px !important;
    background-color: rgb(241, 137, 45) !important;
    background-size: auto auto !important;
    background-image: repeating-linear-gradient(135deg, transparent, transparent 10px, rgba(255,255,255,0.15) 10px, rgba(255,255,255,0.15) 20px) !important;
}

.sg-task.task-conduct_brd {
    background-color: #e74c3c !important;
}

.sg-task.task-test {
    background-color: rgb(14, 172, 81) !important;
}

.sg-task:hover {
    background-color: rgb(241, 137, 45) !important;
    background-size: auto auto !important;
    background-image: repeating-linear-gradient(135deg, transparent, transparent 10px, rgba(255,255,255,0.15) 10px, rgba(255,255,255,0.15) 20px) !important;
}

.sg-task.task-conduct_brd:hover {
    background-color: #e74c3c !important;
}

.sg-task.task-test:hover {
    background-color: rgb(14, 172, 81) !important;
}

.sg-task.sg-task-selected {
    box-shadow: 0 0 1px 2px #0077c0;
}

.sg-task-background {
    background-color: rgb(241, 137, 45) !important;
    border-radius: 6px;
}

.sg-task.task-conduct_brd .sg-task-background {
    background-color: #e74c3c !important;
}

.sg-task.task-test .sg-task-background {
    background-color: rgb(14, 172, 81) !important;
}

.tasks-view .sg-task-content {
    padding-left: 0 !important;
}

.sg-table-header,
.sg-header {
    background: rgb(249, 250, 251);
}

.sg-table-header-cell,
.sg-table-body-cell,
.column-header-cell {
    font-weight: 400 !important;
    color: rgb(116, 122, 129);
}

.sg-table-body-cell {
    color: rgb(96, 96, 96);
}

.sg-table-body-cell .task-label a {
    color: rgb(96, 96, 96);
}

.column-header-row:nth-child(3),
.column-header-row:nth-child(4) {
    height: 24px !important;
}

.column-header-row:nth-child(3) .column-header-cell {
    border-bottom: 0;
    font-size: 18px;
    font-weight: 400;
}

.column-header-row:nth-child(4) .column-header-cell {
    font-size: 13px;
    line-height: 13px;
    font-weight: 300 !important;
}

.sync-jira.syncing .mdi-reload {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 120px;
    height: 120px;
    margin:-60px 0 0 -60px;
    -webkit-animation:spin 4s linear infinite;
    -moz-animation:spin 4s linear infinite;
    animation:spin 4s linear infinite;
}

@-moz-keyframes spin {
    100% { -moz-transform: rotate(360deg); }
}
@-webkit-keyframes spin {
    100% { -webkit-transform: rotate(360deg); }
}
@keyframes spin {
    100% {
        -webkit-transform: rotate(360deg);
        transform:rotate(360deg);
    }
}

.task-label {
    line-height: 22px;
}

.task-label a {
    color: white;
}

.task-description {
    font-size: 11px;
    line-height: 11px;
}

.avatars-group.stacked {
    display: flex;
    flex-direction: row;
    direction: ltr;
    max-width: 100%;
    overflow: hidden;
    overflow-x: auto;
    white-space: nowrap;
}

.avatars-group.stacked > * {
    margin-right: -8px;
}

.avatars-group.stacked > *:last-of-type {
    padding-right: 16px;
}

.avatars-group__item {
    cursor: default;
    transition: all .1s ease-out;
}

.avatars-group__item:hover {
    transform: translateY(-4px);
    z-index: 1;
}

.avatars-group .v-avatar {
    box-shadow: 0 0 0 2px #fff inset;
}

.avatars-group .v-avatar img {
    padding: 2px;
}
</style>
