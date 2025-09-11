/**
 * D3.js Chart Components for HD Tickets Analytics Dashboard
 * 
 * Advanced data visualization components using D3.js for complex analytics
 * that complement the Chart.js visualizations.
 */

class D3AnalyticsCharts {
    constructor() {
        this.config = {
            colors: {
                primary: '#667eea',
                secondary: '#764ba2',
                success: '#2ecc71',
                warning: '#f39c12',
                danger: '#e74c3c',
                info: '#3498db',
                light: '#ecf0f1',
                dark: '#2c3e50'
            },
            margins: { top: 20, right: 20, bottom: 40, left: 60 },
            animation: { duration: 750, ease: d3.easeQuadOut }
        };
    }

    /**
     * Create a heatmap for price trends across platforms and time
     * 
     * @param {string} containerId - DOM element ID
     * @param {Array} data - Data array with platform, date, and price
     * @param {Object} options - Chart configuration options
     */
    createPriceHeatmap(containerId, data, _options = {}) {
        const container = d3.select(`#${containerId}`);
        container.selectAll('*').remove();

        const containerRect = container.node().getBoundingClientRect();
        const width = containerRect.width - this.config.margins.left - this.config.margins.right;
        const height = 400 - this.config.margins.top - this.config.margins.bottom;

        const svg = container
            .append('svg')
            .attr('width', width + this.config.margins.left + this.config.margins.right)
            .attr('height', height + this.config.margins.top + this.config.margins.bottom);

        const g = svg.append('g')
            .attr('transform', `translate(${this.config.margins.left},${this.config.margins.top})`);

        // Process data
        const platforms = [...new Set(data.map(d => d.platform))];
        const dates = [...new Set(data.map(d => d.date))].sort();
        
        // Create scales
        const xScale = d3.scaleBand()
            .domain(dates)
            .range([0, width])
            .padding(0.1);

        const yScale = d3.scaleBand()
            .domain(platforms)
            .range([0, height])
            .padding(0.1);

        const colorScale = d3.scaleSequential(d3.interpolateRdYlGn)
            .domain(d3.extent(data, d => d.price));

        // Create tooltip
        const tooltip = this.createTooltip();

        // Draw heatmap cells
        g.selectAll('.heatmap-cell')
            .data(data)
            .enter()
            .append('rect')
            .attr('class', 'heatmap-cell')
            .attr('x', d => xScale(d.date))
            .attr('y', d => yScale(d.platform))
            .attr('width', xScale.bandwidth())
            .attr('height', yScale.bandwidth())
            .attr('fill', d => colorScale(d.price))
            .attr('stroke', '#fff')
            .attr('stroke-width', 1)
            .style('opacity', 0)
            .on('mouseover', (event, d) => {
                tooltip.style('visibility', 'visible')
                    .html(`
                        <strong>${d.platform}</strong><br>
                        Date: ${d.date}<br>
                        Price: $${d.price.toFixed(2)}
                    `);
            })
            .on('mousemove', (event) => {
                tooltip.style('top', (event.pageY - 10) + 'px')
                    .style('left', (event.pageX + 10) + 'px');
            })
            .on('mouseout', () => {
                tooltip.style('visibility', 'hidden');
            })
            .transition()
            .duration(this.config.animation.duration)
            .style('opacity', 1);

        // Add axes
        const xAxis = d3.axisBottom(xScale)
            .tickFormat(d3.timeFormat('%m/%d'));

        const yAxis = d3.axisLeft(yScale);

        g.append('g')
            .attr('class', 'x-axis')
            .attr('transform', `translate(0,${height})`)
            .call(xAxis)
            .selectAll('text')
            .style('text-anchor', 'end')
            .attr('dx', '-.8em')
            .attr('dy', '.15em')
            .attr('transform', 'rotate(-45)');

        g.append('g')
            .attr('class', 'y-axis')
            .call(yAxis);

        // Add color legend
        this.addColorLegend(svg, colorScale, width, height);
    }

