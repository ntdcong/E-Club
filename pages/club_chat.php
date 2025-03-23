<?php
// Thêm vào đầu file club_chat.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'chat_error.log');

try {
    $club_id = sanitize($_GET['club_id']);

    // Kiểm tra kết nối database
    if (!$conn) {
        error_log("Database connection failed");
        throw new Exception("Không thể kết nối database");
    }

    // Cập nhật thời gian hoạt động của user
    $sql = "UPDATE users SET last_activity = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Lỗi chuẩn bị câu truy vấn");
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Lỗi cập nhật trạng thái");
    }

    // Kiểm tra quyền truy cập
    $sql = "SELECT cm.status, c.name as club_name 
            FROM club_members cm 
            JOIN clubs c ON cm.club_id = c.id 
            WHERE cm.club_id = ? AND cm.user_id = ? AND cm.status = 'approved'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Lỗi chuẩn bị câu truy vấn");
    }
    $stmt->bind_param("ii", $club_id, $_SESSION['user_id']);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Lỗi kiểm tra quyền truy cập");
    }
    $member = $stmt->get_result()->fetch_assoc();

    if (!$member) {
        throw new Exception('Bạn không phải là thành viên của CLB này');
    }

    // Load tin nhắn cũ
    $sql = "SELECT m.*, u.name as sender_name 
            FROM club_messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.club_id = ? 
            ORDER BY m.created_at DESC 
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Lỗi chuẩn bị câu truy vấn");
    }
    $stmt->bind_param("i", $club_id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Lỗi tải tin nhắn");
    }
    $old_messages = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));

} catch (Exception $e) {
    error_log("Chat error: " . $e->getMessage());
    flashMessage($e->getMessage(), 'danger');
    redirect('/index.php?page=clubs');
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-comments me-2"></i>
                Chat - <?php echo htmlspecialchars($member['club_name']); ?>
            </h5>
            <div>
                <span class="badge bg-light text-dark" id="onlineCount">0 online</span>
                <a href="index.php?page=club_detail&id=<?php echo $club_id; ?>" class="btn btn-light btn-sm ms-2">
                    <i class="fas fa-arrow-left"></i> Quay lại CLB
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Danh sách thành viên online -->
                <div class="col-md-3 border-end">
                    <div class="p-3">
                        <h6 class="mb-3">Thành viên online</h6>
                        <div id="onlineMembers" class="list-group list-group-flush">
                            <!-- Danh sách được cập nhật bằng AJAX -->
                        </div>
                    </div>
                </div>

                <!-- Khu vực chat -->
                <div class="col-md-9">
                    <div class="d-flex flex-column" style="height: 600px;">
                        <!-- Khu vực tin nhắn -->
                        <div class="flex-grow-1 p-3 overflow-auto" id="messageArea">
                            <?php foreach ($old_messages as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-end' : ''; ?> mb-3">
                                <div class="d-inline-block">
                                    <small class="text-muted"><?php echo htmlspecialchars($msg['sender_name']); ?></small>
                                    <div class="p-2 rounded <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-primary text-white' : 'bg-light'; ?>">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                    </div>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($msg['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Form nhập tin nhắn -->
                        <div class="border-top p-3">
                            <form id="messageForm" class="d-flex gap-2">
                                <input type="text" class="form-control" id="messageInput" 
                                       placeholder="Nhập tin nhắn..." autocomplete="off" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageArea = document.getElementById('messageArea');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const clubId = <?php echo $club_id; ?>;
    let lastMessageId = <?php echo empty($old_messages) ? 0 : $old_messages[count($old_messages)-1]['id']; ?>;

    // Debug info
    console.log('Club ID:', clubId);
    console.log('Last Message ID:', lastMessageId);

    // Cuộn xuống tin nhắn mới nhất
    messageArea.scrollTop = messageArea.scrollHeight;

    // Cập nhật tin nhắn mới mỗi 2 giây
    setInterval(loadMessages, 2000);

    // Cập nhật danh sách thành viên online mỗi 10 giây
    loadOnlineMembers();
    setInterval(loadOnlineMembers, 10000);

    // Xử lý gửi tin nhắn
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (message) {
            console.log('Sending message:', message); // Debug
            sendMessage(message);
            messageInput.value = '';
        }
    });

    function loadMessages() {
        const url = `ajax/get_messages.php?club_id=${clubId}&last_id=${lastMessageId}`;
        console.log('Loading messages from:', url); // Debug
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('Received data:', data); // Debug
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        appendMessage(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    messageArea.scrollTop = messageArea.scrollHeight;
                }
            })
            .catch(error => console.error('Error loading messages:', error));
    }

    function appendMessage(msg) {
        console.log('Appending message:', msg); // Debug
        const isOwnMessage = msg.sender_id == <?php echo $_SESSION['user_id']; ?>;
        const messageHtml = `
            <div class="message ${isOwnMessage ? 'text-end' : ''} mb-3">
                <div class="d-inline-block">
                    <small class="text-muted">${msg.sender_name}</small>
                    <div class="p-2 rounded ${isOwnMessage ? 'bg-primary text-white' : 'bg-light'}">
                        ${msg.message}
                    </div>
                    <small class="text-muted">${msg.created_at}</small>
                </div>
            </div>
        `;
        messageArea.insertAdjacentHTML('beforeend', messageHtml);
    }

    function sendMessage(message) {
        console.log('Sending message to server:', message); // Debug
        fetch('ajax/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                club_id: clubId,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data); // Debug
            if (data.success) {
                loadMessages();
            } else {
                alert(data.message || 'Không thể gửi tin nhắn');
            }
        })
        .catch(error => console.error('Error sending message:', error));
    }

    function loadOnlineMembers() {
        const url = `ajax/get_online_members.php?club_id=${clubId}`;
        console.log('Loading online members from:', url); // Debug
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('Online members data:', data); // Debug
                if (data.success) {
                    document.getElementById('onlineCount').textContent = `${data.count} online`;
                    const membersList = document.getElementById('onlineMembers');
                    membersList.innerHTML = data.members.map(member => `
                        <div class="list-group-item d-flex align-items-center">
                            <span class="badge bg-success me-2"></span>
                            ${member.name}
                        </div>
                    `).join('');
                }
            })
            .catch(error => console.error('Error loading online members:', error));
    }
});
</script>

<style>
.message {
    max-width: 80%;
    margin: 0.5rem 0;
}
.message .d-inline-block {
    max-width: 100%;
}
.message .bg-light {
    background-color: #f8f9fa;
}
.badge.bg-success {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
#messageArea {
    scroll-behavior: smooth;
}
.message:not(.text-end) {
    margin-right: auto;
}
.message.text-end {
    margin-left: auto;
}
</style> 