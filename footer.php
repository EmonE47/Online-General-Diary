<?php
/**
 * Common Footer
 * Online General Diary System
 */
?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        // Confirm delete actions
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }
        
        // Mark notification as read
        function markNotificationAsRead(notifId) {
            $.ajax({
                url: '<?php echo APP_URL; ?>/api/notifications.php',
                method: 'POST',
                data: {
                    action: 'mark_read',
                    notif_id: notifId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
        
        // Auto-refresh notifications every 30 seconds
        setInterval(function() {
            $.ajax({
                url: '<?php echo APP_URL; ?>/api/notifications.php',
                method: 'GET',
                data: { action: 'get_unread_count' },
                success: function(response) {
                    if (response.count > 0) {
                        $('.notification-badge').text(response.count).show();
                    } else {
                        $('.notification-badge').hide();
                    }
                }
            });
        }, 30000);
        
        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;
            
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }
        
        // File upload progress
        function uploadFile(input, gdId) {
            const file = input.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('gd_id', gdId);
            
            $.ajax({
                url: '<?php echo APP_URL; ?>/api/file_upload.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = evt.loaded / evt.total * 100;
                            $('#upload-progress').show().find('.progress-bar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Upload failed: ' + response.error);
                    }
                },
                error: function() {
                    alert('Upload failed. Please try again.');
                }
            });
        }
        
        // Data table initialization
        function initDataTable(tableId) {
            if ($.fn.DataTable) {
                $('#' + tableId).DataTable({
                    "pageLength": 25,
                    "order": [[0, "desc"]],
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    }
                });
            }
        }
        
        // Export to CSV
        function exportToCSV(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    let cellText = cols[j].innerText;
                    cellText = cellText.replace(/"/g, '""');
                    row.push('"' + cellText + '"');
                }
                
                csv.push(row.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        // Print page
        function printPage() {
            window.print();
        }
        
        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard!');
            });
        }
        
        // Show loading spinner
        function showLoading() {
            $('#loading-spinner').show();
        }
        
        // Hide loading spinner
        function hideLoading() {
            $('#loading-spinner').hide();
        }
        
        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            if (xhr.status === 401) {
                alert('Session expired. Please login again.');
                window.location.href = '<?php echo APP_URL; ?>/auth/login.php';
            } else if (xhr.status === 403) {
                alert('Access denied. You do not have permission to perform this action.');
            } else if (xhr.status >= 500) {
                alert('Server error. Please try again later.');
            }
        });
    </script>
    
    <!-- Loading Spinner -->
    <div id="loading-spinner" class="position-fixed top-50 start-50 translate-middle" style="display: none; z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Upload Progress Bar -->
    <div id="upload-progress" class="position-fixed bottom-0 start-0 w-100" style="display: none; z-index: 9998;">
        <div class="progress" style="height: 4px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
    </div>
    
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
                <span class="ms-3">Version <?php echo APP_VERSION; ?></span>
            </p>
        </div>
    </footer>
</body>
</html>
