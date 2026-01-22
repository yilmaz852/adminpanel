/**
 * WooCommerce Üretim Planlama - Scheduler JavaScript
 * Scheduling interface and drag-and-drop functionality
 */

(function($) {
    'use strict';
    
    var wupScheduler = {
        
        /**
         * Initialize scheduler
         */
        init: function() {
            this.initDragDrop();
            this.initDatePickers();
            this.initFilters();
            this.bindEvents();
        },
        
        /**
         * Initialize drag and drop
         */
        initDragDrop: function() {
            var self = this;
            
            // Make orders draggable
            $('.wup-order-item').draggable({
                helper: 'clone',
                revert: 'invalid',
                cursor: 'move',
                zIndex: 1000,
                start: function(event, ui) {
                    $(this).addClass('dragging');
                },
                stop: function(event, ui) {
                    $(this).removeClass('dragging');
                }
            });
            
            // Make schedule slots droppable
            $('.wup-schedule-slot').droppable({
                accept: '.wup-order-item',
                hoverClass: 'drop-hover',
                drop: function(event, ui) {
                    var orderId = ui.draggable.data('order-id');
                    var departmentId = $(this).data('department-id');
                    var date = $(this).data('date');
                    
                    self.assignOrderToDepartment(orderId, departmentId, date, $(this));
                }
            });
            
            // Make existing schedule items draggable
            $('.wup-scheduled-item').draggable({
                revert: 'invalid',
                cursor: 'move',
                helper: 'clone',
                start: function() {
                    $(this).addClass('dragging');
                },
                stop: function() {
                    $(this).removeClass('dragging');
                }
            });
        },
        
        /**
         * Assign order to department
         */
        assignOrderToDepartment: function(orderId, departmentId, date, $slot) {
            var self = this;
            
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_assign_order',
                    nonce: wupData.nonce,
                    order_id: orderId,
                    department_id: departmentId,
                    scheduled_date: date
                },
                beforeSend: function() {
                    $slot.addClass('loading');
                },
                success: function(response) {
                    $slot.removeClass('loading');
                    
                    if (response.success) {
                        self.showNotice('Order assigned successfully', 'success');
                        self.refreshSchedule();
                    } else {
                        self.showNotice(response.data || 'Failed to assign order', 'error');
                    }
                },
                error: function() {
                    $slot.removeClass('loading');
                    self.showNotice('Error assigning order', 'error');
                }
            });
        },
        
        /**
         * Initialize date pickers
         */
        initDatePickers: function() {
            $('.wup-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0,
                beforeShowDay: function(date) {
                    // Disable weekends if needed
                    var day = date.getDay();
                    return [(day !== 0 && day !== 6)];
                }
            });
        },
        
        /**
         * Initialize filters
         */
        initFilters: function() {
            var self = this;
            
            // Department filter
            $('#wup-filter-department').on('change', function() {
                self.applyFilters();
            });
            
            // Status filter
            $('#wup-filter-status').on('change', function() {
                self.applyFilters();
            });
            
            // Date range filter
            $('#wup-filter-date-start, #wup-filter-date-end').on('change', function() {
                self.applyFilters();
            });
            
            // Priority filter
            $('#wup-filter-priority').on('change', function() {
                self.applyFilters();
            });
        },
        
        /**
         * Apply filters
         */
        applyFilters: function() {
            var filters = {
                department: $('#wup-filter-department').val(),
                status: $('#wup-filter-status').val(),
                dateStart: $('#wup-filter-date-start').val(),
                dateEnd: $('#wup-filter-date-end').val(),
                priority: $('#wup-filter-priority').val()
            };
            
            // Update URL with filters
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
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // Auto-schedule button
            $('#wup-auto-schedule').on('click', function(e) {
                e.preventDefault();
                self.autoSchedule();
            });
            
            // Optimize schedule button
            $('#wup-optimize-schedule').on('click', function(e) {
                e.preventDefault();
                self.optimizeSchedule();
            });
            
            // Clear schedule button
            $('#wup-clear-schedule').on('click', function(e) {
                e.preventDefault();
                self.clearSchedule();
            });
            
            // Update schedule item
            $(document).on('click', '.wup-update-schedule', function(e) {
                e.preventDefault();
                var scheduleId = $(this).data('schedule-id');
                self.updateScheduleItem(scheduleId);
            });
            
            // Delete schedule item
            $(document).on('click', '.wup-delete-schedule', function(e) {
                e.preventDefault();
                var scheduleId = $(this).data('schedule-id');
                self.deleteScheduleItem(scheduleId);
            });
            
            // Priority change
            $(document).on('change', '.wup-schedule-priority', function() {
                var scheduleId = $(this).data('schedule-id');
                var priority = $(this).val();
                self.updatePriority(scheduleId, priority);
            });
            
            // Status change
            $(document).on('change', '.wup-schedule-status', function() {
                var scheduleId = $(this).data('schedule-id');
                var status = $(this).val();
                self.updateStatus(scheduleId, status);
            });
        },
        
        /**
         * Auto schedule orders
         */
        autoSchedule: function() {
            if (!confirm('This will automatically schedule all unscheduled orders. Continue?')) {
                return;
            }
            
            var self = this;
            
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_auto_schedule',
                    nonce: wupData.nonce
                },
                beforeSend: function() {
                    self.showLoading('Auto-scheduling orders...');
                },
                success: function(response) {
                    self.hideLoading();
                    
                    if (response.success) {
                        self.showNotice('Orders scheduled successfully', 'success');
                        self.refreshSchedule();
                    } else {
                        self.showNotice(response.data || 'Failed to auto-schedule', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showNotice('Error auto-scheduling', 'error');
                }
            });
        },
        
        /**
         * Optimize schedule
         */
        optimizeSchedule: function() {
            if (!confirm('This will optimize the current schedule for better resource utilization. Continue?')) {
                return;
            }
            
            var self = this;
            
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_optimize_schedule',
                    nonce: wupData.nonce
                },
                beforeSend: function() {
                    self.showLoading('Optimizing schedule...');
                },
                success: function(response) {
                    self.hideLoading();
                    
                    if (response.success) {
                        self.showNotice('Schedule optimized successfully', 'success');
                        self.refreshSchedule();
                    } else {
                        self.showNotice(response.data || 'Failed to optimize', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showNotice('Error optimizing schedule', 'error');
                }
            });
        },
        
        /**
         * Clear schedule
         */
        clearSchedule: function() {
            if (!confirm('This will clear all scheduled items. Are you sure?')) {
                return;
            }
            
            var self = this;
            
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_clear_schedule',
                    nonce: wupData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('Schedule cleared successfully', 'success');
                        self.refreshSchedule();
                    } else {
                        self.showNotice(response.data || 'Failed to clear schedule', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error clearing schedule', 'error');
                }
            });
        },
        
        /**
         * Update schedule item
         */
        updateScheduleItem: function(scheduleId) {
            // Implement update logic
            window.location.href = wupData.adminUrl + 'admin.php?page=wup-schedule&action=edit&id=' + scheduleId;
        },
        
        /**
         * Delete schedule item
         */
        deleteScheduleItem: function(scheduleId) {
            if (!confirm('Delete this schedule item?')) {
                return;
            }
            
            var self = this;
            
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_delete_schedule',
                    nonce: wupData.nonce,
                    id: scheduleId
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('Schedule deleted successfully', 'success');
                        $('#schedule-' + scheduleId).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        self.showNotice(response.data || 'Failed to delete', 'error');
                    }
                },
                error: function() {
                    self.showNotice('Error deleting schedule', 'error');
                }
            });
        },
        
        /**
         * Update priority
         */
        updatePriority: function(scheduleId, priority) {
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_update_schedule_priority',
                    nonce: wupData.nonce,
                    id: scheduleId,
                    priority: priority
                },
                success: function(response) {
                    if (!response.success) {
                        wupScheduler.showNotice(response.data || 'Failed to update priority', 'error');
                    }
                }
            });
        },
        
        /**
         * Update status
         */
        updateStatus: function(scheduleId, status) {
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_update_schedule_status',
                    nonce: wupData.nonce,
                    id: scheduleId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        wupScheduler.showNotice('Status updated', 'success');
                    } else {
                        wupScheduler.showNotice(response.data || 'Failed to update status', 'error');
                    }
                }
            });
        },
        
        /**
         * Refresh schedule
         */
        refreshSchedule: function() {
            window.location.reload();
        },
        
        /**
         * Show loading overlay
         */
        showLoading: function(message) {
            var overlay = $('<div class="wup-loading-overlay">');
            overlay.html(
                '<div class="wup-loading-content">' +
                '<div class="spinner is-active"></div>' +
                '<p>' + message + '</p>' +
                '</div>'
            );
            $('body').append(overlay);
        },
        
        /**
         * Hide loading overlay
         */
        hideLoading: function() {
            $('.wup-loading-overlay').remove();
        },
        
        /**
         * Show notice
         */
        showNotice: function(message, type) {
            var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after(notice);
            
            setTimeout(function() {
                notice.fadeOut(300, function() {
                    notice.remove();
                });
            }, 3000);
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('.wup-scheduler-page').length) {
            wupScheduler.init();
        }
    });
    
    // Expose globally
    window.wupScheduler = wupScheduler;
    
})(jQuery);
