/**
 * WooCommerce Üretim Planlama - Calendar JavaScript
 * Calendar interactions and FullCalendar integration
 */

(function($) {
    'use strict';
    
    var wupCalendar = {
        calendar: null,
        filters: {
            department: 'all',
            status: 'all',
            dateRange: null
        },
        
        /**
         * Initialize calendar
         */
        init: function() {
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar library not loaded');
                return;
            }
            
            this.initCalendar();
            this.initFilters();
            this.bindEvents();
        },
        
        /**
         * Initialize FullCalendar
         */
        initCalendar: function() {
            var calendarEl = document.getElementById('wup-calendar');
            if (!calendarEl) {
                return;
            }
            
            var self = this;
            
            this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                editable: true,
                droppable: true,
                eventDurationEditable: true,
                eventStartEditable: true,
                events: function(info, successCallback, failureCallback) {
                    self.loadEvents(info, successCallback, failureCallback);
                },
                eventClick: function(info) {
                    self.handleEventClick(info);
                },
                eventDrop: function(info) {
                    self.handleEventDrop(info);
                },
                eventResize: function(info) {
                    self.handleEventResize(info);
                },
                eventDidMount: function(info) {
                    self.customizeEvent(info);
                },
                loading: function(isLoading) {
                    self.toggleLoading(isLoading);
                },
                height: 'auto',
                locale: 'tr',
                firstDay: 1,
                businessHours: {
                    daysOfWeek: [1, 2, 3, 4, 5],
                    startTime: '08:00',
                    endTime: '18:00'
                },
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                nowIndicator: true,
                navLinks: true,
                dayMaxEvents: true
            });
            
            this.calendar.render();
        },
        
        /**
         * Load events from server
         */
        loadEvents: function(info, successCallback, failureCallback) {
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_get_calendar_events',
                    nonce: wupData.nonce,
                    start: info.startStr,
                    end: info.endStr,
                    department: this.filters.department,
                    status: this.filters.status
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        failureCallback(response.data);
                    }
                },
                error: function() {
                    failureCallback('Error loading events');
                }
            });
        },
        
        /**
         * Handle event click
         */
        handleEventClick: function(info) {
            var event = info.event;
            var content = '<div class="wup-event-details">';
            content += '<h3>' + event.title + '</h3>';
            content += '<p><strong>Order:</strong> #' + (event.extendedProps.orderId || 'N/A') + '</p>';
            content += '<p><strong>Department:</strong> ' + (event.extendedProps.departmentName || 'N/A') + '</p>';
            content += '<p><strong>Status:</strong> ' + (event.extendedProps.status || 'N/A') + '</p>';
            content += '<p><strong>Start:</strong> ' + event.start.toLocaleString() + '</p>';
            
            if (event.end) {
                content += '<p><strong>End:</strong> ' + event.end.toLocaleString() + '</p>';
            }
            
            if (event.extendedProps.notes) {
                content += '<p><strong>Notes:</strong> ' + event.extendedProps.notes + '</p>';
            }
            
            content += '<div class="wup-event-actions">';
            content += '<button class="button button-primary" onclick="wupCalendar.editEvent(' + event.id + ')">Edit</button>';
            content += '<button class="button" onclick="wupCalendar.deleteEvent(' + event.id + ')">Delete</button>';
            content += '</div>';
            content += '</div>';
            
            // Show modal or tooltip
            this.showModal('Event Details', content);
        },
        
        /**
         * Handle event drop (drag and drop)
         */
        handleEventDrop: function(info) {
            var event = info.event;
            
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_update_schedule',
                    nonce: wupData.nonce,
                    id: event.id,
                    start: event.start.toISOString(),
                    end: event.end ? event.end.toISOString() : null
                },
                success: function(response) {
                    if (response.success) {
                        wupCalendar.showNotice('Schedule updated successfully', 'success');
                    } else {
                        info.revert();
                        wupCalendar.showNotice(response.data || 'Failed to update schedule', 'error');
                    }
                },
                error: function() {
                    info.revert();
                    wupCalendar.showNotice('Error updating schedule', 'error');
                }
            });
        },
        
        /**
         * Handle event resize
         */
        handleEventResize: function(info) {
            this.handleEventDrop(info);
        },
        
        /**
         * Customize event appearance
         */
        customizeEvent: function(info) {
            var event = info.event;
            var el = info.el;
            
            // Add status class
            if (event.extendedProps.status) {
                el.classList.add('status-' + event.extendedProps.status);
            }
            
            // Add department class
            if (event.extendedProps.departmentId) {
                el.classList.add('dept-' + event.extendedProps.departmentId);
            }
            
            // Add tooltip
            $(el).attr('title', event.title);
        },
        
        /**
         * Initialize filters
         */
        initFilters: function() {
            var self = this;
            
            $('#wup-filter-department').on('change', function() {
                self.filters.department = $(this).val();
                self.refreshCalendar();
            });
            
            $('#wup-filter-status').on('change', function() {
                self.filters.status = $(this).val();
                self.refreshCalendar();
            });
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // Refresh button
            $('#wup-refresh-calendar').on('click', function(e) {
                e.preventDefault();
                self.refreshCalendar();
            });
            
            // Add schedule button
            $('#wup-add-schedule').on('click', function(e) {
                e.preventDefault();
                self.showAddScheduleForm();
            });
        },
        
        /**
         * Refresh calendar
         */
        refreshCalendar: function() {
            if (this.calendar) {
                this.calendar.refetchEvents();
            }
        },
        
        /**
         * Toggle loading state
         */
        toggleLoading: function(isLoading) {
            if (isLoading) {
                $('#wup-calendar-container').addClass('loading');
            } else {
                $('#wup-calendar-container').removeClass('loading');
            }
        },
        
        /**
         * Edit event
         */
        editEvent: function(eventId) {
            window.location.href = wupData.adminUrl + 'admin.php?page=wup-schedule&action=edit&id=' + eventId;
        },
        
        /**
         * Delete event
         */
        deleteEvent: function(eventId) {
            if (!confirm('Are you sure you want to delete this schedule?')) {
                return;
            }
            
            $.ajax({
                url: wupData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wup_delete_schedule',
                    nonce: wupData.nonce,
                    id: eventId
                },
                success: function(response) {
                    if (response.success) {
                        wupCalendar.refreshCalendar();
                        wupCalendar.showNotice('Schedule deleted successfully', 'success');
                    } else {
                        wupCalendar.showNotice(response.data || 'Failed to delete schedule', 'error');
                    }
                },
                error: function() {
                    wupCalendar.showNotice('Error deleting schedule', 'error');
                }
            });
        },
        
        /**
         * Show add schedule form
         */
        showAddScheduleForm: function() {
            window.location.href = wupData.adminUrl + 'admin.php?page=wup-schedule&action=add';
        },
        
        /**
         * Show modal
         */
        showModal: function(title, content) {
            var modal = $('<div class="wup-modal">');
            modal.html(
                '<div class="wup-modal-content">' +
                '<div class="wup-modal-header">' +
                '<h2>' + title + '</h2>' +
                '<span class="wup-modal-close">&times;</span>' +
                '</div>' +
                '<div class="wup-modal-body">' + content + '</div>' +
                '</div>'
            );
            
            $('body').append(modal);
            modal.fadeIn(200);
            
            // Close on click
            modal.on('click', '.wup-modal-close, .wup-modal', function(e) {
                if (e.target === this) {
                    modal.fadeOut(200, function() {
                        modal.remove();
                    });
                }
            });
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
        if ($('#wup-calendar').length) {
            wupCalendar.init();
        }
    });
    
    // Expose globally
    window.wupCalendar = wupCalendar;
    
})(jQuery);
