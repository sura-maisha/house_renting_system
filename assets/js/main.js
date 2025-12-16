/**
 * Rental Platform - Main JavaScript
 * Handles form validation, image previews, and UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Image preview for listing forms
    const imageInputs = document.querySelectorAll('input[type="file"][name="images[]"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const previewContainer = document.getElementById('imagePreview');
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            const files = Array.from(e.target.files);
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '4px';
                        img.style.margin = '8px';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check if form has required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e63946';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Password confirmation validation
            const passwordField = form.querySelector('input[name="password"]');
            const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
            if (passwordField && confirmPasswordField) {
                if (passwordField.value !== confirmPasswordField.value) {
                    isValid = false;
                    confirmPasswordField.style.borderColor = '#e63946';
                    alert('Passwords do not match');
                }
            }
            
            // New password confirmation validation
            const newPasswordField = form.querySelector('input[name="new_password"]');
            const confirmNewPasswordField = form.querySelector('input[name="confirm_password"]');
            if (newPasswordField && confirmNewPasswordField && form.querySelector('input[name="change_password"]')) {
                if (newPasswordField.value !== confirmNewPasswordField.value) {
                    isValid = false;
                    confirmNewPasswordField.style.borderColor = '#e63946';
                    alert('New passwords do not match');
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Real-time validation feedback
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.style.borderColor = '#e63946';
            } else {
                this.style.borderColor = '';
            }
        });
        
        input.addEventListener('input', function() {
            if (this.style.borderColor === 'rgb(230, 57, 70)') {
                if (this.value.trim()) {
                    this.style.borderColor = '';
                }
            }
        });
    });
    
    // Search enhancement - debounce for better performance
    let searchTimeout;
    const searchInput = document.getElementById('keyword');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            // Could add live search here if needed
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Alert auto-dismiss (optional enhancement)
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Image gallery navigation
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('button[type="submit"].btn-danger, .btn-danger[type="submit"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
});

