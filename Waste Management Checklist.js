// Auto-fill date fields with current date
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('collectionDate').value = today;
    document.getElementById('confirmDate').value = today;
    
    // Initialize all photo upload areas
    setupPhotoUpload('beforeDropArea', 'beforeImageUpload', 'beforePreview');
    setupPhotoUpload('afterDropArea', 'afterImageUpload', 'afterPreview');
    setupPhotoUpload('collectionDropArea', 'collectionImageUpload', 'collectionPreview');
    setupPhotoUpload('disposalDropArea', 'disposalImageUpload', 'disposalPreview');
    
    // Initialize form submission handler - STANDARD FORM SUBMISSION
    document.getElementById('wasteManagementForm').addEventListener('submit', function(e) {
        // Validate form
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Update waste count
        document.getElementById('wasteCount').value = document.querySelectorAll('#wasteTable tbody tr').length;
        
        // Allow form to submit normally
        return true;
    });
    
    // Add alert styles
    addAlertStyles();
});

// Function to handle adding new rows
function addRow() {
    const tbody = document.querySelector('#wasteTable tbody');
    const rowCount = tbody.children.length;
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td>
            <select name="wasteType${rowCount + 1}" required>
                <option value="">Select Type</option>
                <option value="General">General Waste</option>
                <option value="Plastic">Plastic</option>
                <option value="Metal">Metal</option>
                <option value="Paper">Paper/Cardboard</option>
                <option value="Organic">Organic/Food</option>
                <option value="Hazardous">Hazardous</option>
                <option value="Electronic">Electronic</option>
                <option value="Construction">Construction</option>
                <option value="Other">Other</option>
            </select>
        </td>
        <td><input type="number" name="quantity${rowCount + 1}" required></td>
        <td>
            <select name="disposalMethod${rowCount + 1}" required>
                <option value="">Select Method</option>
                <option value="Landfill">Landfill</option>
                <option value="Recycling">Recycling</option>
                <option value="Composting">Composting</option>
                <option value="Incineration">Incineration</option>
                <option value="SpecialTreatment">Special Treatment</option>
            </select>
        </td>
        <td><button type="button" class="btn" onclick="removeRow(this)">Remove</button></td>
    `;
    
    tbody.appendChild(newRow);
}

// Function to remove rows
function removeRow(button) {
    const tbody = document.querySelector('#wasteTable tbody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
    } else {
        showMessage('Cannot remove the last row', false);
    }
}

// Show message function
function showMessage(message, isSuccess) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert ${isSuccess ? 'alert-success' : 'alert-danger'}`;
    messageDiv.textContent = message;
    
    const container = document.querySelector('.container');
    container.insertBefore(messageDiv, container.firstChild);
    
    setTimeout(() => messageDiv.remove(), 5000);
}

// Setup drag and drop for each photo category
function setupPhotoUpload(dropAreaId, inputId, previewId) {
    const dropArea = document.getElementById(dropAreaId);
    const imageUpload = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('highlight'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('highlight'), false);
    });

    dropArea.addEventListener('drop', handleDrop, false);
    imageUpload.addEventListener('change', () => handleFiles(imageUpload.files));
    dropArea.addEventListener('click', () => imageUpload.click());

    function handleDrop(e) {
        handleFiles(e.dataTransfer.files);
    }

    function handleFiles(files) {
        [...files].forEach(file => {
            if (validateFile(file)) {
                previewFile(file, preview);
            }
        });
    }
}

function validateFile(file) {
    if (!file.type.startsWith('image/')) {
        showMessage(`${file.name} is not an image file`, false);
        return false;
    }
    if (file.size > 5 * 1024 * 1024) {
        showMessage(`${file.name} is too large (max 5MB)`, false);
        return false;
    }
    return true;
}

function previewFile(file, previewElement) {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    
    reader.onload = function() {
        const imgContainer = document.createElement('div');
        imgContainer.className = 'image-container';
        
        const img = document.createElement('img');
        img.src = reader.result;
        
        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-img';
        removeBtn.innerHTML = '×';
        removeBtn.onclick = function(e) {
            e.preventDefault();
            imgContainer.remove();
        };
        
        imgContainer.appendChild(img);
        imgContainer.appendChild(removeBtn);
        previewElement.appendChild(imgContainer);
    };
}

// Validate form before submission
function validateForm() {
    // Check if at least one waste type is entered
    const wasteRows = document.querySelectorAll('#wasteTable tbody tr');
    if (wasteRows.length === 0) {
        showMessage('Please add at least one waste type', false);
        return false;
    }
    
    // Validate that photos are uploaded for each category
    const photoCategories = [
        {id: 'beforePreview', name: 'before collection'},
        {id: 'afterPreview', name: 'after collection'},
        {id: 'collectionPreview', name: 'collection area'},
        {id: 'disposalPreview', name: 'disposal method'}
    ];
    
    let missingPhotos = [];
    photoCategories.forEach(category => {
        if (document.getElementById(category.id).children.length === 0) {
            missingPhotos.push(category.name);
        }
    });
    
    if (missingPhotos.length > 0) {
        showMessage(`Please upload photos for: ${missingPhotos.join(', ')}`, false);
        return false;
    }
    
    return true;
}

// Add CSS classes for alerts if not already in stylesheet
function addAlertStyles() {
    if (!document.getElementById('alert-styles')) {
        const style = document.createElement('style');
        style.id = 'alert-styles';
        style.textContent = `
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid transparent;
                border-radius: 4px;
                text-align: center;
                font-weight: bold;
            }
            .alert-success {
                color: #155724;
                background-color: #d4edda;
                border-color: #c3e6cb;
            }
            .alert-danger {
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
            }
        `;
        document.head.appendChild(style);
    }
}