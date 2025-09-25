<?php
require_once __DIR__ . '/includes/menu_functions.php';

$menuItems = load_menu_items();
$groupedMenu = group_menu_items_by_category($menuItems);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dione Menu</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Dione Menu</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="admin.php">Admin Panel</a>
        </div>
    </div>
</nav>
<div class="container pb-5">
    <header class="mb-5 text-center">
        <h1 class="display-5 fw-bold">Discover Our Menu</h1>
        <p class="text-muted">Freshly imported from the latest CSV provided by the admin team.</p>
    </header>

    <?php if ($menuItems === []): ?>
        <div class="alert alert-info" role="alert">
            Our menu is being prepared. Please check back soon!
        </div>
    <?php else: ?>
        <?php foreach ($groupedMenu as $category => $items): ?>
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h3 mb-0"><?= htmlspecialchars($category, ENT_QUOTES) ?></h2>
                    <span class="badge bg-secondary"><?= count($items) ?> item(s)</span>
                </div>
                <div class="row g-4">
                    <?php foreach ($items as $item): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 menu-card">
                                <div class="card-body d-flex flex-column">
                                    <h3 class="card-title h5"><?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES) ?></h3>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="card-text text-muted flex-grow-1"><?= nl2br(htmlspecialchars($item['description'], ENT_QUOTES)) ?></p>
                                    <?php else: ?>
                                        <p class="card-text text-muted flex-grow-1">&nbsp;</p>
                                    <?php endif; ?>
                                    <div class="mt-auto d-flex align-items-center justify-content-between">
                                        <span class="fw-bold text-primary fs-5">
                                            <?= htmlspecialchars($item['price'] ?? '', ENT_QUOTES) ?>
                                        </span>
                                        <span class="text-muted small">Category: <?= htmlspecialchars($item['category'] ?? 'Uncategorized', ENT_QUOTES) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
