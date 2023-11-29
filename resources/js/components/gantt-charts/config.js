import {MomentSvelteGanttDateAdapter, SvelteGanttTable, SvelteGanttDependencies} from 'svelte-gantt';
import moment from "moment";

export default {
    dateAdapter: new MomentSvelteGanttDateAdapter(moment),
    ganttTableModules: [SvelteGanttTable],
    ganttBodyModules: [SvelteGanttDependencies],
    fitWidth: true,
    rowHeight: 64,
    columnUnit: 'day',
    highlightedDurations: {unit: 'day', fractions: [0, 6]},
    highlightColor: '#efefef',
    columnOffset: 1,
    columnStrokeColor: '#efefef',
    columnStrokeWidth: 1,
    useCanvasColumns: false,
}
