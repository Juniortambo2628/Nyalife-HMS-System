<?php include(APPPATH . 'includes/header.php'); ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h5 class="mb-0"><?= $pageTitle ?></h5>
                </div>
                <div class="card-body">
                    <?php include('_form.php'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(APPPATH . 'includes/footer.php'); ?>

<!-- Initialize form elements -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker
    if (typeof flatpickr !== 'undefined') {
        flatpickr("#consultation_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:s",
            time_24hr: true
        });
    }
    
    // Initialize select2 for dropdowns
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select an option',
            allowClear: true
        });
    }
    
    // Initialize TinyMCE for rich text areas
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.tinymce',
            plugins: 'link lists table code help',
            toolbar: 'undo redo | formatselect | bold italic backcolor | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | removeformat | help',
            menubar: false,
            height: 300,
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
            relative_urls: false,
            convert_urls: false
        });
    }
    
    // Calculate BMI when height or weight changes
    function calculateBMI() {
        const height = parseFloat($('#height').val()) || 0;
        const weight = parseFloat($('#weight').val()) || 0;
        
        if (height > 0 && weight > 0) {
            const heightInMeters = height / 100; // Convert cm to m
            const bmi = weight / (heightInMeters * heightInMeters);
            $('#bmi').val(bmi.toFixed(1));
        } else {
            $('#bmi').val('');
        }
    }
    
    $('#height, #weight').on('input', calculateBMI);
    
    // Toggle gynecological fields based on patient gender
    function toggleGynecologicalFields() {
        const isFemale = '<?= strtolower($patient['gender'] ?? '') ?>' === 'female';
        $('.gynecological-field').toggle(isFemale);
    }
    
    toggleGynecologicalFields();
    
    // Form submission
    $('#consultationForm').on('submit', function(e) {
        e.preventDefault();
        
        // Update TinyMCE textareas before form submission
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const submitBtnText = submitBtn.html();
        
        // Disable submit button and show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    showAlert('error', response.message || 'An error occurred');
                    submitBtn.prop('disabled', false).html(submitBtnText);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred while saving the consultation';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Error parsing error response:', e);
                }
                
                showAlert('error', errorMessage);
                submitBtn.prop('disabled', false).html(submitBtnText);
            }
        });
    });
    
    // Function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove any existing alerts
        $('.alert-dismissible').alert('close');
        
        // Add new alert
        $('.card-header').after(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('.alert-dismissible').alert('close');
        }, 5000);
    }
    
    // Handle back button
    $('.btn-back').on('click', function(e) {
        e.preventDefault();
        window.history.back();
    });
});
</script>
