<?php
define('CSS_CACHE_BUST', 2);
require_once 'api/auth_check.php';
$pageTitle = 'Permit Templates - Samoa Biosecurity System';
$currentPage = 'permit_templates';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<style>
    .templates-header {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        color: white;
        padding: 40px;
        text-align: center;
    }

    .templates-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .templates-header p {
        font-size: 1.1em;
        opacity: 0.9;
    }

    .templates-content {
        padding: 40px;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .search-filters {
        display: flex;
        gap: 15px;
        flex: 1;
        flex-wrap: wrap;
    }

    .search-input {
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1em;
        min-width: 250px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filter-select {
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1em;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn {
        padding: 12px 24px;
        font-size: 1em;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-sm {
        padding: 8px 14px;
        font-size: 0.9em;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-edit {
        background: #4299e1;
        color: white;
    }

    .btn-edit:hover {
        background: #3182ce;
        transform: translateY(-1px);
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
        color: #4a5568;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%);
    }

    .templates-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .templates-table th {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        color: white;
        padding: 14px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #667eea;
    }

    .templates-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .templates-table tr:hover {
        background: #f7fafc;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .actions-cell {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background: white;
        margin: 2% auto;
        padding: 0;
        border-radius: 12px;
        width: 95%;
        max-width: 900px;
        max-height: 90vh;
        overflow: hidden;
        animation: slideIn 0.3s ease;
    }

    .modal-header {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        color: white;
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5em;
    }

    .close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5em;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.3s ease;
    }

    .close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .modal-body {
        padding: 30px;
        max-height: 75vh;
        overflow-y: auto;
    }

    .form-section {
        margin-bottom: 25px;
        padding: 20px;
        background: #f7fafc;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .form-section h3 {
        color: #2d3748;
        margin-bottom: 15px;
        font-size: 1.2em;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1em;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .modal-footer {
        padding: 20px 30px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.5em;
        margin-bottom: 10px;
        color: #374151;
    }

    .empty-state p {
        font-size: 1.1em;
        margin-bottom: 30px;
    }

    .required {
        color: #dc2626;
    }

    .delete-confirm-modal .modal-header {
        background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
        border-radius: 12px 12px 0 0;
    }

    .delete-confirm-modal .modal-content {
        max-width: 500px;
    }

    .delete-warning {
        text-align: center;
        padding: 20px;
    }

    .warning-icon {
        font-size: 3em;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-filters {
            flex-direction: column;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .templates-table {
            font-size: 0.9em;
        }

        .templates-table th,
        .templates-table td {
            padding: 10px 6px;
        }

        .actions-cell {
            flex-direction: column;
            gap: 4px;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>
<?php include 'includes/layout-start.php'; ?>
                <div class="templates-header">
                    <h1>📄 Permit Templates</h1>
                    <p>Manage import permit templates with pre-configured requirements</p>
                </div>

                <div class="templates-content">
                    <div class="action-bar">
                        <div class="search-filters">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search templates...">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="openTemplateModal()">
                                <span>➕</span>
                                <span>New Template</span>
                            </button>
                        </div>
                    </div>

                    <div id="templatesTableContainer">
                        <div class="empty-state">
                            <div class="empty-state-icon">📄</div>
                            <h3>No Permit Templates Found</h3>
                            <p>Get started by creating a permit template with pre-configured requirements.</p>
                        </div>
                    </div>
                </div>

                <!-- Create/Edit Template Modal -->
                <div id="templateModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 id="modalTitle">Create New Permit Template</h2>
                            <button class="close" onclick="closeModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="templateForm">
                                <input type="hidden" id="templateId" name="id">
                                <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                <div class="form-section">
                                    <h3>📄 Template Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="templateName">Permit Template Name <span class="required">*</span></label>
                                            <input type="text" id="templateName" name="template_name" required placeholder="e.g., Agricultural Products">
                                        </div>
                                        <div class="form-group">
                                            <label for="iraReference">IRA Reference <span class="required">*</span></label>
                                            <input type="text" id="iraReference" name="ira_reference" required placeholder="e.g., IRA-AG-001">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" id="inUse" name="in_use" value="1" checked>
                                            In Use
                                        </label>
                                        <small><em>Uncheck to mark this template as inactive</em></small>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>📦 Commodities</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="commodity1">Commodity 1</label>
                                            <textarea id="commodity1" name="commodity_1" rows="4" placeholder="Primary commodity category"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="commodity2">Commodity 2</label>
                                            <textarea id="commodity2" name="commodity_2" rows="4" placeholder="Secondary commodity category (optional)"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>📋 Import Requirements</h3>
                                    <div class="form-group">
                                        <label for="importRequirements">Import Requirements</label>
                                        <textarea id="importRequirements" name="import_requirements" rows="6" placeholder="Describe the specific import requirements and conditions..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="saveTemplate()">Save Template</button>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteConfirmModal" class="modal delete-confirm-modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>🗑️ Confirm Delete</h2>
                            <button class="close" onclick="closeDeleteModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="delete-warning">
                                <div class="warning-icon">⚠️</div>
                                <h3>Are you sure you want to delete this template?</h3>
                                <div id="deleteTemplateName" style="margin: 15px 0; padding: 10px; background: #f7fafc; border-radius: 4px; font-weight: 600;"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()" style="background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%); color: white;">
                                <span>🗑️</span> Delete Template
                            </button>
                        </div>
                    </div>
                </div>
<?php include 'includes/layout-end.php'; ?>
    <script>
        let templates = [];
        let deleteTargetId = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadTemplates();
            document.getElementById('searchInput').addEventListener('input', loadTemplates);
            document.getElementById('statusFilter').addEventListener('change', loadTemplates);
        });

        async function loadTemplates() {
            try {
                const search = document.getElementById('searchInput').value;
                const inUse = document.getElementById('statusFilter').value;

                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (inUse !== '') params.append('in_use', inUse);

                const response = await fetch(`api/get_permit_templates.php?${params}`);
                const result = await response.json();

                if (result.success) {
                    templates = result.data;
                    renderTemplatesTable(templates);
                } else {
                    showNotification('Failed to load templates: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error loading templates:', error);
                showNotification('Error loading templates', 'error');
            }
        }

        function renderTemplatesTable(data) {
            const container = document.getElementById('templatesTableContainer');

            if (!data || data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📄</div>
                        <h3>No Permit Templates Found</h3>
                        <p>Get started by creating a permit template with pre-configured requirements.</p>
                    </div>
                `;
                return;
            }

            const table = `
                <table class="templates-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Template Name</th>
                            <th>IRA Reference</th>
                            <th>Commodities</th>
                            <th>In Use</th>
                            <th>Last Modified</th>
                            <th>Modified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(t => createTemplateRow(t)).join('')}
                    </tbody>
                </table>
            `;

            container.innerHTML = table;
        }

        function createTemplateRow(t) {
            const inUse = t.in_use == 1;
            const statusClass = inUse ? 'status-active' : 'status-inactive';
            const statusText = inUse ? 'Active' : 'Inactive';
            const commodities = [t.commodity_1, t.commodity_2].filter(Boolean).join(', ') || '—';
            const modDate = t.modified_date ? new Date(t.modified_date).toLocaleDateString('en-GB') : '—';

            return `
                <tr>
                    <td><strong>#${t.id}</strong></td>
                    <td>${t.template_name}</td>
                    <td><code style="background: #f7fafc; padding: 3px 8px; border-radius: 4px;">${t.ira_reference}</code></td>
                    <td>${commodities}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td>${modDate}</td>
                    <td>${t.modified_by}</td>
                    <td class="actions-cell">
                        <button class="btn-sm btn-edit" onclick="editTemplate(${t.id})" title="Edit">✏️ Edit</button>
                        <button class="btn-sm btn-delete" onclick="deleteTemplate(${t.id}, '${(t.template_name || '').replace(/'/g, "\\'")}')" title="Delete">🗑️ Delete</button>
                    </td>
                </tr>
            `;
        }

        function openTemplateModal() {
            document.getElementById('templateForm').reset();
            document.getElementById('templateId').value = '';
            document.getElementById('inUse').checked = true;
            document.getElementById('modalTitle').textContent = 'Create New Permit Template';
            document.getElementById('templateModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('templateModal').style.display = 'none';
            document.getElementById('templateForm').reset();
        }

        async function editTemplate(id) {
            try {
                const response = await fetch(`api/edit_permit_template.php?id=${id}`);
                const result = await response.json();

                if (result.success) {
                    const t = result.data;
                    document.getElementById('templateId').value = t.id;
                    document.getElementById('templateName').value = t.template_name;
                    document.getElementById('iraReference').value = t.ira_reference;
                    document.getElementById('inUse').checked = t.in_use == 1;
                    document.getElementById('commodity1').value = t.commodity_1 || '';
                    document.getElementById('commodity2').value = t.commodity_2 || '';
                    document.getElementById('importRequirements').value = t.import_requirements || '';
                    document.getElementById('modalTitle').textContent = 'Edit Permit Template';
                    document.getElementById('templateModal').style.display = 'flex';
                } else {
                    showNotification('Failed to load template: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error loading template:', error);
                showNotification('Error loading template', 'error');
            }
        }

        async function saveTemplate() {
            const form = document.getElementById('templateForm');
            const formData = new FormData(form);

            try {
                const response = await fetch('api/submit_permit_template.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    closeModal();
                    loadTemplates();
                } else {
                    if (result.errors) {
                        let errorMsg = result.message + '\n';
                        Object.values(result.errors).forEach(v => errorMsg += '- ' + v + '\n');
                        alert(errorMsg);
                    } else {
                        showNotification('Error: ' + result.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Error saving template:', error);
                showNotification('Error saving template', 'error');
            }
        }

        function deleteTemplate(id, name) {
            deleteTargetId = id;
            document.getElementById('deleteTemplateName').textContent = `Template: ${name}`;
            document.getElementById('deleteConfirmModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
            deleteTargetId = null;
        }

        async function confirmDelete() {
            if (!deleteTargetId) return;

            try {
                const response = await fetch('api/edit_permit_template.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: deleteTargetId })
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    closeDeleteModal();
                    loadTemplates();
                } else {
                    showNotification('Error: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting template:', error);
                showNotification('Error deleting template', 'error');
            }
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            const bgColor = type === 'error' ? '#e53e3e' : '#27ae60';
            notification.style.cssText = `position: fixed; top: 20px; right: 20px; background: ${bgColor}; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 10000;`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
<?php include 'includes/layout-end.php'; ?>
