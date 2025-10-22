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
$totalQRCodes = $db->fetchOne($countQuery, $params)['total'];
$totalPages = ceil($totalQRCodes / $limit);

// Get QR codes
$qrCodes = $db->fetchAll("SELECT * FROM qr_codes $where ORDER BY created_at DESC LIMIT ? OFFSET ?", array_merge($params, [$limit, $offset]));

renderAdminHeader('qr-codes');
?>

<div class="header-content">
    <h2>QR Codes Management</h2>
    <button class="btn-create" onclick="showCreateModal()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Create New QR Code
    </button>
</div>

<div class="filters">
    <div class="filter-group">
        <label for="statusFilter">Status:</label>
        <select id="statusFilter" onchange="applyFilter()">
            <option value="">All Status</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="deleted" <?= $status === 'deleted' ? 'selected' : '' ?>>Deleted</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="stationFilter">Station:</label>
        <select id="stationFilter" onchange="applyFilter()">
            <option value="">All Stations</option>
            <?php foreach (STATIONS as $key => $value): ?>
                <option value="<?= $key ?>" <?= $station === $key ? 'selected' : '' ?>><?= $value['name'] ?></option>
            <?php endforeach; ?>
        </select>
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
                                <!-- Use PNG API for display -->
                                <img src="../api/qr-png.php?data=<?= urlencode($qrCode['qr_url']) ?>&size=100"
                                     alt="QR Code"
                                     class="qr-image"
                                     onclick="showQRModal('<?= htmlspecialchars($qrCode['qr_url']) ?>')">
                                <div class="qr-actions">
                                    <button onclick="downloadQR('<?= htmlspecialchars($qrCode['qr_url']) ?>')"
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
                    <td><?= (new DateTime($qrCode['created_at']))->format('Y-m-d H:i') ?></td>
                    <td class="actions">
                        <button onclick="editQRCode(<?= $qrCode['id'] ?>)" class="btn-edit">Edit</button>
                        <button onclick="toggleQRStatus(<?= $qrCode['id'] ?>, '<?= $qrCode['status'] ?>')"
                                class="btn-toggle btn-<?= $qrCode['status'] === 'active' ? 'deactivate' : 'activate' ?>">
                            <?= $qrCode['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                        <button onclick="deleteQRCode(<?= $qrCode['id'] ?>)" class="btn-delete">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Create QR Code Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New QR Code</h3>
            <span class="close" onclick="hideCreateModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="createForm">
                <div class="form-group">
                    <label for="stationSelect">Station:</label>
                    <select id="stationSelect" required>
                        <option value="">Select Station</option>
                        <?php foreach (STATIONS as $key => $value): ?>
                            <option value="<?= $key ?>"><?= $value['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notesInput">Notes (optional):</label>
                    <textarea id="notesInput" placeholder="Add notes for this QR code..."></textarea>
                </div>
                <div class="form-group">
                    <label for="expiresAtInput">Expires At (optional):</label>
                    <input type="datetime-local" id="expiresAtInput">
                </div>
                <div class="form-actions">
                    <button type="button" onclick="hideCreateModal()" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-create">Create QR Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- QR Code View Modal -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>QR Code View</h3>
            <span class="close" onclick="hideQRModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="qr-modal-image-container">
                <img id="qrModalImage" src="" alt="QR Code" class="qr-modal-image">
            </div>
            <div class="qr-modal-actions">
                <button onclick="downloadCurrentQR()" class="btn-download">Download QR Code</button>
            </div>
        </div>
    </div>
</div>

<style>
.qr-image-container {
    position: relative;
    display: inline-block;
}

.qr-image {
    width: 60px;
    height: 60px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
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
    background: #10b981;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.btn-download:hover {
    background: #059669;
}

.no-qr {
    color: #9ca3af;
    font-style: italic;
}

.qr-modal-image-container {
    text-align: center;
    margin-bottom: 20px;
}

.qr-modal-image {
    max-width: 300px;
    max-height: 300px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.qr-modal-actions {
    text-align: center;
}

.qr-url {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<script>
// Global functions for onclick attributes - must be defined before DOM
function showCreateModal() {
    document.getElementById('createModal').style.display = 'block';
}

function hideCreateModal() {
    document.getElementById('createModal').style.display = 'none';
    document.getElementById('createForm').reset();
}

function showQRModal(qrUrl) {
    document.getElementById('qrModalImage').src = '../api/qr-png.php?data=' + encodeURIComponent(qrUrl) + '&size=300';
    document.getElementById('qrModal').style.display = 'block';
}

function hideQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    let currentQRUrl = '';

    document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const stationId = document.getElementById('stationSelect').value;
    const notes = document.getElementById('notesInput').value;
    const expiresAt = document.getElementById('expiresAtInput').value;

    if (!stationId) {
        alert('Please select a station');
        return;
    }

    try {
        const response = await fetch('../api/endroid-qr-generator.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                station_id: stationId,
                notes: notes,
                expires_at: expiresAt || null
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('QR Code created successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

function applyFilter() {
    const status = document.getElementById('statusFilter').value;
    const station = document.getElementById('stationFilter').value;

    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (station) params.append('station', station);

    window.location.href = '?' + params.toString();
}

function editQRCode(id) {
    // TODO: Implement edit functionality
    alert('Edit functionality coming soon');
}

function toggleQRStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

    if (confirm(`Are you sure you want to ${newStatus} this QR code?`)) {
        // TODO: Implement status toggle
        alert('Status toggle functionality coming soon');
    }
}

function deleteQRCode(id) {
    if (confirm('Are you sure you want to delete this QR code? This action cannot be undone.')) {
        // TODO: Implement delete functionality
        alert('Delete functionality coming soon');
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const createModal = document.getElementById('createModal');
    const qrModal = document.getElementById('qrModal');

    if (event.target === createModal) {
        hideCreateModal();
    }
    if (event.target === qrModal) {
        hideQRModal();
    }
}
}); // End DOMContentLoaded
</script>

<?php renderAdminFooter(); ?>
