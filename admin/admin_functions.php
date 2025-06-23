<?php
// Admin-specific functions
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

function logAdminAction($admin_id, $action, $details = '') {
    global $conn;
    
    if (!$conn) {
        require_once '../includes/database.php';
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("iss", $admin_id, $action, $details);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

function getTotalGames() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM games");
        return $stmt->fetch_assoc()['count'];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return 0;
    }
}

function getTotalOrders() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
        return $stmt->fetch_assoc()['count'];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return 0;
    }
}

function getTotalUsers() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        return $stmt->fetch_assoc()['count'];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return 0;
    }
}

function getRecentOrders($limit = 5) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT o.*, u.name as user_name, p.status as payment_status 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON o.id = p.order_id
            ORDER BY o.order_date DESC 
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function getSalesAnalytics($start_date = null, $end_date = null, $game_id = null) {
    global $conn;
    
    try {
        $where = "WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($start_date) {
            $where .= " AND o.order_date >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $where .= " AND o.order_date <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        if ($game_id) {
            $where .= " AND oi.game_id = ?";
            $params[] = $game_id;
            $types .= "i";
        }
        
        $query = "
            SELECT 
                g.id,
                g.title,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            JOIN games g ON oi.game_id = g.id
            JOIN orders o ON oi.order_id = o.id
            $where
            GROUP BY g.id, g.title
            ORDER BY total_revenue DESC
        ";
        
        $stmt = $conn->prepare($query);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function getTopSellingGames($limit = 5, $start_date = null, $end_date = null) {
    global $conn;
    
    try {
        $where = "WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($start_date) {
            $where .= " AND o.order_date >= ?";
            $params[] = $start_date;
            $types .= "s";
        }
        
        if ($end_date) {
            $where .= " AND o.order_date <= ?";
            $params[] = $end_date;
            $types .= "s";
        }
        
        $query = "
            SELECT 
                g.id,
                g.title,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            JOIN games g ON oi.game_id = g.id
            JOIN orders o ON oi.order_id = o.id
            $where
            GROUP BY g.id, g.title
            ORDER BY total_revenue DESC
            LIMIT ?
        ";
        
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function getRevenueByMonth($months = 6) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT 
                DATE_FORMAT(o.order_date, '%Y-%m') as month,
                SUM(o.total_amount) as revenue
            FROM orders o
            WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY month
            ORDER BY month ASC
        ");
        $stmt->bind_param("i", $months);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function getGamesWithPagination($page = 1, $per_page = 10, $search = '') {
    global $conn;
    
    try {
        $offset = ($page - 1) * $per_page;
        $params = [];
        $types = "";
        $where = "";
        
        if (!empty($search)) {
            $where = " WHERE title LIKE ? OR description LIKE ?";
            $search_term = "%$search%";
            $params = [$search_term, $search_term];
            $types = "ss";
        }
        
        $count_query = "SELECT COUNT(*) as total FROM games $where";
        $stmt = $conn->prepare($count_query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'];
        $pages = ceil($total / $per_page);
        
        $query = "SELECT * FROM games $where ORDER BY title LIMIT ? OFFSET ?";
        $params[] = $per_page;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return [
            'data' => $data,
            'total' => $total,
            'pages' => $pages,
            'page' => $page
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return ['data' => [], 'total' => 0, 'pages' => 0, 'page' => 1];
    }
}

function getOrdersWithPagination($page = 1, $per_page = 10, $search = '', $status = '') {
    global $conn;
    
    try {
        $offset = ($page - 1) * $per_page;
        $params = [];
        $types = "";
        $where = "WHERE 1=1";
        
        if (!empty($search)) {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
            $search_term = "%$search%";
            $params = [$search_term, $search_term, $search_term];
            $types = "sss";
        }
        
        if (!empty($status)) {
            $where .= " AND p.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $count_query = "
            SELECT COUNT(*) as total 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON o.id = p.order_id
            $where
        ";
        
        $stmt = $conn->prepare($count_query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'];
        $pages = ceil($total / $per_page);
        
        $query = "
            SELECT 
                o.*, 
                u.name as user_name,
                p.status as payment_status
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON o.id = p.order_id
            $where
            ORDER BY o.order_date DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $per_page;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return [
            'data' => $data,
            'total' => $total,
            'pages' => $pages,
            'page' => $page
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return ['data' => [], 'total' => 0, 'pages' => 0, 'page' => 1];
    }
}

function getReviewComplaints() {
    global $conn;
    try {
        $query = "
            SELECT 
                rc.*,
                r.comment,
                u.name as complainant,
                g.title as game_title
            FROM review_complaints rc
            JOIN reviews r ON rc.review_id = r.id
            JOIN users u ON rc.user_id = u.id
            JOIN games g ON r.game_id = g.id
            ORDER BY rc.created_at DESC
        ";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function updateReviewComplaintStatus($complaint_id, $status) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE review_complaints SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $complaint_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

function deleteReview($review_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

function deleteGame($game_id) {
    global $conn;
    try {
        $conn->begin_transaction();
        
        // Delete related records first
        $stmt = $conn->prepare("DELETE FROM order_items WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM reviews WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        
        // Delete the game
        $stmt = $conn->prepare("DELETE FROM games WHERE id = ?");
        $stmt->bind_param("i", $game_id);
        $result = $stmt->execute();
        
        $conn->commit();
        return $result;
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        return false;
    }
}

function deleteUser($user_id) {
    global $conn;
    try {
        $conn->begin_transaction();
        
        // Delete related records first
        $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM review_complaints WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete payments for orders associated with the user
        $stmt = $conn->prepare("DELETE p FROM payments p JOIN orders o ON p.order_id = o.id WHERE o.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        
        $conn->commit();
        return $result;
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        return false;
    }
}

function updateOrderStatus($order_id, $status) {
    global $conn;
    try {
        // Validate status
        if (!in_array($status, ['pending', 'completed', 'failed'])) {
            throw new Exception("Invalid status: $status");
        }
        
        // Check if payment record exists
        $stmt = $conn->prepare("SELECT id FROM payments WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing payment record
            $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $status, $order_id);
        } else {
            // Insert new payment record
            $stmt = $conn->prepare("INSERT INTO payments (order_id, status) VALUES (?, ?)");
            $stmt->bind_param("is", $order_id, $status);
        }
        
        $result = $stmt->execute();
        return $result;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}
?>