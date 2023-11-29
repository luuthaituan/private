<template>
    <div class="gantt-wrapper tasks-view" id="gantt" ref="gantt"></div>
</template>
<script>
import {getCurrentInstance, onMounted, watch} from "vue";
import {SvelteGantt} from 'svelte-gantt';
import defaults from "./config";
import sha256 from 'crypto-js/sha256';
import _ from 'lodash';
import dayjs from "dayjs";

export default {
    name: 'gantt-chart-tasks-view',
    props: ['tasks'],
    setup(props, context) {
        let gantt;

        const options = _.defaultsDeep({
            from: dayjs().startOf('month').valueOf(),
            to: dayjs().endOf('month').valueOf(),
            rows: getRows(props.tasks),
            tasks: getTasks(props.tasks),
            headers: [
                {
                    format: "MMMM",
                    unit: 'month',
                    sticky: true,
                },
                {
                    format: '[W]W',
                    unit: 'week',
                    sticky: true,
                },
                {
                    format: "D",
                    unit: 'day',
                    sticky: true,
                },
                {
                    format: "ddd",
                    unit: 'day',
                    sticky: true,
                },
            ],
            taskElementHook: (node, task) =>  {
                node.addEventListener('dblclick', () => context.emit('on-task-dblclick', task));
            },
            tableWidth: 400,
            tableHeaders: [
                {
                    title: 'Task(s)',
                }
            ],
            layout: 'pack',
        }, defaults);

        onMounted(() => {
            gantt = new SvelteGantt({
                // target a DOM element
                target: getCurrentInstance().ctx._.refs.gantt,
                // svelte-gantt options
                props: options,
            });
        });

        watch(props.tasks, (newValue) => {
            gantt.$set({
                rows: getRows(newValue),
                tasks: getTasks(newValue),
            });
        });

        function getRows(rows) {
            let data = [];

            _.each(rows, (task) => {
                if (task === undefined) {
                    return;
                }

                let headerHtml = `<div class="task-text">`;
                if (task.jira_id) {
                    headerHtml += `<div class="task-label"><a target="_blank" href="https://jira.smartosc.com/browse/${task.jira_id}" title="${task.title}">${task.title}</a></div>`
                } else {
                    headerHtml += `<div class="task-label">${task.title}</div>`;
                }
                if (task.status) {
                    headerHtml += `<div class="task-description">Status: ${task.status}</div>`
                }
                headerHtml += '</div>';

                data.push({
                    id: `task_${task.id}`,
                    enableDragging: false,
                    headerHtml: headerHtml,
                });
            });

            return data;
        }

        function getTasks(tasks) {
            let data = [];

            _.each(tasks, (task) => {
                if (task === undefined) {
                    return;
                }

                _.each(task.children, function (child) {
                    let startDate = dayjs(child.start_date).startOf('day');
                    let endDate = dayjs(child.end_date).endOf('day');

                    let html = `<div class="task-assignees"><section class="avatars-group pa-2 stacked">`;
                    _.each(child.assignees, (resource) => {
                        let avatar = resource.avatar ? resource.avatar : `https://gravatar.com/avatar/${sha256(resource.email).toString()}`;
                        html += `<div class="avatars-group__item">
                            <div class="v-avatar v-avatar--density-default v-avatar--size-small">
                                <div class="v-responsive v-img">
                                    <img class="v-img__img v-img__img--cover" src="${avatar}" title="${`${resource.name} (${resource.account})`}" />
                                </div>
                            </div>
                        </div>`;
                    });
                    html += '</section></div>';
                    html += `<div class="task-text">`;
                    if (task.jira_id) {
                        html += `<div class="task-label"><a target="_blank" href="https://jira.smartosc.com/browse/${task.jira_id}" title="${task.title}">${task.title}</a></div>`
                    } else {
                        html += `<div class="task-label">${task.title}</div>`;
                    }
                    html += `</div>`;

                    data.push({
                        real_id: task.id,
                        id: `task_${child.id}`,
                        resourceId: `task_${task.id}`,
                        amountDone: child.progress,
                        from: startDate.valueOf(),
                        to: endDate.valueOf(),
                        enableDragging: false,
                        html: html,
                        classes: `task-${child.title}`
                    });
                });
            });

            return data;
        }

        function addEventListener(element, data) {
            context.emit('add-event-listener', element, data);
        }
    },
}
</script>