    /**
     * Create a network graph showing platform relationships
     * 
     * @param {string} containerId - DOM element ID
     * @param {Object} data - Graph data with nodes and links
     * @param {Object} options - Chart configuration options
     */
    createPlatformNetwork(containerId, data, _options = {}) {
        const container = d3.select(`#${containerId}`);
        container.selectAll('*').remove();

        const containerRect = container.node().getBoundingClientRect();
        const width = containerRect.width;
        const height = 400;

        const svg = container
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        // Create force simulation
        const simulation = d3.forceSimulation(data.nodes)
            .force('link', d3.forceLink(data.links).id(d => d.id).distance(100))
            .force('charge', d3.forceManyBody().strength(-300))
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force('collision', d3.forceCollide().radius(30));

        // Create tooltip
        const tooltip = this.createTooltip();

        // Draw links
        const _link = svg.append('g')
            .selectAll('.link')
            .data(data.links)
            .enter()
            .append('line')
            .attr('class', 'link')
            .attr('stroke', this.config.colors.light)
            .attr('stroke-width', d => Math.sqrt(d.value) * 2);

        // Draw nodes
        const node = svg.append('g')
            .selectAll('.node')
            .data(data.nodes)
            .enter()
            .append('circle')
            .attr('class', 'node')
            .attr('r', d => Math.sqrt(d.size) * 3)
            .attr('fill', d => this.getNodeColor(d.group))
            .on('mouseover', (event, d) => {
                tooltip.style('visibility', 'visible')
                    .html(`
                        <strong>${d.name}</strong><br>
                        Category: ${d.group}<br>
                        Connections: ${d.connections || 0}
                    `);
            })
            .on('mousemove', (event) => {
                tooltip.style('top', (event.pageY - 10) + 'px')
                    .style('left', (event.pageX + 10) + 'px');
            })
            .on('mouseout', () => {
                tooltip.style('visibility', 'hidden');
            })
            .call(this.drag(simulation));

        // Add labels
        const labels = svg.append('g')
            .selectAll('.label')
            .data(data.nodes)
            .enter()
            .append('text')
            .attr('class', 'label')
            .attr('text-anchor', 'middle')
            .attr('dy', '.35em')
            .style('font-size', '12px')
            .style('fill', '#333')
            .text(d => d.name.length > 10 ? d.name.substring(0, 10) + '...' : d.name);

        // Update positions on simulation tick
        simulation.on('tick', () => {
            _link
                .attr('x1', d => d.source.x)
                .attr('y1', d => d.source.y)
                .attr('x2', d => d.target.x)
                .attr('y2', d => d.target.y);

            node
                .attr('cx', d => d.x)
                .attr('cy', d => d.y);

            labels
                .attr('x', d => d.x)
                .attr('y', d => d.y);
        });
    }

    /**
     * Create a radial tree for hierarchical data
     * 
     * @param {string} containerId - DOM element ID
     * @param {Object} data - Hierarchical data structure
     * @param {Object} options - Chart configuration options
     */
    createRadialTree(containerId, data, _options = {}) {
        const container = d3.select(`#${containerId}`);
        container.selectAll('*').remove();

        const containerRect = container.node().getBoundingClientRect();
        const width = containerRect.width;
        const height = 400;
        const radius = Math.min(width, height) / 2 - 50;

        const svg = container
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g')
            .attr('transform', `translate(${width / 2},${height / 2})`);

        // Create tree layout
        const tree = d3.tree()
            .size([2 * Math.PI, radius])
            .separation((a, b) => (a.parent == b.parent ? 1 : 2) / a.depth);

        const root = d3.hierarchy(data);
        tree(root);

        // Create tooltip
        const tooltip = this.createTooltip();

        // Draw links
        const link = g.selectAll('.link')
            .data(root.links())
            .enter()
            .append('path')
            .attr('class', 'link')
            .attr('fill', 'none')
            .attr('stroke', this.config.colors.light)
            .attr('stroke-width', 2)
            .attr('d', d3.linkRadial()
                .angle(d => d.x)
                .radius(d => d.y));

        // Draw nodes
        const node = g.selectAll('.node')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('class', 'node')
            .attr('transform', d => `
                rotate(${(d.x * 180 / Math.PI - 90)})
                translate(${d.y},0)
            `);

        node.append('circle')
            .attr('r', 5)
            .attr('fill', d => d.children ? this.config.colors.primary : this.config.colors.success)
            .on('mouseover', (event, d) => {
                tooltip.style('visibility', 'visible')
                    .html(`
                        <strong>${d.data.name}</strong><br>
                        ${d.data.description || 'No description'}
                    `);
            })
            .on('mousemove', (event) => {
                tooltip.style('top', (event.pageY - 10) + 'px')
                    .style('left', (event.pageX + 10) + 'px');
            })
            .on('mouseout', () => {
                tooltip.style('visibility', 'hidden');
            });

        node.append('text')
            .attr('dy', '.31em')
            .attr('x', d => d.x < Math.PI === !d.children ? 6 : -6)
            .style('text-anchor', d => d.x < Math.PI === !d.children ? 'start' : 'end')
            .attr('transform', d => d.x >= Math.PI ? 'rotate(180)' : null)
            .style('font-size', '11px')
            .text(d => d.data.name);
    }

