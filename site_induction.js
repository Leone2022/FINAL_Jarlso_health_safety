// site_induction.js

document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill today's date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('inductionDate').value = today;
});

let rowCount = 1;

function addRow() {
    rowCount++;
    const tableBody = document.querySelector('#visitorLogTable tbody');
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td><input type="date" name="date${rowCount}" required></td>
        <td><input type="time" name="time${rowCount}" required></td>
        <td><input type="text" name="name${rowCount}" required></td>
        <td><input type="text" name="company${rowCount}" required></td>
        <td><input type="text" name="signature${rowCount}" required></td>
        <td><button type="button" class="btn" onclick="removeRow(this)">Remove</button></td>
    `;
    
    tableBody.appendChild(newRow);
}

function removeRow(button) {
    if (document.querySelectorAll('#visitorLogTable tbody tr').length > 1) {
        button.closest('tr').remove();
    } else {
        alert('At least one visitor entry is required.');
    }
}

// File upload handling
const dropArea = document.getElementById('dropArea');
const fileInput = document.getElementById('imageUpload');
const preview = document.getElementById('preview');

function initializeFileUpload() {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    dropArea.addEventListener('drop', handleDrop, false);
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    dropArea.addEventListener('click', () => fileInput.click());
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight() {
    dropArea.classList.add('highlight');
}

function unhighlight() {
    dropArea.classList.remove('highlight');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

function handleFiles(files) {
    [...files].forEach(file => {
        if (validateFile(file)) {
            previewFile(file);
        }
    });
}

function validateFile(file) {
    // Check if it's an image
    if (!file.type.startsWith('image/')) {
        alert('Please upload only image files.');
        return false;
    }
    
    // Check file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        alert('File size should not exceed 5MB.');
        return false;
    }
    
    return true;
}

function previewFile(file) {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    
    reader.onload = function() {
        const imgContainer = document.createElement('div');
        imgContainer.className = 'image-container';
        
        const img = document.createElement('img');
        img.src = reader.result;
        img.setAttribute('data-file', file.name);
        
        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-img';
        removeBtn.innerHTML = '×';
        removeBtn.onclick = function(e) {
            e.preventDefault();
            imgContainer.remove();
        };
        
        imgContainer.appendChild(img);
        imgContainer.appendChild(removeBtn);
        preview.appendChild(imgContainer);
    };
}

// Form submission
document.getElementById('inductionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(this);
        
        // Add image files
        const images = document.querySelectorAll('.upload-preview img');
        const imagePromises = Array.from(images).map((img, index) => 
            fetch(img.src)
                .then(res => res.blob())
                .then(blob => {
                    formData.append(`images[]`, blob, img.getAttribute('data-file') || `image${index}.jpg`);
                })
        );
        
        await Promise.all(imagePromises);
        
        const response = await fetch('submit_induction.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Site induction form submitted successfully!');
            // Reset form
            this.reset();
            preview.innerHTML = '';
            // Keep only one row in visitor log
            const tbody = document.querySelector('#visitorLogTable tbody');
            while (tbody.children.length > 1) {
                tbody.removeChild(tbody.lastChild);
            }
        } else {
            throw new Error(result.message || 'Submission failed');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error submitting form: ' + error.message);
    }
});

// Initialize file upload handlers
initializeFileUpload();