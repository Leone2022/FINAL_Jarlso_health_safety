// Auto-fill date fields with current date on page load
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;
    document.getElementById('declarationDate').value = today;
});

// Show response message function with enhanced debugging
function showMessage(message, isSuccess, debugInfo) {
    const existingMessage = document.getElementById('responseMessage');
    
    if (existingMessage) {
        existingMessage.innerHTML = message;
        if (debugInfo) {
            existingMessage.innerHTML += '<hr><div style="text-align: left; max-height: 200px; overflow-y: auto;"><strong>Debug Information:</strong><pre>' + JSON.stringify(debugInfo, null, 2) + '</pre></div>';
        }
        existingMessage.className = 'response-message ' + (isSuccess ? 'success-message' : 'error-message');
        existingMessage.style.display = 'block';
    } else {
        const messageElement = document.createElement('div');
        messageElement.id = 'responseMessage';
        messageElement.innerHTML = message;
        if (debugInfo) {
            messageElement.innerHTML += '<hr><div style="text-align: left; max-height: 200px; overflow-y: auto;"><strong>Debug Information:</strong><pre>' + JSON.stringify(debugInfo, null, 2) + '</pre></div>';
        }
        messageElement.className = 'response-message ' + (isSuccess ? 'success-message' : 'error-message');
        
        const container = document.querySelector('.container');
        const form = document.getElementById('toolboxForm');
        container.insertBefore(messageElement, form);
    }
    
    // Make error message stay visible
    if (!isSuccess) {
        // Don't auto-hide error messages
    } else {
        // Hide success messages after 5 seconds
        setTimeout(() => {
            const messageElement = document.getElementById('responseMessage');
            if (messageElement) {
                messageElement.style.display = 'none';
            }
        }, 5000);
    }
    
    // Scroll to message
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Function to handle adding new rows
function addRow() {
    const tbody = document.querySelector('#attendance tbody');
    const rowCount = tbody.children.length;
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td>${rowCount + 1}</td>
        <td><input type="text" name="name${rowCount + 1}" required></td>
        <td><input type="text" name="role${rowCount + 1}" required></td>
        <td><input type="text" name="contact${rowCount + 1}" required></td>
        <td><input type="text" name="signature${rowCount + 1}" required></td>
        <td><button type="button" class="btn" onclick="removeRow(this)">Remove</button></td>
    `;
    
    tbody.appendChild(newRow);
    updateRowNumbers();
}

// Function to remove rows
function removeRow(button) {
    const tbody = document.querySelector('#attendance tbody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
        updateRowNumbers();
    } else {
        showMessage('Cannot remove the last attendee row', false);
    }
}

// Function to update row numbers
function updateRowNumbers() {
    const rows = document.querySelectorAll('#attendance tbody tr');
    rows.forEach((row, index) => {
        row.firstElementChild.textContent = index + 1;
        
        // Update input names to ensure they match the row number
        const inputs = row.querySelectorAll('input[type="text"]');
        const nameInput = inputs[0];
        const roleInput = inputs[1];
        const contactInput = inputs[2];
        const signatureInput = inputs[3];
        
        nameInput.name = `name${index + 1}`;
        roleInput.name = `role${index + 1}`;
        contactInput.name = `contact${index + 1}`;
        signatureInput.name = `signature${index + 1}`;
    });
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
                radioButtons.forEach(radio => {
                    radio.parentElement.classList.add('error');
                });
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

// Prevent default behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Highlight drop area
['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropArea.classList.add('highlight');
}

function unhighlight(e) {
    dropArea.classList.remove('highlight');
}

// Handle dropped files
dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

// Handle file selection
imageUpload.addEventListener('change', function() {
    handleFiles(this.files);
});

dropArea.addEventListener('click', () => {
    imageUpload.click();
});

function handleFiles(files) {
    [...files].forEach(previewFile);
}

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

// Helper function to log form data for debugging
function logFormData(formData) {
    console.log("Form data being sent:");
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
}

// Direct form submission (bypassing fetch for debugging)
function submitFormDirectly() {
    document.getElementById('toolboxForm').submit();
}

// Button to toggle direct form submission
function addDirectSubmitButton() {
    const container = document.querySelector('.container');
    const submitBtn = document.querySelector('.submit-button');
    
    const directSubmitBtn = document.createElement('button');
    directSubmitBtn.type = 'button';
    directSubmitBtn.className = 'btn';
    directSubmitBtn.style.marginTop = '10px';
    directSubmitBtn.style.backgroundColor = '#dc3545';
    directSubmitBtn.textContent = 'Submit Directly (Debug)';
    directSubmitBtn.onclick = submitFormDirectly;
    
    submitBtn.parentNode.insertBefore(directSubmitBtn, submitBtn.nextSibling);
}

// Add the direct submit button when the page loads
document.addEventListener('DOMContentLoaded', function() {
    addDirectSubmitButton();
});

// Form submission handler with enhanced error handling
document.getElementById('toolboxForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.submit-button');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Create FormData from the form
    const formData = new FormData(this);
    
    // Log form data for debugging
    logFormData(formData);
    
    // Process preview images and add them to formData
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
        const response = await fetch('submit_toolbox.php', {
            method: 'POST',
            body: formData
        });
        
        let data;
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // If not JSON, get the raw text
            const text = await response.text();
            showMessage(`Non-JSON response received: <pre>${text}</pre>`, false);
            throw new Error('Non-JSON response received');
        }
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        if (data.success) {
            showMessage(data.message || 'Form submitted successfully!', true, data.debug_info);
            this.reset();
            preview.innerHTML = '';
            
            // Reset attendance table to have just one row
            const tbody = document.querySelector('#attendance tbody');
            tbody.innerHTML = `
                <tr>
                    <td>1</td>
                    <td><input type="text" name="name1" required></td>
                    <td><input type="text" name="role1" required></td>
                    <td><input type="text" name="contact1" required></td>
                    <td><input type="text" name="signature1" required></td>
                    <td><button type="button" class="btn" onclick="removeRow(this)">Remove</button></td>
                </tr>
            `;
            
            // Refill today's date after form reset
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').value = today;
            document.getElementById('declarationDate').value = today;
        } else {
            showMessage(data.message || 'Error submitting form. Please try again.', false, data.debug_info);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage(`Error submitting form: ${error.message}.<br>See console for more details.`, false);
    } finally {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});