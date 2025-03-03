<?php
$total_clubs = 0;
$total_events = 0;
$user_clubs = [];

// Get total number of clubs
$sql = "SELECT COUNT(*) as total FROM clubs";
$result = $conn->query($sql);
if ($result) {
    $total_clubs = $result->fetch_assoc()['total'];
}

// Get total number of upcoming events
$sql = "SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()";
$result = $conn->query($sql);
if ($result) {
    $total_events = $result->fetch_assoc()['total'];
}

// If user is logged in, get their clubs
if (isLoggedIn()) {
    $sql = "SELECT c.* FROM clubs c 
           INNER JOIN club_members cm ON c.id = cm.club_id 
           WHERE cm.user_id = ? AND cm.status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($club = $result->fetch_assoc()) {
        $user_clubs[] = $club;
    }
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto text-center">
            <div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
                <h1 class="display-4 fw-bold"><?php echo __('welcome_to', ['name' => APP_NAME]); ?></h1>
                <p class="lead mb-4"><?php echo __('home_description'); ?></p>
                <?php if (!isLoggedIn()): ?>
                    <hr class="my-4">
                    <p class="mb-4"><?php echo __('get_started_message'); ?></p>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <a class="btn btn-primary btn-lg px-4 gap-3" href="index.php?page=register" role="button">
                            <i class="bi bi-person-plus me-2"></i><?php echo __('register'); ?>
                        </a>
                        <a class="btn btn-outline-primary btn-lg px-4" href="index.php?page=login" role="button">
                            <i class="bi bi-box-arrow-in-right me-2"></i><?php echo __('login'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-4"><?php echo __('total_clubs'); ?></h5>
                    <p class="card-text display-4 mb-4"><?php echo $total_clubs; ?></p>
                    <a href="index.php?page=clubs" class="btn btn-primary mt-auto">
                        <i class="bi bi-people me-2"></i><?php echo __('view_clubs'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-4"><?php echo __('upcoming_events'); ?></h5>
                    <p class="card-text display-4 mb-4"><?php echo $total_events; ?></p>
                    <a href="index.php?page=events" class="btn btn-primary mt-auto">
                        <i class="bi bi-calendar-event me-2"></i><?php echo __('view_events'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title mb-4"><?php echo __('quick_links'); ?></h5>
                    <div class="list-group">
                        <a href="index.php?page=clubs" class="list-group-item list-group-item-action">
                            <i class="bi bi-people me-2"></i><?php echo __('browse_clubs'); ?>
                        </a>
                        <a href="index.php?page=events" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-event me-2"></i><?php echo __('view_events'); ?>
                        </a>
                        <?php if (isLoggedIn()): ?>
                            <a href="index.php?page=profile" class="list-group-item list-group-item-action">
                                <i class="bi bi-person me-2"></i><?php echo __('profile'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isLoggedIn() && !empty($user_clubs)): ?>
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4"><?php echo __('your_clubs'); ?></h2>
                <div class="row g-4">
                    <?php foreach ($user_clubs as $club): ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title mb-3"><?php echo htmlspecialchars($club['name']); ?></h5>
                                    <p class="card-text mb-4"><?php echo htmlspecialchars(substr($club['description'], 0, 100)) . '...'; ?></p>
                                    <a href="index.php?page=clubs&id=<?php echo $club['id']; ?>" class="btn btn-primary mt-auto">
                                        <i class="bi bi-arrow-right me-2"></i><?php echo __('view_details'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>