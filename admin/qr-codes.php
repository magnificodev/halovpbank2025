<?php
require_once '_auth.php';
require_once '../api/db.php';
require_once '_template.php';

$db = new Database();

// Get QR codes with pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$status = $_GET['status'] ?? '';
$station = $_GET['station'] ?? '';

$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];

if ($status) {
    $where .= " AND status = ?";
    $params[] = $status;
}

if ($station) {
    $where .= " AND station_id = ?";
    $params[] = $station;
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM qr_codes $where";
$totalResult = $db->fetchOne($countQuery, $params);
$total = $totalResult['total'];
$totalPages = ceil($total / $limit);

// Get QR codes
$query = "SELECT * FROM qr_codes $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$qrCodes = $db->fetchAll($query, $params);

// Get stations for filter
$stations = $db->fetchAll("SELECT DISTINCT station_id FROM qr_codes ORDER BY station_id");

renderAdminHeader('qr-codes');
?>

<style>
    .qr-codes-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .filters {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .filters-row {
        display: flex;
        gap: 15px;
        align-items: end;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }

    .filter-group select,
    .filter-group input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .btn-create {
        background: var(--accent);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-create:hover {
        background: var(--accent-600);
    }

    .qr-codes-table {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: #f9fafb;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .table tr:hover {
        background: #f9fafb;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fef3c7;
        color: #92400e;
    }

    .status-deleted {
        background: #fee2e2;
        color: #991b1b;
    }

    .qr-url {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: monospace;
        font-size: 12px;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-edit {
        background: #3b82f6;
        color: white;
    }

    .btn-edit:hover {
        background: #2563eb;
    }

    .btn-deactivate {
        background: #f59e0b;
        color: white;
    }

    .btn-deactivate:hover {
        background: #d97706;
    }

    .btn-activate {
        background: #10b981;
        color: white;
    }

    .btn-activate:hover {
        background: #059669;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin: 20px 0;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        text-decoration: none;
        color: #374151;
    }

    .pagination a:hover {
        background: #f3f4f6;
    }

    .pagination .current {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }
</style>

<div class="qr-codes-container">
    <div class="filters">
        <div class="filters-row">
            <div class="filter-group">
                <label>Status</label>
                <select name="status" onchange="filterQRCodes()">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="deleted" <?= $status === 'deleted' ? 'selected' : '' ?>>Deleted</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Station</label>
                <select name="station" onchange="filterQRCodes()">
                    <option value="">All Stations</option>
                    <?php foreach ($stations as $stationItem): ?>
                        <option value="<?= htmlspecialchars($stationItem['station_id']) ?>"
                                <?= $station === $stationItem['station_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($stationItem['station_id']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <a href="#" onclick="showCreateModal()" class="btn-create">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                    Create QR Code
                </a>
            </div>
        </div>
    </div>

    <div class="qr-codes-table">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Station</th>
                    <th>QR Code</th>
                    <th>QR URL</th>
                    <th>Status</th>
                    <th>Scans</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($qrCodes as $qrCode): ?>
                    <tr>
                        <td><?= $qrCode['id'] ?></td>
                        <td><?= htmlspecialchars($qrCode['station_id']) ?></td>
                        <td>
                            <?php if (!empty($qrCode['qr_filename'])): ?>
                                <div class="qr-image-container">
                                    <img src="../assets/qr-codes/<?= htmlspecialchars($qrCode['qr_filename']) ?>" 
                                         alt="QR Code" 
                                         class="qr-image"
                                         onclick="showQRModal('<?= htmlspecialchars($qrCode['qr_filename']) ?>')">
                                    <div class="qr-actions">
                                        <button onclick="downloadQR('<?= htmlspecialchars($qrCode['qr_filename']) ?>')" 
                                                class="btn-download" title="Download QR Code">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7,10 12,15 17,10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="no-qr">No QR Code</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="qr-url" title="<?= htmlspecialchars($qrCode['qr_url']) ?>">
                                <?= htmlspecialchars($qrCode['qr_url']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $qrCode['status'] ?>">
                                <?= ucfirst($qrCode['status']) ?>
                            </span>
                        </td>
                        <td><?= $qrCode['scan_count'] ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($qrCode['created_at'])) ?></td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="editQRCode(<?= $qrCode['id'] ?>)" class="btn-action btn-edit">
                                    Edit
                                </button>

                                <?php if ($qrCode['status'] === 'active'): ?>
                                    <button onclick="updateStatus(<?= $qrCode['id'] ?>, 'inactive')" class="btn-action btn-deactivate">
                                        Deactivate
                                    </button>
                                <?php elseif ($qrCode['status'] === 'inactive'): ?>
                                    <button onclick="updateStatus(<?= $qrCode['id'] ?>, 'active')" class="btn-action btn-activate">
                                        Activate
                                    </button>
                                <?php endif; ?>

                                <button onclick="deleteQRCode(<?= $qrCode['id'] ?>)" class="btn-action btn-delete">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&status=<?= $status ?>&station=<?= $station ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function filterQRCodes() {
    const status = document.querySelector('select[name="status"]').value;
    const station = document.querySelector('select[name="station"]').value;

    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (station) params.append('station', station);

    window.location.href = '?' + params.toString();
}

function updateStatus(id, status) {
    if (confirm(`Are you sure you want to ${status} this QR code?`)) {
        fetch('../api/qr-management.php?action=update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function deleteQRCode(id) {
    if (confirm('Are you sure you want to delete this QR code?')) {
        fetch('../api/qr-management.php?action=delete&id=' + id, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function editQRCode(id) {
    // TODO: Implement edit modal
    alert('Edit functionality coming soon!');
}

// Create QR Code Modal
function showCreateModal() {
    const modal = document.getElementById('createModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function hideCreateModal() {
    const modal = document.getElementById('createModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function createQRCode() {
    const stationId = document.getElementById('stationSelect').value;
    const notes = document.getElementById('notesInput').value;
    const expiresAt = document.getElementById('expiresInput').value;
    
    if (!stationId) {
        alert('Please select a station');
        return;
    }
    
    fetch('../api/qr-management.php?action=create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            station_id: stationId,
            notes: notes,
            expires_at: expiresAt || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideCreateModal();
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// QR Code Modal Functions
function showQRModal(filename) {
    const modal = document.getElementById('qrModal');
    const img = document.getElementById('qrModalImage');
    const downloadBtn = document.getElementById('qrModalDownload');
    
    if (modal && img) {
        img.src = '../assets/qr-codes/' + filename;
        downloadBtn.onclick = () => downloadQR(filename);
        modal.classList.add('show');
    }
}

function hideQRModal() {
    const modal = document.getElementById('qrModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

function downloadQR(filename) {
    const link = document.createElement('a');
    link.href = '../assets/qr-codes/' + filename;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<!-- Create QR Code Modal -->
<div id="createModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New QR Code</h3>
            <button onclick="hideCreateModal()" class="close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="stationSelect">Station:</label>
                <select id="stationSelect" required>
                    <option value="">Choose a station...</option>
                    <option value="HALLO_GLOW">HALLO GLOW</option>
                    <option value="HALLO_SOLUTION">HALLO SOLUTION</option>
                    <option value="HALLO_WIN">HALLO WIN</option>
                    <option value="HALLO_SHOP">HALLO SHOP</option>
                    <option value="HALLO_EXPERIENCE">HALLO EXPERIENCE</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notesInput">Notes (optional):</label>
                <textarea id="notesInput" placeholder="Add notes for this QR code..."></textarea>
            </div>
            <div class="form-group">
                <label for="expiresInput">Expires At (optional):</label>
                <input type="datetime-local" id="expiresInput">
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="hideCreateModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="createQRCode()" class="btn btn-primary">Create QR Code</button>
        </div>
    </div>
</div>

<!-- QR Code View Modal -->
<div id="qrModal" class="qr-modal">
    <div class="qr-modal-content">
        <button class="qr-modal-close" onclick="hideQRModal()">&times;</button>
        <img id="qrModalImage" class="qr-modal-image" alt="QR Code">
        <div class="qr-modal-actions">
            <button id="qrModalDownload" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7,10 12,15 17,10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Download QR Code
            </button>
            <button onclick="hideQRModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
}

.close:hover {
    color: #374151;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e5e7eb;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #374151;
}

.form-group select,
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.form-group textarea {
    height: 80px;
    resize: vertical;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-primary {
    background: var(--accent);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-600);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

    .btn-secondary:hover {
        background: #4b5563;
    }

    /* QR Code Image Styles */
    .qr-image-container {
        position: relative;
        display: inline-block;
    }

    .qr-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s ease;
        border: 1px solid #e5e7eb;
    }

    .qr-image:hover {
        transform: scale(1.1);
    }

    .qr-actions {
        position: absolute;
        top: -5px;
        right: -5px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .qr-image-container:hover .qr-actions {
        opacity: 1;
    }

    .btn-download {
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .btn-download:hover {
        background: var(--accent-600);
    }

    .no-qr {
        color: #9ca3af;
        font-style: italic;
    }

    /* QR Modal Styles */
    .qr-modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        justify-content: center;
        align-items: center;
    }

    .qr-modal.show {
        display: flex;
    }

    .qr-modal-content {
        background: white;
        border-radius: 12px;
        padding: 20px;
        max-width: 90%;
        max-height: 90%;
        text-align: center;
        position: relative;
    }

    .qr-modal-image {
        max-width: 400px;
        max-height: 400px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .qr-modal-close {
        position: absolute;
        top: 10px;
        right: 15px;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
    }

    .qr-modal-close:hover {
        color: #374151;
    }

    .qr-modal-actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
        justify-content: center;
    }
</style>

<?php renderAdminFooter(); ?>
