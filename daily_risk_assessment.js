// Auto-fill date fields with current date on page load
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;
    
    // Set default dates for hazard rows
    const whenInputs = document.querySelectorAll('input[name^="when"]');
    whenInputs.forEach(input => {
        input.value = today;
    });
});

// Show response message function
function showMessage(message, isSuccess) {
    // Create message element if it doesn't exist
    if (!document.getElementById('responseMessage')) {
        const messageElement = document.createElement('div');
        messageElement.id = 'responseMessage';
        messageElement.className = 'response-message';
        
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
        
        const container = document.querySelector('.container');
        const form = document.getElementById('riskAssessmentForm');
        container.insertBefore(messageElement, form);
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
    // Check if required fields are filled
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
    
    // Validate at least one hazard is entered
    const hazardRows = document.querySelectorAll('#hazardsTable tbody tr');
    if (hazardRows.length === 0) {
        showMessage('Please add at least one hazard assessment', false);
        return false;
    }
    
    // Validate at least one team member is entered
    const teamRows = document.querySelectorAll('#teamTable tbody tr');
    if (teamRows.length === 0) {
        showMessage('Please add at least one team member', false);
        return false;
    }

    return true;
}

// Function to add incidence row
function addIncidenceRow() {
    const tbody = document.querySelector('#incidencesTable tbody');
    const rowCount = tbody.children.length + 1;
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td><input type="text" name="incidence${rowCount}"></td>
        <td><input type="text" name="lesson${rowCount}"></td>
        <td><input type="text" name="control${rowCount}"></td>
        <td><button type="button" class="btn btn-remove" onclick="removeIncidenceRow(this)">Remove</button></td>
    `;
    
    tbody.appendChild(newRow);
}

function removeIncidenceRow(button) {
    const tbody = document.querySelector('#incidencesTable tbody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
        updateIncidenceNumbers();
    } else {
        showMessage('You need to keep at least one incidence row', false);
    }
}

function updateIncidenceNumbers() {
    const rows = document.querySelectorAll('#incidencesTable tbody tr');
    rows.forEach((row, index) => {
        // Update input names
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            const baseName = input.name.replace(/\d+$/, '');
            input.name = baseName + (index + 1);
        });
    });
}

function addHazardRow() {
    const tbody = document.querySelector('#hazardsTable tbody');
    const rowCount = tbody.children.length + 1;
    const newRow = document.createElement('tr');
    const today = new Date().toISOString().split('T')[0];
    
    newRow.innerHTML = `
        <td>${rowCount}</td>
        <td><input type="text" name="hazard${rowCount}" required></td>
        <td><input type="text" name="affected${rowCount}" required></td>
        <td>
            <div class="risk-levels">
                <label><input type="radio" name="risk${rowCount}" value="H" required> H</label>
                <label><input type="radio" name="risk${rowCount}" value="M"> M</label>
                <label><input type="radio" name="risk${rowCount}" value="L"> L</label>
            </div>
        </td>
        <td><input type="text" name="existingControl${rowCount}" required></td>
        <td><input type="text" name="additionalControl${rowCount}"></td>
        <td><input type="text" name="actionBy${rowCount}"></td>
        <td><input type="date" name="when${rowCount}" value="${today}"></td>
        <td><input type="text" name="status${rowCount}" placeholder="Open"></td>
        <td><button type="button" class="btn btn-remove" onclick="removeHazardRow(this)">Remove</button></td>
    `;
    
    tbody.appendChild(newRow);
}

function removeHazardRow(button) {
    const tbody = document.querySelector('#hazardsTable tbody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
        updateHazardNumbers();
    } else {
        showMessage('You need to keep at least one hazard row', false);
    }
}

function updateHazardNumbers() {
    const rows = document.querySelectorAll('#hazardsTable tbody tr');
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
        
        // Update the names of inputs to maintain sequential numbering
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            const baseName = input.name.replace(/\d+$/, '');
            input.name = baseName + (index + 1);
        });
        
        // Update the name of the radio button group
        const radios = row.querySelectorAll('input[type="radio"]');
        radios.forEach(radio => {
            radio.name = `risk${index + 1}`;
        });
    });
}

function addTeamRow() {
    const tbody = document.querySelector('#teamTable tbody');
    const rowCount = tbody.children.length + 1;
    const newRow = document.createElement('tr');
    
    newRow.innerHTML = `
        <td>${rowCount}</td>
        <td><input type="text" name="teamName${rowCount}" required></td>
        <td><input type="text" name="teamTitle${rowCount}" required></td>
        <td><input type="text" name="teamSignature${rowCount}"></td>
        <td><button type="button" class="btn btn-remove" onclick="removeTeamRow(this)">Remove</button></td>
    `;
    
    tbody.appendChild(newRow);
}

function removeTeamRow(button) {
    const tbody = document.querySelector('#teamTable tbody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
        updateTeamNumbers();
    } else {
        showMessage('You need to keep at least one team member', false);
    }
}

function updateTeamNumbers() {
    const rows = document.querySelectorAll('#teamTable tbody tr');
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
        
        // Update input names
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            const baseName = input.name.replace(/\d+$/, '');
            input.name = baseName + (index + 1);
        });
    });
}

// Handle drag and drop functionality
const dropArea = document.getElementById('dropArea');
const imageUpload = document.getElementById('imageUpload');
const preview = document.getElementById('preview');

if (dropArea && imageUpload && preview) {
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
}

// Form submission handler with proper image processing
document.getElementById('riskAssessmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.form-actions .btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Create FormData from the form
    const formData = new FormData(this);
    
    // Add counts to form data
    const incidenceCount = document.querySelectorAll('#incidencesTable tbody tr').length;
    const hazardCount = document.querySelectorAll('#hazardsTable tbody tr').length;
    const teamCount = document.querySelectorAll('#teamTable tbody tr').length;
    
    formData.append('incidenceCount', incidenceCount);
    formData.append('hazardCount', hazardCount);
    formData.append('teamCount', teamCount);
    
    // Process preview images and add them to formData
    if (preview) {
        const previewImages = document.querySelectorAll('.upload-preview img');
        const imagePromises = [];
        
        previewImages.forEach((img, index) => {
            const promise = fetch(img.src)
                .then(res => res.blob())
                .then(blob => {
                    // Use the original filename if available, otherwise generate one
                    const filename = img.getAttribute('data-filename') || `risk_assessment_image_${index}.jpg`;
                    formData.append('images[]', blob, filename);
                });
            imagePromises.push(promise);
        });
        
        // Wait for all image processing to complete
        if (imagePromises.length > 0) {
            await Promise.all(imagePromises);
        }
    }
    
    try {
        // Submit the form
        const response = await fetch('submit_risk_assessment.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message || 'Risk assessment submitted successfully!', true);
            this.reset();
            if (preview) {
                preview.innerHTML = '';
            }
            
            // Reset default values
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').value = today;
            
            // Recreate the default rows
            const hazardTbody = document.querySelector('#hazardsTable tbody');
            const teamTbody = document.querySelector('#teamTable tbody');
            
            hazardTbody.innerHTML = `
                <tr>
                    <td>1</td>
                    <td><input type="text" name="hazard1" required></td>
                    <td><input type="text" name="affected1" required></td>
                    <td>
                        <div class="risk-levels">
                            <label><input type="radio" name="risk1" value="H" required> H</label>
                            <label><input type="radio" name="risk1" value="M"> M</label>
                            <label><input type="radio" name="risk1" value="L"> L</label>
                        </div>
                    </td>
                    <td><input type="text" name="existingControl1" required></td>
                    <td><input type="text" name="additionalControl1"></td>
                    <td><input type="text" name="actionBy1"></td>
                    <td><input type="date" name="when1" value="${today}"></td>
                    <td><input type="text" name="status1" placeholder="Open"></td>
                    <td><button type="button" class="btn btn-remove" onclick="removeHazardRow(this)">Remove</button></td>
                </tr>
            `;
            
            teamTbody.innerHTML = `
                <tr>
                    <td>1</td>
                    <td><input type="text" name="teamName1" required></td>
                    <td><input type="text" name="teamTitle1" required></td>
                    <td><input type="text" name="teamSignature1"></td>
                    <td><button type="button" class="btn btn-remove" onclick="removeTeamRow(this)">Remove</button></td>
                </tr>
            `;
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