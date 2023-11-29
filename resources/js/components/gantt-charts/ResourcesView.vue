<template>
    <div class="gantt-wrapper resources-view" id="gantt" ref="gantt"></div>
</template>
<script>
import {getCurrentInstance, onMounted, watch} from "vue";
import {SvelteGantt} from 'svelte-gantt';
import defaults from "./config";
import sha256 from 'crypto-js/sha256';
import _ from 'lodash';
import dayjs from "dayjs";
import isoWeek from "dayjs/plugin/isoWeek";
dayjs.extend(isoWeek);

export default {
    name: 'gantt-chart-resources-view',
    props: ['tasks', 'allocated_resources'],
    setup(props, context) {
        let gantt;

        const options = _.defaultsDeep({
            from: dayjs().startOf('isoWeek').valueOf(),
            to: dayjs().add(1, 'week').endOf('isoWeek').endOf('day').valueOf(),
            rows: getRows(props.allocated_resources),
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
                    format: "dddd",
                    unit: 'day',
                    sticky: true,
                },
            ],
            taskElementHook: (node, task) =>  {
                node.addEventListener('dblclick', () => context.emit('on-task-dblclick', task));
            },
            tableWidth: 300,
            tableHeaders: [
                {
                    title: 'Resource(s)',
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

        watch(props.allocated_resources, (newValue) => {
            gantt.$set({
                rows: getRows(newValue)
            });
        });

        watch(props.tasks, (newValue) => {
            gantt.$set({
                tasks: getTasks(newValue)
            });
        });

        function getRows(rows) {
            let data = [];

            _.each(rows, (resource) => {
                if (resource === undefined) {
                    return;
                }

                let avatar = resource.avatar ? resource.avatar : `https://gravatar.com/avatar/${sha256(resource.email).toString()}`;

                data.push({
                    id: `resource_${resource.id}`,
                    enableDragging: false,
                    headerHtml: `<div class="v-avatar v-avatar--density-default v-avatar--size-small">
                        <div class="v-responsive v-img">
                            <img class="v-img__img v-img__img--cover" src="${avatar}" alt="${`${resource.name} (${resource.account})`}" />
                        </div>
                    </div>${resource.name} (${resource.account})`,
                });
            });

            return data;
        }

        function getTasks(tasks) {
            let data = [];

            _.each(tasks, (task) => {
                if (task === undefined || task.children.length === 0) {
                    return;
                }

                _.each(task.children, function (child) {
                    let startDate = dayjs(child.start_date).startOf('day');
                    let endDate = dayjs(child.end_date).endOf('day');

                    _.each(child.assignees, function (assignee) {
                        let html = `<div class="task-text">`;
                        if (task.jira_id) {
                            html += `<div class="task-label"><a target="_blank" href="https://jira.smartosc.com/browse/${task.jira_id}" title="${task.title}">${task.title}</a></div>`
                        } else {
                            html += `<div class="task-label">${task.title}</div>`;
                        }

                        if (task.status) {
                            html += `<div class="task-description">Status: ${task.status}</div>`;
                        }
                        html += '</div>';

                        data.push({
                            real_id: task.id,
                            id: `task_${assignee.id}_${child.id}`,
                            resourceId: `resource_${assignee.id}`,
                            amountDone: child.progress,
                            from: startDate.valueOf(),
                            to: endDate.valueOf(),
                            enableDragging: false,
                            html: html,
                            classes: `task-${child.title}`
                        });
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