    /**
     * Create a bubble chart for event popularity
     * 
     * @param {string} containerId - DOM element ID
     * @param {Array} data - Array of events with size and category
     * @param {Object} options - Chart configuration options
     */
    createBubbleChart(containerId, data, _options = {}) {
        const container = d3.select(`#${containerId}`);
        container.selectAll('*').remove();

        const containerRect = container.node().getBoundingClientRect();
        const width = containerRect.width;
        const height = 400;

        const svg = container
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        // Create pack layout
        const pack = d3.pack()
            .size([width, height])
            .padding(5);

        const root = d3.hierarchy({ children: data })
            .sum(d => d.value)
            .sort((a, b) => b.value - a.value);

        pack(root);

        // Create color scale
        const colorScale = d3.scaleOrdinal(d3.schemeCategory10);

        // Create tooltip
        const tooltip = this.createTooltip();

        // Draw bubbles
        const node = svg.selectAll('.node')
            .data(root.leaves())
            .enter()
            .append('g')
            .attr('class', 'node')
            .attr('transform', d => `translate(${d.x},${d.y})`);

        node.append('circle')
            .attr('r', d => d.r)
            .attr('fill', d => colorScale(d.data.category))
            .attr('stroke', '#fff')
            .attr('stroke-width', 2)
            .style('opacity', 0.8)
            .on('mouseover', (event, d) => {
                tooltip.style('visibility', 'visible')
                    .html(`
                        <strong>${d.data.name}</strong><br>
                        Category: ${d.data.category}<br>
                        Value: ${d.data.value.toLocaleString()}
                    `);
            })
            .on('mousemove', (event) => {
                tooltip.style('top', (event.pageY - 10) + 'px')
                    .style('left', (event.pageX + 10) + 'px');
            })
            .on('mouseout', () => {
                tooltip.style('visibility', 'hidden');
            })
            .transition()
            .duration(this.config.animation.duration)
            .attr('r', d => d.r);

        // Add labels for larger bubbles
        node.filter(d => d.r > 30)
            .append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '.35em')
            .style('font-size', d => Math.min(d.r / 3, 14) + 'px')
            .style('fill', '#fff')
            .style('font-weight', 'bold')
            .text(d => d.data.name.length > 12 ? d.data.name.substring(0, 12) + '...' : d.data.name);
    }

    /**
     * Create a parallel coordinates chart for multi-dimensional analysis
     * 
     * @param {string} containerId - DOM element ID
     * @param {Array} data - Array of objects with multiple numeric dimensions
     * @param {Array} dimensions - Array of dimension names to display
     * @param {Object} options - Chart configuration options
     */
    createParallelCoordinates(containerId, data, dimensions, _options = {}) {
        const container = d3.select(`#${containerId}`);
        container.selectAll('*').remove();

        const containerRect = container.node().getBoundingClientRect();
        const width = containerRect.width - this.config.margins.left - this.config.margins.right;
        const height = 400 - this.config.margins.top - this.config.margins.bottom;

        const svg = container
            .append('svg')
            .attr('width', width + this.config.margins.left + this.config.margins.right)
            .attr('height', height + this.config.margins.top + this.config.margins.bottom);

        const g = svg.append('g')
            .attr('transform', `translate(${this.config.margins.left},${this.config.margins.top})`);

        // Create scales
        const x = d3.scalePoint()
            .range([0, width])
            .domain(dimensions);

        const y = {};
        dimensions.forEach(dim => {
            y[dim] = d3.scaleLinear()
                .domain(d3.extent(data, d => d[dim]))
                .range([height, 0]);
        });

        // Create line function
        const line = d3.line()
            .defined(d => !isNaN(d[1]))
            .x(d => d[0])
            .y(d => d[1]);

        // Create color scale
        const colorScale = d3.scaleOrdinal(d3.schemeCategory10);

        // Draw lines
        g.selectAll('.line')
            .data(data)
            .enter()
            .append('path')
            .attr('class', 'line')
            .attr('d', d => line(dimensions.map(dim => [x(dim), y[dim](d[dim])])))
            .attr('fill', 'none')
            .attr('stroke', (d, i) => colorScale(i))
            .attr('stroke-width', 1.5)
            .style('opacity', 0.6);

        // Draw axes
        const axes = g.selectAll('.axis')
            .data(dimensions)
            .enter()
            .append('g')
            .attr('class', 'axis')
            .attr('transform', d => `translate(${x(d)},0)`);

        axes.each(function(d) {
            d3.select(this).call(d3.axisLeft(y[d]));
        });

        // Add axis labels
        axes.append('text')
            .attr('text-anchor', 'middle')
            .attr('y', -9)
            .style('font-weight', 'bold')
            .text(d => d.replace('_', ' ').toUpperCase());
    }

    // Helper methods

    /**
     * Create a reusable tooltip
     */
    createTooltip() {
        return d3.select('body')
            .append('div')
            .style('position', 'absolute')
            .style('visibility', 'hidden')
            .style('background', 'rgba(0, 0, 0, 0.8)')
            .style('color', 'white')
            .style('padding', '8px')
            .style('border-radius', '4px')
            .style('font-size', '12px')
            .style('z-index', '1000')
            .style('pointer-events', 'none');
    }

    /**
     * Add color legend to chart
     */
    addColorLegend(svg, colorScale, width, _height) {
        const legendWidth = 200;
        const legendHeight = 20;
        const legendX = width + this.config.margins.left - legendWidth;
        const legendY = 10;

        const legend = svg.append('g')
            .attr('class', 'legend')
            .attr('transform', `translate(${legendX},${legendY})`);

        // Create gradient
        const defs = svg.append('defs');
        const gradient = defs.append('linearGradient')
            .attr('id', 'legend-gradient');

        const domain = colorScale.domain();
        const range = d3.range(0, 1.1, 0.1);
        
        range.forEach((t, _i) => {
            gradient.append('stop')
                .attr('offset', `${t * 100}%`)
                .attr('stop-color', colorScale(domain[0] + t * (domain[1] - domain[0])));
        });

        // Draw legend rectangle
        legend.append('rect')
            .attr('width', legendWidth)
            .attr('height', legendHeight)
            .attr('fill', 'url(#legend-gradient)');

        // Add legend labels
        legend.append('text')
            .attr('x', 0)
            .attr('y', legendHeight + 15)
            .style('font-size', '12px')
            .text(domain[0].toFixed(0));

        legend.append('text')
            .attr('x', legendWidth)
            .attr('y', legendHeight + 15)
            .attr('text-anchor', 'end')
            .style('font-size', '12px')
            .text(domain[1].toFixed(0));
    }

    /**
     * Get node color based on group
     */
    getNodeColor(group) {
        const colorMap = {
            'platform': this.config.colors.primary,
            'event': this.config.colors.success,
            'venue': this.config.colors.warning,
            'category': this.config.colors.info,
            'default': this.config.colors.secondary
        };
        return colorMap[group] || colorMap['default'];
    }

    /**
     * Create drag behavior for network nodes
     */
    drag(simulation) {
        function dragstarted(event) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            event.subject.fx = event.subject.x;
            event.subject.fy = event.subject.y;
        }

        function dragged(event) {
            event.subject.fx = event.x;
            event.subject.fy = event.y;
        }

        function dragended(event) {
            if (!event.active) simulation.alphaTarget(0);
            event.subject.fx = null;
            event.subject.fy = null;
        }

        return d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended);
    }

    /**
     * Resize chart to fit container
     */
    resizeChart(containerId) {
        // Implementation for responsive chart resizing
        const container = d3.select(`#${containerId}`);
        const svg = container.select('svg');
        
        if (!svg.empty()) {
            const containerRect = container.node().getBoundingClientRect();
            svg.attr('width', containerRect.width);
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = D3AnalyticsCharts;
} else {
    window.D3AnalyticsCharts = D3AnalyticsCharts;
}
