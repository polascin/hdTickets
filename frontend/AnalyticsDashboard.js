[0m// frontend/AnalyticsDashboard.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';

const AnalyticsDashboard = () => {
    const [dashboardData, setDashboardData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        async function fetchDashboardData() {
            try {
                const response = await axios.get('/api/analytics/custom-dashboard', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    },
                    params: { widgets: ['price_trends', 'demand_patterns', 'success_rates', 'platform_comparison'] }
                });
                setDashboardData(response.data);
                setLoading(false);
            } catch (err) {
                setError(err);
                setLoading(false);
            }
        }

        fetchDashboardData();
    }, []);

    if (loading) return <div>Loading dashboard...</div>;
    if (error) return <div>Error loading dashboard: {error.message}</div>;

    return (
        <div className="dashboard">
            <h1>Advanced Analytics Dashboard</h1>
            {/* Render widgets based on dashboardData */}
            {dashboardData.widgets.map(widget => (
                <div key={widget} className="widget">
                    <h2>{widget.title}</h2>
                    {/* Implement the rendering logic for each widget */}
                </div>
            ))}
        </div>
    );
};

export default AnalyticsDashboard;
