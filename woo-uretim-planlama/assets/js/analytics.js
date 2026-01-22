/**
 * WooCommerce Üretim Planlama - Analytics JavaScript
 * Charts and analytics display
 */

(function($) {
    'use strict';
    
    var wupAnalytics = {
        charts: {},
        
        /**
         * Initialize analytics
         */
        init: function() {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js library not loaded');
                return;
            }
            
            this.initCharts();
            this.initFilters();
            this.bindEvents();
        },
        
        /**
         * Initialize charts
         */
        initCharts: function() {
            // Production time by department chart
            if ($('#chart-department-time').length) {
                this.createDepartmentTimeChart();
            }
            
            // Order status distribution chart
            if ($('#chart-status-distribution').length) {
                this.createStatusDistributionChart();
            }
            
            // Production trend chart
            if ($('#chart-production-trend').length) {
                this.createProductionTrendChart();
            }
            
            // Department efficiency chart
            if ($('#chart-department-efficiency').length) {
                this.createDepartmentEfficiencyChart();
            }
            
            // On-time delivery chart
            if ($('#chart-delivery-rate').length) {
                this.createDeliveryRateChart();
            }
        },
        
        /**
         * Create department time chart
         */
        createDepartmentTimeChart: function() {
            var ctx = document.getElementById('chart-department-time').getContext('2d');
            
            this.charts.departmentTime = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: wupAnalyticsData.departmentLabels || [],
                    datasets: [{
                        label: 'Average Time (hours)',
                        data: wupAnalyticsData.departmentTimes || [],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: [
                            'rgb(52, 152, 219)',
                            'rgb(46, 204, 113)',
                            'rgb(155, 89, 182)',
                            'rgb(241, 196, 15)',
                            'rgb(231, 76, 60)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Average Production Time by Department'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Hours'
                            }
                        }
                    }
                }
            });
        },
        
        /**
         * Create status distribution chart
         */
        createStatusDistributionChart: function() {
            var ctx = document.getElementById('chart-status-distribution').getContext('2d');
            
            this.charts.statusDistribution = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: wupAnalyticsData.statusLabels || [],
                    datasets: [{
                        data: wupAnalyticsData.statusCounts || [],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(231, 76, 60, 0.7)',
                            'rgba(149, 165, 166, 0.7)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        title: {
                            display: true,
                            text: 'Order Status Distribution'
                        }
                    }
                }
            });
        },
        
        /**
         * Create production trend chart
         */
        createProductionTrendChart: function() {
            var ctx = document.getElementById('chart-production-trend').getContext('2d');
            
            this.charts.productionTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: wupAnalyticsData.trendDates || [],
                    datasets: [{
                        label: 'Completed Orders',
                        data: wupAnalyticsData.completedOrders || [],
                        borderColor: 'rgb(46, 204, 113)',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'New Orders',
                        data: wupAnalyticsData.newOrders || [],
                        borderColor: 'rgb(52, 152, 219)',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Production Trend'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Orders'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        },
        
        /**
         * Create department efficiency chart
         */
        createDepartmentEfficiencyChart: function() {
            var ctx = document.getElementById('chart-department-efficiency').getContext('2d');
            
            this.charts.departmentEfficiency = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: wupAnalyticsData.departmentLabels || [],
                    datasets: [{
                        label: 'Efficiency %',
                        data: wupAnalyticsData.departmentEfficiency || [],
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: 'rgb(52, 152, 219)',
                        pointBackgroundColor: 'rgb(52, 152, 219)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(52, 152, 219)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Department Efficiency'
                        }
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    }
                }
            });
        },
        
        /**
         * Create delivery rate chart
         */
        createDeliveryRateChart: function() {
            var ctx = document.getElementById('chart-delivery-rate').getContext('2d');
            
            var onTimeRate = wupAnalyticsData.onTimeRate || 0;
            var lateRate = 100 - onTimeRate;
            
            this.charts.deliveryRate = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['On Time', 'Late'],
                    datasets: [{
                        data: [onTimeRate, lateRate],
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'On-Time Delivery Rate'
                        }
                    }
                }
            });
        },
        
        /**
         * Initialize filters
         */
        initFilters: function() {
            var self = this;
            
            // Date range filter
            $('#wup-analytics-date-start, #wup-analytics-date-end').on('change', function() {
                self.refreshData();
            });
            
            // Department filter
            $('#wup-analytics-department').on('change', function() {
                self.refreshData();
            });
            
            // Period filter
            $('#wup-analytics-period').on('change', function() {
                self.refreshData();
            });
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // Export button
            $('#wup-export-analytics').on('click', function(e) {
                e.preventDefault();
                self.exportData();
            });
            
            // Refresh button
            $('#wup-refresh-analytics').on('click', function(e) {
                e.preventDefault();
                self.refreshData();
            });
            
            // Print button
            $('#wup-print-analytics').on('click', function(e) {
                e.preventDefault();
                window.print();
            });
        },
        
        /**
         * Refresh data
         */
        refreshData: function() {
            var filters = {
                date_start: $('#wup-analytics-date-start').val(),
                date_end: $('#wup-analytics-date-end').val(),
                department: $('#wup-analytics-department').val(),
                period: $('#wup-analytics-period').val()
            };
            
            var url = new URL(window.location.href);
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    url.searchParams.set(key, filters[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            
            window.location.href = url.toString();
        },
        
        /**
         * Export data
         */
        exportData: function() {
            var format = $('#wup-export-format').val() || 'csv';
            var filters = {
                action: 'wup_export_analytics',
                nonce: wupData.nonce,
                format: format,
                date_start: $('#wup-analytics-date-start').val(),
                date_end: $('#wup-analytics-date-end').val(),
                department: $('#wup-analytics-department').val()
            };
            
            var params = new URLSearchParams(filters).toString();
            window.location.href = wupData.ajaxUrl + '?' + params;
        },
        
        /**
         * Destroy all charts
         */
        destroyCharts: function() {
            Object.keys(this.charts).forEach(function(key) {
                if (this.charts[key]) {
                    this.charts[key].destroy();
                }
            }.bind(this));
            this.charts = {};
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('.wup-analytics-page').length) {
            wupAnalytics.init();
        }
    });
    
    // Expose globally
    window.wupAnalytics = wupAnalytics;
    
})(jQuery);
