<?php
function addNotification($conn, $sender_id, $sender_role, $receiver_id, $receiver_role, $type, $content) {
    $stmt = $conn->prepare("
        INSERT INTO notifications 
        (sender_id, sender_role, receiver_id, receiver_role, type, content, is_read, created_at)
        VALUES (:sid, :srole, :rid, :rrole, :type, :content, 0, NOW())
    ");
    $stmt->execute([
        ':sid'     => $sender_id,
        ':srole'   => $sender_role,
        ':rid'     => $receiver_id,
        ':rrole'   => $receiver_role,
        ':type'    => $type,
        ':content' => $content
    ]);
}

