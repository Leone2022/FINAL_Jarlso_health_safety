// Auto-fill date fields with current date on page load
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;
    document.getElementById('teamLeaderDate').value = today;
});

// Show response message function
function showMessage(message, isSuccess) {
    const messageElement = document.getElementById('responseMessage');
    messageElement.textContent = message;
    messageElement.className = 'response-message ' + (isSuccess ? 'success-message' : 'error-message');
    messageElement.style.display = 'block';
    
    // Scroll to message
    messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Hide after 5 seconds
    setTimeout(() => {
        messageElement.style.display = 'none';
    }, 5000);
}

// Form validation function
function validateForm() {
    // Check if all required fields are filled
    const requiredFields = document.querySelectorAll('[required]');
    let allFilled = true;
    requiredFields.forEach(field => {
        if (!field.value.trim() && field.type !== 'radio') {
            allFilled = false;
            field.classList.add('error');
        } else if (field.type === 'radio') {
            const radioName = field.name;
            const radioButtons = document.querySelectorAll(`input[name="${radioName}"]`);
            const hasChecked = Array.from(radioButtons).some(radio => radio.checked);
            if (!hasChecked) {
                allFilled = false;
                radioButtons.forEach(radio => radio.parentElement.classList.add('error'));
            }
        } else {
            field.classList.remove('error');
        }
    });

    if (!allFilled) {
        showMessage('Please fill in all required fields', false);
        return false;
    }

    return true;
}

// Handle drag and drop functionality
const dropArea = document.getElementById('dropArea');
const imageUpload = document.getElementById('imageUpload');
const preview = document.getElementById('preview');

// Prevent default behaviors for drag events
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Add highlight class when files are dragged over the drop area
['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

function highlight(e) {
    dropArea.classList.add('highlight');
}

// Remove highlight class when files leave the drop area
['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function unhighlight(e) {
    dropArea.classList.remove('highlight');
}

// Handle file drops
dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

// Handle file selection via input
imageUpload.addEventListener('change', function() {
    handleFiles(this.files);
});

// Make the entire drop area clickable
dropArea.addEventListener('click', () => {
    imageUpload.click();
});

// Process multiple files
function handleFiles(files) {
    [...files].forEach(previewFile);
}

// Generate image previews
function previewFile(file) {
    // Only process image files
    if (!file.type.startsWith('image/')) {
        showMessage(`File "${file.name}" is not an image and was skipped.`, false);
        return;
    }
    
    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showMessage(`File "${file.name}" exceeds 5MB and was skipped.`, false);
        return;
    }
    
    const reader = new FileReader();
    reader.readAsDataURL(file);
    
    reader.onload = function() {
        const imgContainer = document.createElement('div');
        imgContainer.className = 'image-container';
        
        const img = document.createElement('img');
        img.src = reader.result;
        img.setAttribute('data-filename', file.name);
        img.setAttribute('data-size', file.size);
        img.setAttribute('data-type', file.type);
        
        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-img';
        removeBtn.innerHTML = '×';
        removeBtn.onclick = function(e) {
            e.stopPropagation(); // Prevent triggering dropArea click
            imgContainer.remove();
        };
        
        imgContainer.appendChild(img);
        imgContainer.appendChild(removeBtn);
        preview.appendChild(imgContainer);
    };
}

// Form submission handler with proper image processing
document.getElementById('ppeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.submit-button');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Create a new FormData object from the form
    const formData = new FormData(this);
    
    // Process all preview images and add them to formData
    const previewImages = document.querySelectorAll('.upload-preview img');
    const imagePromises = [];
    
    previewImages.forEach((img, index) => {
        const promise = fetch(img.src)
            .then(res => res.blob())
            .then(blob => {
                // Use the original filename if available, otherwise generate one
                const filename = img.getAttribute('data-filename') || `image_${index}.jpg`;
                formData.append('images[]', blob, filename);
            });
        imagePromises.push(promise);
    });
    
    try {
        // Wait for all image processing to complete
        await Promise.all(imagePromises);
        
        // Submit the form
        const response = await fetch('submit_ppe.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message || 'Form submitted successfully!', true);
            this.reset();
            preview.innerHTML = '';
            
            // Refill today's date after form reset
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').value = today;
            document.getElementById('teamLeaderDate').value = today;
        } else {
            showMessage(data.message || 'Error submitting form. Please try again.', false);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error submitting form. Please try again.', false);
    } finally {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});