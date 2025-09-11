import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

// Expose Chart globally for legacy inline scripts that expect window.Chart
// This keeps views working while we gradually refactor inline scripts into modules.
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
window.Chart = Chart;

export default Chart;

