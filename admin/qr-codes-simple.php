<?php
require_once '_auth.php';
require_once '../api/db.php';
require_once '_template.php';

$db = new Database();

// Get QR codes
$qrCodes = $db->fetchAll("SELECT * FROM qr_codes ORDER BY created_at DESC LIMIT 10");

// Define stations if not already defined
if (!defined('STATIONS')) {
    define('STATIONS', [
        'HALLO_GLOW' => ['name' => 'HALLO GLOW', 'description' => 'Trải nghiệm ánh sáng'],
        'HALLO_SOLUTION' => ['name' => 'HALLO SOLUTION', 'description' => 'Giải pháp thông minh'],
        'HALLO_WIN' => ['name' => 'HALLO WIN', 'description' => 'Chiến thắng vinh quang'],
        'HALLO_SHOP' => ['name' => 'HALLO SHOP', 'description' => 'Cửa hàng đặc biệt']
    ]);
}

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
        <select id="statusFilter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="deleted">Deleted</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="stationFilter">Station:</label>
        <select id="stationFilter">
            <option value="">All Stations</option>
            <?php foreach (STATIONS as $key => $value): ?>
                <option value="<?= $key ?>"><?= $value['name'] ?></option>
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
            <?php if (empty($qrCodes)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                        No QR codes found. <a href="#" onclick="showCreateModal(); return false;">Create your first QR code</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($qrCodes as $qrCode): ?>
                    <tr>
                        <td><?= htmlspecialchars($qrCode['id'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($qrCode['station_id'] ?? 'N/A') ?></td>
                        <td>
                            <div class="qr-image-container">
                                <img src="../api/qr-png.php?data=<?= urlencode($qrCode['qr_url'] ?? '') ?>&size=100"
                                     alt="QR Code"
                                     class="qr-image"
                                     onclick="showQRModal('<?= urlencode($qrCode['qr_url'] ?? '') ?>')">
                            </div>
                        </td>
                        <td>
                            <div class="qr-url" title="<?= htmlspecialchars($qrCode['qr_url'] ?? '') ?>">
                                <?= htmlspecialchars($qrCode['qr_url'] ?? 'N/A') ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $qrCode['status'] ?? 'unknown' ?>">
                                <?= ucfirst($qrCode['status'] ?? 'Unknown') ?>
                            </span>
                        </td>
                        <td><?= $qrCode['scan_count'] ?? 0 ?></td>
                        <td><?= isset($qrCode['created_at']) ? (new DateTime($qrCode['created_at']))->format('Y-m-d H:i') : 'N/A' ?></td>
                        <td class="actions">
                            <button onclick="editQRCode(<?= $qrCode['id'] ?? 0 ?>)" class="btn-edit">Edit</button>
                            <button onclick="updateQRStatus(<?= $qrCode['id'] ?? 0 ?>, 'inactive')" class="btn-inactive">Deactivate</button>
                            <button onclick="deleteQRCode(<?= $qrCode['id'] ?? 0 ?>)" class="btn-delete">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create Modal -->
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

<!-- QR Modal -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>QR Code</h3>
            <span class="close" onclick="hideQRModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="qr-modal-image-container">
                <img id="qrModalImage" class="qr-modal-image" alt="QR Code">
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

.qr-url {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<script>
// Global functions - defined immediately
function showCreateModal() {
    console.log('showCreateModal called');
    document.getElementById('createModal').style.display = 'block';
}

function hideCreateModal() {
    console.log('hideCreateModal called');
    document.getElementById('createModal').style.display = 'none';
    document.getElementById('createForm').reset();
}

function showQRModal(qrUrl) {
    console.log('showQRModal called with:', qrUrl);
    document.getElementById('qrModalImage').src = '../api/qr-png.php?data=' + encodeURIComponent(qrUrl) + '&size=300';
    document.getElementById('qrModal').style.display = 'block';
}

function hideQRModal() {
    console.log('hideQRModal called');
    document.getElementById('qrModal').style.display = 'none';
}

function downloadCurrentQR() {
    console.log('downloadCurrentQR called');
    const qrImage = document.getElementById('qrModalImage');
    const link = document.createElement('a');
    link.href = qrImage.src;
    link.download = 'qr-code.png';
    link.click();
}

function applyFilter() {
    console.log('applyFilter called');
    const status = document.getElementById('statusFilter').value;
    const station = document.getElementById('stationFilter').value;

    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (station) params.append('station', station);

    window.location.href = '?' + params.toString();
}

function editQRCode(id) {
    console.log('editQRCode called with:', id);
    alert('Edit functionality coming soon');
}

function updateQRStatus(id, status) {
    console.log('updateQRStatus called with:', id, status);
    alert('Status update functionality coming soon');
}

function deleteQRCode(id) {
    console.log('deleteQRCode called with:', id);
    if (confirm('Are you sure you want to delete this QR code?')) {
        alert('Delete functionality coming soon');
    }
}

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Form submission
    const createForm = document.getElementById('createForm');
    if (createForm) {
        console.log('createForm found');
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');

            const stationId = document.getElementById('stationSelect').value;
            const notes = document.getElementById('notesInput').value;
            const expiresAt = document.getElementById('expiresAtInput').value;

            if (!stationId) {
                alert('Please select a station');
                return;
            }

            // Simple form submission
            const formData = new FormData();
            formData.append('station_id', stationId);
            formData.append('notes', notes);
            formData.append('expires_at', expiresAt);

            fetch('../api/endroid-qr-generator.php?action=create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('QR Code created successfully!');
                    hideCreateModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating QR code');
            });
        });
    } else {
        console.log('createForm not found');
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
});
</script>

<?php renderAdminFooter(); ?>
