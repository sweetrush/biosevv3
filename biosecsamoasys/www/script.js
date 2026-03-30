document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('voyageForm');
    const messageDiv = document.getElementById('message');

    // Load container types
    loadContainerTypes();

    // Auto-populate ModifiedDate with current date
    const modifiedDateInput = document.getElementById('ModifiedDate');
    if (modifiedDateInput && !modifiedDateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        modifiedDateInput.value = today;
    }

    // Auto-populate ArrivalDate with current date
    const arrivalDateInput = document.getElementById('ArrivalDate');
    if (arrivalDateInput && !arrivalDateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        arrivalDateInput.value = today;
    }

    function loadContainerTypes() {
        fetch('api/get_container_types.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const vehicleTypes = document.getElementById('vehicleTypes');
                    const frozenTypes = document.getElementById('frozenTypes');
                    const freshTypes = document.getElementById('freshTypes');

                    data.data.forEach(containerType => {
                        const containerItem = document.createElement('div');
                        containerItem.className = 'cargo-type-item';

                        containerItem.innerHTML = `
                            <label for="container_${containerType.container_type_code}">
                                ${containerType.container_type_name}
                            </label>
                            <small>${containerType.description}</small>
                            <input
                                type="number"
                                id="container_${containerType.container_type_code}"
                                name="container_${containerType.container_type_code}"
                                placeholder="0"
                                min="0"
                                data-container-code="${containerType.container_type_code}">
                        `;

                        // Categorize by type
                        const code = containerType.container_type_code;
                        if (code.startsWith('Cars')) {
                            vehicleTypes.appendChild(containerItem);
                        } else if (code.startsWith('Frozen')) {
                            frozenTypes.appendChild(containerItem);
                        } else if (code.startsWith('Fresh')) {
                            freshTypes.appendChild(containerItem);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading container types:', error);
                showMessage('Failed to load cargo types. Please refresh the page.', 'error');
            });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading message
        showMessage('Submitting voyage details...', 'info');

        const formData = new FormData(form);

        fetch('api/submit_voyage.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cargoMsg = data.containers_inserted > 0
                    ? ` ${data.containers_inserted} cargo type(s) recorded.`
                    : '';

                // Store the active voyage ID in localStorage
                localStorage.setItem('activeVoyageID', data.voyage_id);
                localStorage.setItem('activeVoyageNo', document.getElementById('VoyageNo').value);
                localStorage.setItem('activeVesselID', document.getElementById('VesselID').value);

                // Refresh progress indicators
                if (typeof loadVoyageStatus === 'function') loadVoyageStatus(data.voyage_id);

                if (data.warning) {
                    showMessage(data.warning, 'warning');
                } else {
                    showMessage(`Voyage details submitted successfully!${cargoMsg} (Voyage ID: ${data.voyage_id}). Switch to Passenger Inspection tab to add inspections.`, 'success');
                }

                // Reset form after successful submission
                setTimeout(() => {
                    form.reset();
                    // Re-populate the dates
                    const today = new Date().toISOString().split('T')[0];
                    modifiedDateInput.value = today;
                    arrivalDateInput.value = today;
                }, 2000);
            } else {
                showMessage(data.message || 'Error submitting voyage details. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Network error. Please check your connection and try again.', 'error');
        });
    });

    function showMessage(text, type) {
        messageDiv.textContent = text;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';

        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    }
});
