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
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="hero-anime p-5 rounded-4 shadow-lg position-relative overflow-hidden">
                <div class="anime-overlay"></div>
                <div class="position-relative z-2">
                    <h1 class="display-3 fw-bold mb-2 text-white anime-text-glow animate__animated animate__fadeIn">
                        <?php echo __('Chào Mừng Đến Với'); ?>
                    </h1>
                    <h1 class="display-2 fw-bold mb-4 text-white anime-text-glow animate__animated animate__fadeIn animate__delay-1s">
                        <span class="text-gradient">E-Club</span>
                    </h1>
                    <p class="lead mb-4 text-white-80 animate__animated animate__fadeIn animate__delay-2s">
                        <?php echo __('home_description'); ?>
                    </p>
                    <?php if (!isLoggedIn()): ?>
                        <hr class="my-4 bg-white opacity-25">
                        <p class="mb-4 text-white-80"><?php echo __('get_started_message'); ?></p>
                        <div class="d-grid gap-3 d-sm-flex justify-content-sm-center animate__animated animate__fadeIn animate__delay-3s">
                            <a class="btn btn-light btn-lg px-4 gap-3 anime-btn" href="index.php?page=register" role="button">
                                <i class="bi bi-person-plus me-2"></i><?php echo __('register'); ?>
                            </a>
                            <a class="btn btn-outline-light btn-lg px-4 anime-btn-outline" href="index.php?page=login" role="button">
                                <i class="bi bi-box-arrow-in-right me-2"></i><?php echo __('login'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="anime-decoration anime-circle-1"></div>
                <div class="anime-decoration anime-circle-2"></div>
                <div class="anime-decoration anime-square"></div>
            </div>
        </div>
    </div>

    <!-- Stats Cards with Anime Style -->
    <div class="row g-4 mb-5">
        <div class="col-md-4 animate__animated animate__fadeInUp">
            <div class="card h-100 text-center anime-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="anime-icon-wrapper mb-3">
                        <i class="bi bi-people-fill display-4"></i>
                    </div>
                    <h5 class="card-title mb-3"><?php echo __('total_clubs'); ?></h5>
                    <p class="card-text display-4 mb-3 fw-bold anime-number"><?php echo $total_clubs; ?></p>
                    <a href="index.php?page=clubs" class="btn btn-primary mt-auto anime-btn">
                        <i class="bi bi-people me-2"></i><?php echo __('view_clubs'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
            <div class="card h-100 text-center anime-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="anime-icon-wrapper mb-3 anime-icon-green">
                        <i class="bi bi-calendar-event-fill display-4"></i>
                    </div>
                    <h5 class="card-title mb-3"><?php echo __('upcoming_events'); ?></h5>
                    <p class="card-text display-4 mb-3 fw-bold anime-number-green"><?php echo $total_events; ?></p>
                    <a href="index.php?page=events" class="btn btn-success mt-auto anime-btn">
                        <i class="bi bi-calendar-event me-2"></i><?php echo __('view_events'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
            <div class="card h-100 text-center anime-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="anime-icon-wrapper mb-3 anime-icon-purple">
                        <i class="bi bi-link-45deg display-4"></i>
                    </div>
                    <h5 class="card-title mb-3"><?php echo __('quick_links'); ?></h5>
                    <div class="list-group anime-list">
                        <a href="index.php?page=clubs" class="list-group-item list-group-item-action anime-list-item">
                            <i class="bi bi-people me-2"></i><?php echo __('browse_clubs'); ?>
                            <i class="bi bi-chevron-right ms-auto"></i>
                        </a>
                        <a href="index.php?page=events" class="list-group-item list-group-item-action anime-list-item">
                            <i class="bi bi-calendar-event me-2"></i><?php echo __('view_events'); ?>
                            <i class="bi bi-chevron-right ms-auto"></i>
                        </a>
                        <?php if (isLoggedIn()): ?>
                            <a href="index.php?page=profile" class="list-group-item list-group-item-action anime-list-item">
                                <i class="bi bi-person me-2"></i><?php echo __('profile'); ?>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isLoggedIn() && !empty($user_clubs)): ?>
        <div class="anime-section-header mb-4 animate__animated animate__fadeIn animate__delay-3s">
            <h2 class="anime-title">
                <i class="bi bi-star-fill me-2 text-warning"></i>
                <?php echo __('your_clubs'); ?>
            </h2>
        </div>
        <div class="row g-4 mb-4 animate__animated animate__fadeIn animate__delay-3s">
            <?php foreach ($user_clubs as $club): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 anime-card">
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title mb-3"><?php echo htmlspecialchars($club['name']); ?></h5>
                            <p class="card-text mb-4"><?php echo htmlspecialchars(substr($club['description'], 0, 100)) . '...'; ?></p>
                            <a href="index.php?page=clubs&id=<?php echo $club['id']; ?>" class="btn btn-outline-primary mt-auto anime-btn-outline">
                                <span><?php echo __('view_details'); ?></span>
                                <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.hero-anime {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    padding: 3rem !important;
    position: relative;
    z-index: 1;
}

.anime-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1IiBoZWlnaHQ9IjUiPgo8cmVjdCB3aWR0aD0iNSIgaGVpZ2h0PSI1IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMSI+PC9yZWN0Pgo8L3N2Zz4=');
    opacity: 0.3;
    z-index: 0;
}

.z-2 {
    position: relative;
    z-index: 2;
}

.text-white-80 {
    color: rgba(255, 255, 255, 0.8);
}

.anime-text-glow {
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.text-gradient {
    background: linear-gradient(to right, #ff8a00, #da1b60);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    position: relative;
}

.anime-decoration {
    position: absolute;
    border-radius: 50%;
    z-index: 1;
}

.anime-circle-1 {
    width: 150px;
    height: 150px;
    background: rgba(255, 255, 255, 0.1);
    top: -30px;
    right: 10%;
    animation: float 6s ease-in-out infinite;
}

.anime-circle-2 {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.15);
    bottom: 20px;
    left: 10%;
    animation: float 8s ease-in-out infinite;
}

.anime-square {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.1);
    bottom: 40%;
    right: 20%;
    border-radius: 4px;
    transform: rotate(45deg);
    animation: float 7s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translatey(0) rotate(0deg); }
    50% { transform: translatey(-20px) rotate(10deg); }
    100% { transform: translatey(0) rotate(0deg); }
}

