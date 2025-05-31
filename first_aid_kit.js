// Auto-fill date fields with current date on page load
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('confirmDate').value = today;
    
    // Add initial empty row
    addRow();
});

// Show response message function
function showMessage(message, isSuccess) {
    // Create message element if it doesn't exist
    if (!document.getElementById('responseMessage')) {
        const messageElement = document.createElement('div');
        messageElement.id = 'responseMessage';
        messageElement.className = 'response-message';
        
        // Insert before the form
        const form = document.getElementById('firstAidKitChecklistForm');
        form.parentNode.insertBefore(messageElement, form);
        
        // Add response message styles if not already in document
        if (!document.getElementById('responseMessageStyles')) {
            const style = document.createElement('style');
            style.id = 'responseMessageStyles';
            style.textContent = `
                .response-message {
                    padding: 15px;
                    margin: 15px 0;
                    border-radius: 5px;
                    display: none;
                    font-weight: 500;
                    text-align: center;
                    position: relative;
                    z-index: 10;
                }
                
                .success-message {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                
                .error-message {
                    background-color: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    const messageElement = document.getElementById('responseMessage');
    messageElement.textContent = message;
    messageElement.className = 'response-message ' + (isSuccess ? 'success-message' : 'error-message');
    messageElement.style.display = 'block';
    
    // Scroll to message
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
    
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
        if (!field.value.trim()) {
            allFilled = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });

    if (!allFilled) {
        showMessage('Please fill in all required fields', false);
        return false;
    }
    
    // Check if at least one row exists
    const rows = document.querySelectorAll('#checklistTable tbody tr');
    if (rows.length === 0) {
        showMessage('Please add at least one item to the checklist', false);
        return false;
    }

    return true;
}

// Function to add a new row
function addRow() {
    addRowWithValues('', '', '');
}

// Function to add a row with specific values
function addRowWithValues(itemValue, descriptionValue, quantityValue) {
    const tbody = document.querySelector('#checklistTable tbody');
    const rowCount = tbody.children.length;
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td><input type="text" name="item${rowCount + 1}" value="${itemValue}" required></td>
        <td><input type="text" name="description${rowCount + 1}" value="${descriptionValue}" required></td>
        <td><input type="number" name="quantity${rowCount + 1}" value="${quantityValue}" min="0" required></td>
        <td><button type="button" class="btn" onclick="removeRow(this)">Remove</button></td>
    `;
    
    tbody.appendChild(newRow);
    updateRowNumbers();
}

// Function to remove a row
function removeRow(button) {
    const tbody = document.querySelector('#checklistTable tbody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
        updateRowNumbers();
    } else {
        showMessage('You must have at least one item in the checklist', false);
    }
}

// Function to update row numbers/names
function updateRowNumbers() {
    const rows = document.querySelectorAll('#checklistTable tbody tr');
    rows.forEach((row, index) => {
        // Update input names
        const inputs = row.querySelectorAll('input');
        inputs[0].name = `item${index + 1}`;
        inputs[1].name = `description${index + 1}`;
        inputs[2].name = `quantity${index + 1}`;
    });
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
    
    // Check file size (max 20MB)
    if (file.size > 20 * 1024 * 1024) {
        showMessage(`File "${file.name}" exceeds 20MB and was skipped.`, false);
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
document.getElementById('firstAidKitChecklistForm').addEventListener('submit', async function(e) {
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
    
    // Add row count to form data
    const rowCount = document.querySelectorAll('#checklistTable tbody tr').length;
    formData.append('rowCount', rowCount);
    
    // Process preview images and add them to formData
    const previewImages = document.querySelectorAll('.upload-preview img');
    const imagePromises = [];
    
    previewImages.forEach((img, index) => {
        const promise = fetch(img.src)
            .then(res => res.blob())
            .then(blob => {
                // Use the original filename if available, otherwise generate one
                const filename = img.getAttribute('data-filename') || `first_aid_image_${index}.jpg`;
                formData.append('images[]', blob, filename);
            });
        imagePromises.push(promise);
    });
    
    try {
        // Wait for all image processing to complete
        await Promise.all(imagePromises);
        
        // Submit the form
        const response = await fetch('submit_first_aid_kit_checklist.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message || 'First aid kit checklist submitted successfully!', true);
            
            // Reset the form
            this.reset();
            preview.innerHTML = '';
            
            // Add a fresh empty row
            const tbody = document.querySelector('#checklistTable tbody');
            tbody.innerHTML = '';
            addRow();
            
            // Set today's date again
            document.getElementById('confirmDate').value = new Date().toISOString().split('T')[0];
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