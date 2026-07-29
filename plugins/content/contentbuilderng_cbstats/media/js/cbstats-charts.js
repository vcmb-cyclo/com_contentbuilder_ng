(() => {
  'use strict';

  const valueLabels = {
    id: 'cbstatsValueLabels',
    afterDatasetsDraw(chart) {
      const dataset = chart.data.datasets[0];
      const meta = chart.getDatasetMeta(0);

      if (!dataset || !meta || meta.hidden) {
        return;
      }

      const context = chart.ctx;
      context.save();
      context.fillStyle = '#495057';
      context.font = '600 12px system-ui, sans-serif';
      context.textAlign = 'center';
      context.textBaseline = 'bottom';

      meta.data.forEach((element, index) => {
        const value = dataset.data[index];
        const position = element.tooltipPosition();
        context.fillText(String(value), position.x, position.y - 7);
      });

      context.restore();
    },
  };

  const initialise = (root) => {
    if (root.dataset.cbstatsInitialised === 'true' || typeof window.Chart !== 'function') {
      return;
    }

    let payload;

    try {
      payload = JSON.parse(root.dataset.cbstatsChart || '{}');
    } catch (error) {
      return;
    }

    const items = Array.isArray(payload.items) ? payload.items : [];
    const output = String(payload.type || '');
    const canvas = root.querySelector('canvas');

    if (!canvas || items.length === 0 || !['histogram', 'line', 'radar'].includes(output)) {
      return;
    }

    const labels = items.map((item) => String(item.label || ''));
    const values = items.map((item) => Number(item.value || 0));
    const colors = items.map((item) => String(item.color || '#2a69b8'));
    const type = output === 'histogram' ? 'bar' : output;
    const dataset = {
      data: values,
      borderColor: output === 'histogram' ? colors : '#2a69b8',
      backgroundColor: output === 'radar' ? 'rgba(42, 105, 184, 0.22)' : colors,
      borderWidth: 2,
      pointBackgroundColor: colors,
      pointBorderColor: '#fff',
      pointRadius: output === 'histogram' ? 0 : 4,
      tension: output === 'line' ? 0.2 : 0,
      fill: output === 'radar',
    };
    const cartesian = output !== 'radar';

    new window.Chart(canvas, {
      type,
      data: { labels, datasets: [dataset] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        layout: { padding: { top: 22, right: 12, bottom: 4, left: 8 } },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => `${context.label}: ${context.formattedValue}`,
            },
          },
        },
        scales: cartesian
          ? {
              x: {
                ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 },
                grid: { display: false },
              },
              y: { beginAtZero: true, ticks: { precision: 0 } },
            }
          : {
              r: { beginAtZero: true, ticks: { precision: 0, backdropColor: 'transparent' } },
            },
      },
      plugins: [valueLabels],
    });

    root.dataset.cbstatsInitialised = 'true';
  };

  const initialiseAll = () => {
    document.querySelectorAll('[data-cbstats-chart]').forEach(initialise);
  };

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', initialiseAll, { once: true })
    : initialiseAll();
})();