.anime-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    overflow: hidden;
}

.anime-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.anime-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(var(--bs-primary-rgb), 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: var(--bs-primary);
    transition: all 0.3s ease;
}

.anime-icon-green {
    background: rgba(var(--bs-success-rgb), 0.1);
    color: var(--bs-success);
}

.anime-icon-purple {
    background: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
}

.anime-card:hover .anime-icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}

.anime-number {
    color: var(--bs-primary);
    font-weight: 700;
}

.anime-number-green {
    color: var(--bs-success);
    font-weight: 700;
}

.anime-btn {
    border-radius: 50px;
    padding: 0.6rem 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    z-index: 1;
    transition: all 0.3s ease;
}

.anime-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 0%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    z-index: -1;
    transition: all 0.3s ease;
}

.anime-btn:hover::before {
    width: 100%;
}

.anime-btn-outline {
    border-radius: 50px;
    padding: 0.6rem 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    z-index: 1;
    transition: all 0.3s ease;
}

.anime-btn-outline:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.anime-list {
    border-radius: 12px;
    overflow: hidden;
}

.anime-list-item {
    border: none;
    padding: 0.8rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 8px !important;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.anime-list-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
    transform: translateX(5px);
}

.anime-section-header {
    position: relative;
    padding-bottom: 0.5rem;
    margin-bottom: 2rem;
}

.anime-title {
    font-weight: 700;
    position: relative;
    display: inline-block;
    padding-bottom: 0.5rem;
}

.anime-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50%;
    height: 3px;
    background: linear-gradient(to right, var(--bs-primary), transparent);
}
</style>
