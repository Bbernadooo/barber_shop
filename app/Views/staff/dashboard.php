<?php
/**
 * Variables expected: $staff_name, $date, $appointments,
 *                     $blockedTimes, $todayStats, $message, $messageType
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Premium Barber Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Barber_shop/public/css/staff-style.css">
    <style>
        :root {
            --primary: #111111;
            --secondary: #161616;
            --accent: #ff2e2e;
            --card-bg: #161616;
            --border: #222222;
        }
        body { background-color: var(--primary); }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar__container">
            <a href="#" class="navbar__brand">ፏ_BARBER</a>
            <div class="navbar__user">
                <span class="navbar__user-name">
                    <i class="fa-regular fa-user" style="margin-right: 6px; color: var(--accent);"></i>
                    <?= htmlspecialchars($staff_name) ?>
                </span>
               <a href="?page=logout" class="navbar__logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <div class="page-header">
            <h1 class="page-header__title">Dashboard Overview</h1>
            <div class="page-header__date">
                <label for="dateFilter">View Date:</label>
                <input type="date" id="dateFilter" value="<?= $date ?>"
                       onchange="window.location.href='?page=staff&date='+this.value">
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert--<?= $messageType ?>">
                <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card stat-card--primary">
                <div class="stat-card__icon"><i class="fa-regular fa-calendar-check"></i></div>
                <div class="stat-card__label">Total Bookings</div>
                <div class="stat-card__value"><?= $todayStats['total'] ?></div>
            </div>
            <div class="stat-card stat-card--success">
                <div class="stat-card__icon"><i class="fa-regular fa-circle-dot"></i></div>
                <div class="stat-card__label">Active Confirmed</div>
                <div class="stat-card__value"><?= $todayStats['confirmed'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon"><i class="fa-solid fa-square-poll-horizontal"></i></div>
                <div class="stat-card__label">Completed Runs</div>
                <div class="stat-card__value"><?= $todayStats['completed'] ?></div>
            </div>
            <div class="stat-card stat-card--warning">
                <div class="stat-card__icon"><i class="fa-solid fa-ban"></i></div>
                <div class="stat-card__label">Blocked Slots</div>
                <div class="stat-card__value"><?= $todayStats['blocked'] ?></div>
            </div>
        </div>

        <div class="content-grid">

            <div class="card">
                <div class="card__header">
                    <h2 class="card__title">Scheduled Lineup</h2>
                </div>

                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <div class="empty-state__icon"><i class="fa-regular fa-folder-open"></i></div>
                        <p class="empty-state__text">No customer appointments booked for this date block.</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time Grid</th>
                                    <th>Client / Guest</th>
                                    <th>Service Requested</th>
                                    <th>Status Badge</th>
                                    <th style="text-align:right; padding-right:1.5rem;">Console Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td class="table__time"><?= date('g:i A', strtotime($app['start_time'])) ?></td>
                                    <td class="table__client"><?= htmlspecialchars($app['client']) ?></td>
                                    <td class="table__service"><?= htmlspecialchars($app['service']) ?></td>
                                    <td>
                                        <span class="badge badge--<?= $app['status'] ?>">
                                            <?= htmlspecialchars($app['status']) ?>
                                        </span>
                                    </td>
                                    <td class="table__actions" style="justify-content:flex-end; padding-right:1.5rem;">
                                        <?php if ($app['status'] === 'confirmed'): ?>
                                            <form method="POST" style="display:inline; margin-right:4px;">
                                                <input type="hidden" name="action" value="complete_appointment">
                                                <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                                <button type="submit" class="btn-small btn-small--complete">Complete</button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="cancel_appointment">
                                                <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                                <button type="submit" class="btn-small btn-small--cancel">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em;">Archived</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="card">
                    <div class="card__header">
                        <h2 class="card__title">Block Work Hours</h2>
                    </div>
                    <form method="POST" class="block-form">
                        <input type="hidden" name="action" value="block_time">
                        <div class="block-form__row">
                            <div class="block-form__group">
                                <label class="block-form__label" for="block_start">Start Time</label>
                                <input type="datetime-local" id="block_start" name="block_start" class="block-form__input" required>
                            </div>
                            <div class="block-form__group">
                                <label class="block-form__label" for="block_end">End Time</label>
                                <input type="datetime-local" id="block_end" name="block_end" class="block-form__input" required>
                            </div>
                        </div>
                        <button type="submit" class="btn">Lock Out Slot</button>
                    </form>

                    <?php if (!empty($blockedTimes)): ?>
                        <div class="unavailable-list">
                            <h3 style="margin:2rem 0 1rem; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted);">
                                Restricted Windows Today
                            </h3>
                            <?php foreach ($blockedTimes as $block): ?>
                                <div class="unavailable-item">
                                    <span class="unavailable-item__time">
                                        <?= date('g:i A', strtotime($block['start_time'])) ?> -
                                        <?= date('g:i A', strtotime($block['end_time'])) ?>
                                    </span>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="remove_block">
                                        <input type="hidden" name="block_id" value="<?= $block['id'] ?>">
                                        <button type="submit" class="unavailable-item__remove">Release</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card__header">
                        <h2 class="card__title">Console Guides</h2>
                    </div>
                    <ul style="list-style:none; padding:0; margin:0; font-size:0.9rem; color:var(--text-muted); display:flex; flex-direction:column; gap:0.75rem;">
                        <li style="display:flex; align-items:baseline; gap:8px;"><span style="color:var(--success);">■</span> Confirm and close active customer entries using row complete tags.</li>
                        <li style="display:flex; align-items:baseline; gap:8px;"><span style="color:var(--accent);">■</span> Cancellation flags will instantly clear reservation logs for clients.</li>
                        <li style="display:flex; align-items:baseline; gap:8px;"><span style="color:var(--border-focus);">■</span> Blocking intervals cleanly isolates timeline ranges on user-end forms.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <script>
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('block_start').min = now.toISOString().slice(0,16);
        document.getElementById('block_end').min = now.toISOString().slice(0,16);
    </script>
</body>
</html>