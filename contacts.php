<?php
/**
 * Emergency Contacts Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Force authentication
require_auth();

$user_id = $_SESSION['user_id'];
$errors = [];
$success_msg = '';

// Handle AJAX or POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD CONTACT
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $relation = trim($_POST['relation'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($relation) || empty($phone)) {
            $errors[] = "All fields are required.";
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO emergency_contacts (user_id, name, relation, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $relation, $phone]);
                $success_msg = "Emergency contact added successfully.";
            } catch (PDOException $e) {
                $errors[] = "Error adding contact: " . $e->getMessage();
            }
        }
    }
    
    // EDIT CONTACT
    elseif ($action === 'edit') {
        $contact_id = intval($_POST['contact_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $relation = trim($_POST['relation'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($relation) || empty($phone)) {
            $errors[] = "All fields are required.";
        }

        if (empty($errors)) {
            try {
                // Verify contact belongs to user
                $stmt = $pdo->prepare("UPDATE emergency_contacts SET name = ?, relation = ?, phone = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$name, $relation, $phone, $contact_id, $user_id]);
                $success_msg = "Emergency contact updated successfully.";
            } catch (PDOException $e) {
                $errors[] = "Error updating contact: " . $e->getMessage();
            }
        }
    }

    // DELETE CONTACT
    elseif ($action === 'delete') {
        $contact_id = intval($_POST['contact_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM emergency_contacts WHERE id = ? AND user_id = ?");
            $stmt->execute([$contact_id, $user_id]);
            $success_msg = "Emergency contact deleted successfully.";
        } catch (PDOException $e) {
            $errors[] = "Error deleting contact: " . $e->getMessage();
        }
    }
}

// Fetch all contacts for user
try {
    $stmt = $pdo->prepare("SELECT * FROM emergency_contacts WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    $contacts = [];
    $errors[] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Contacts - Silent Gesture Emergency System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f3f4f6;
            color: var(--text-dark);
        }

        .contacts-container {
            max-width: 650px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .contact-card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .contact-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
            border-color: rgba(11, 94, 215, 0.3);
        }

        .contact-info h5 {
            margin: 0 0 5px 0;
            font-weight: 600;
            font-size: 16px;
            color: var(--text-dark);
        }

        .contact-info .relation-badge {
            font-size: 11px;
            background-color: rgba(11, 94, 215, 0.1);
            color: var(--primary-blue);
            padding: 3px 8px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 6px;
        }

        .contact-info p {
            margin: 0;
            font-size: 14px;
            color: var(--text-muted);
        }

        .contact-actions {
            display: flex;
            gap: 10px;
        }

        .btn-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: none;
            transition: all 0.2s;
        }

        .btn-edit-contact {
            background-color: rgba(11, 94, 215, 0.1);
            color: var(--primary-blue);
        }

        .btn-edit-contact:hover {
            background-color: var(--primary-blue);
            color: #ffffff;
        }

        .btn-delete-contact {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--emergency-red);
        }

        .btn-delete-contact:hover {
            background-color: var(--emergency-red);
            color: #ffffff;
        }

        .empty-contacts-view {
            text-align: center;
            padding: 40px 20px;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px dashed var(--border-color);
            color: var(--text-muted);
        }

        .empty-contacts-view i {
            font-size: 48px;
            margin-bottom: 15px;
            color: rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <span class="fs-4 fw-bold text-uppercase">🛡️ Silent Gesture System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item ms-lg-3 text-white-50">Welcome, <strong><?php echo h($_SESSION['user_name']); ?></strong></li>
                    <li class="nav-item ms-3"><a class="btn btn-danger btn-sm py-1 px-3" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contacts Body -->
    <div class="contacts-container">
        
        <div class="row align-items-center mb-4">
            <div class="col-8">
                <h1 class="h3 fw-bold text-dark mb-1">Your Emergency Contacts</h1>
                <p class="text-muted small mb-0">These contacts will be notified during emergency events</p>
            </div>
            <div class="col-4 text-end">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo h($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo h($success_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- List of Contacts -->
        <div id="contacts-list">
            <?php if (empty($contacts)): ?>
                <div class="empty-contacts-view">
                    <i class="fa-solid fa-address-book"></i>
                    <h5 class="fw-semibold text-dark">No Contacts Added</h5>
                    <p class="mb-0">Add trusted contacts below to notify them in case of emergency.</p>
                </div>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                    <div class="contact-card">
                        <div class="contact-info">
                            <span class="relation-badge"><?php echo h($contact['relation']); ?></span>
                            <h5><?php echo h($contact['name']); ?></h5>
                            <p><i class="fa-solid fa-phone me-2"></i><?php echo h($contact['phone']); ?></p>
                        </div>
                        <div class="contact-actions">
                            <button class="btn btn-circle btn-edit-contact" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editContactModal"
                                    data-id="<?php echo $contact['id']; ?>"
                                    data-name="<?php echo h($contact['name']); ?>"
                                    data-relation="<?php echo h($contact['relation']); ?>"
                                    data-phone="<?php echo h($contact['phone']); ?>"
                                    title="Edit Contact">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <form action="contacts.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this emergency contact?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                <button type="submit" class="btn btn-circle btn-delete-contact" title="Delete Contact">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Add Contact Button -->
        <div class="d-grid gap-2 mt-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                <i class="fa-solid fa-plus me-2"></i> Add Contact
            </button>
        </div>

    </div>

    <!-- ADD CONTACT MODAL -->
    <div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f2b4c, #0a192f);">
                    <h5 class="modal-title fw-bold" id="addContactModalLabel">Add Emergency Contact</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="contacts.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="add-name" class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="add-name" name="name" placeholder="Enter contact name" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-relation" class="form-label">Relationship</label>
                            <select class="form-select form-control" id="add-relation" name="relation" required style="appearance: auto; padding: 12px 16px;">
                                <option value="" disabled selected>Select Relationship</option>
                                <option value="Mother">Mother</option>
                                <option value="Father">Father</option>
                                <option value="Spouse">Spouse/Partner</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Relative">Other Relative</option>
                                <option value="Friend">Friend</option>
                                <option value="Neighbor">Neighbor</option>
                                <option value="Guardian">Guardian</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="add-phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="add-phone" name="phone" placeholder="Enter phone number" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-secondary py-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary py-2 px-4">Add Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT CONTACT MODAL -->
    <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f2b4c, #0a192f);">
                    <h5 class="modal-title fw-bold" id="editContactModalLabel">Edit Emergency Contact</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="contacts.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="contact_id" id="edit-id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-relation" class="form-label">Relationship</label>
                            <select class="form-select form-control" id="edit-relation" name="relation" required style="appearance: auto; padding: 12px 16px;">
                                <option value="Mother">Mother</option>
                                <option value="Father">Father</option>
                                <option value="Spouse">Spouse/Partner</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Relative">Other Relative</option>
                                <option value="Friend">Friend</option>
                                <option value="Neighbor">Neighbor</option>
                                <option value="Guardian">Guardian</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="edit-phone" name="phone" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-secondary py-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary py-2 px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-text" style="color: rgba(0,0,0,0.5); padding-top: 30px;">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved. | <a href="admin_login.php" style="color: rgba(0,0,0,0.6); text-decoration: none;">Admin Portal</a></p>
    </div>

    <!-- Bootstrap JS & Script to fill Edit Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editModal = document.getElementById('editContactModal');
            editModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const relation = button.getAttribute('data-relation');
                const phone = button.getAttribute('data-phone');

                document.getElementById('edit-id').value = id;
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-relation').value = relation;
                document.getElementById('edit-phone').value = phone;
            });
        });
    </script>
</body>
</html>
