<template>
  <div class="price-chart">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { Chart, LineController, LineElement, PointElement, LinearScale, Title, CategoryScale, Tooltip } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, Title, CategoryScale, Tooltip);

export default {
  name: 'PriceChart',
  props: {
    data: {
      type: Array,
      required: true,
    },
    labels: {
      type: Array,
      required: true,
    }
  },
  setup(props) {
    const chartCanvas = ref(null);

    onMounted(() => {
      const ctx = chartCanvas.value.getContext('2d');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: props.labels,
          datasets: [{
            label: 'Ticket Prices',
            data: props.data,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                      label += ': ';
                  }
                  if (context.parsed.y !== null) {
                      label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                  }
                  return label;
                }
              }
            }
          }
        }
      });
    });

    return { chartCanvas };
  }
}
</script>

<style scoped>
.price-chart {
  position: relative;
  height: 400px;
  width: 100%;
}
</style>

