import Alpine from 'alpinejs';
import {
  ArcElement,
  BarController,
  BarElement,
  CategoryScale,
  Chart,
  DoughnutController,
  Legend,
  LinearScale,
  LineController,
  LineElement,
  PointElement,
  Title,
  Tooltip,
} from 'chart.js';

// Register Chart.js components
Chart.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  DoughnutController,
  LineController,
  BarController
);

document.addEventListener('alpine:init', () => {
  Alpine.data('chartComponent', (config = {}) => ({
    chart: null,
    chartConfig: {
      type: 'line',
      data: {
        labels: [],
        datasets: [],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          intersect: false,
          mode: 'index',
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
          },
          tooltip: {
            enabled: true,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: '#333',
            borderWidth: 1,
          },
        },
        scales: {
          x: {
            display: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.1)',
            },
          },
          y: {
            display: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.1)',
            },
          },
        },
      },
      ...config,
    },

    init() {
      this.$nextTick(() => {
        this.initializeChart();
      });
    },

    initializeChart() {
      const canvas = this.$el.querySelector('canvas');
      if (!canvas) {
        console.error('Chart canvas element not found');
        return;
      }

      const ctx = canvas.getContext('2d');
      this.chart = new Chart(ctx, this.chartConfig);
    },

    updateChart(newData) {
      if (!this.chart) return;

      this.chart.data = { ...this.chart.data, ...newData };
      this.chart.update('none');
    },

    updateDatasets(datasets) {
      if (!this.chart) return;

      this.chart.data.datasets = datasets;
      this.chart.update('none');
    },

    addDataPoint(label, data) {
      if (!this.chart) return;

      this.chart.data.labels.push(label);
      this.chart.data.datasets.forEach((dataset, index) => {
        dataset.data.push(data[index] || 0);
      });

      // Keep only last 20 data points
      if (this.chart.data.labels.length > 20) {
        this.chart.data.labels.shift();
        this.chart.data.datasets.forEach(dataset => {
          dataset.data.shift();
        });
      }

      this.chart.update('none');
    },

    setTheme(isDark) {
      if (!this.chart) return;

      const textColor = isDark ? '#e5e7eb' : '#374151';
      const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

      this.chart.options.plugins.legend.labels.color = textColor;
      this.chart.options.scales.x.ticks.color = textColor;
      this.chart.options.scales.y.ticks.color = textColor;
      this.chart.options.scales.x.grid.color = gridColor;
      this.chart.options.scales.y.grid.color = gridColor;

      this.chart.update('none');
    },

    destroy() {
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }
    },
  }));

  // Real-time stats chart
  Alpine.data('statsChart', () => ({
    ...Alpine.raw(Alpine.data('chartComponent')({
      type: 'line',
      data: {
        labels: [],
        datasets: [
          {
            label: 'Platform Status',
            data: [],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
          },
          {
            label: 'Active Users',
            data: [],
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 750,
          easing: 'easeInOutQuart',
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
          },
        },
      },
    })),
  }));

  // Queue performance chart
  Alpine.data('queueChart', () => ({
    ...Alpine.raw(Alpine.data('chartComponent')({
      type: 'bar',
      data: {
        labels: ['Processed', 'Failed', 'Pending'],
        datasets: [
          {
            label: 'Jobs',
            data: [0, 0, 0],
            backgroundColor: [
              'rgba(34, 197, 94, 0.8)',
              'rgba(239, 68, 68, 0.8)',
              'rgba(245, 158, 11, 0.8)',
            ],
            borderColor: [
              'rgb(34, 197, 94)',
              'rgb(239, 68, 68)',
              'rgb(245, 158, 11)',
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    })),
  }));

  // Error distribution chart
  Alpine.data('errorChart', () => ({
    ...Alpine.raw(Alpine.data('chartComponent')({
      type: 'doughnut',
      data: {
        labels: ['Network Errors', 'Server Errors', 'Client Errors', 'Other'],
        datasets: [
          {
            data: [0, 0, 0, 0],
            backgroundColor: [
              'rgba(239, 68, 68, 0.8)',
              'rgba(245, 158, 11, 0.8)',
              'rgba(59, 130, 246, 0.8)',
              'rgba(107, 114, 128, 0.8)',
            ],
            borderColor: [
              'rgb(239, 68, 68)',
              'rgb(245, 158, 11)',
              'rgb(59, 130, 246)',
              'rgb(107, 114, 128)',
            ],
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
          },
        },
      },
    })),
  }));
});
