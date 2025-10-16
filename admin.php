<?php
session_start();

require_once __DIR__ . '/includes/menu_functions.php';

$menuItems = load_menu_items();
$uploadError = null;
$uploadSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset_menu']) && $_POST['reset_menu'] === '1') {
        $menuItems = [];
        save_menu_items($menuItems);
        $uploadSuccess = 'Menu cleared successfully.';
    } elseif (!isset($_FILES['menu_csv']) || !is_uploaded_file($_FILES['menu_csv']['tmp_name'])) {
        $uploadError = 'Please choose a CSV file before uploading.';
    } else {
        $file = $_FILES['menu_csv'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadError = 'An error occurred while uploading the file.';
        } else {
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle === false) {
                $uploadError = 'Unable to read the uploaded file.';
            } else {
                $headers = fgetcsv($handle);

                if ($headers === false) {
                    $uploadError = 'The CSV file appears to be empty.';
                } else {
                    $normalisedHeaders = array_map(
                        static fn($header) => strtolower(trim((string) $header)),
                        $headers
                    );

                    $required = ['name', 'price'];
                    $missing = array_diff($required, $normalisedHeaders);

                    if ($missing !== []) {
                        $uploadError = 'Missing required columns: ' . implode(', ', $missing);
                    } else {
                        $parsedItems = [];
                        while (($row = fgetcsv($handle)) !== false) {
                            if ($row === [null] || $row === false) {
                                continue;
                            }

                            $item = [
                                'name' => '',
                                'description' => '',
                                'price' => '',
                                'price_unit' => '',
                                'price_amount' => null,
                                'category' => 'Uncategorized',
                                'type' => '',
                                'spicy' => null,
                                'cuisine' => '',
                                'tags' => [],
                                'addons' => [],
                            ];

                            foreach ($normalisedHeaders as $index => $column) {
                                if (!array_key_exists($index, $row)) {
                                    continue;
                                }

                                $value = trim((string) $row[$index]);

                                switch ($column) {
                                    case 'name':
                                        $item['name'] = $value;
                                        break;
                                    case 'description':
                                        $item['description'] = $value;
                                        break;
                                    case 'price':
                                        $item['price'] = $value;
                                        $numeric = preg_replace('/[^0-9.]/', '', $value);
                                        $item['price_amount'] = $numeric === '' ? null : (float) $numeric;
                                        break;
                                    case 'unit':
                                    case 'price_unit':
                                        $item['price_unit'] = $value;
                                        break;
                                    case 'type':
                                        $allowedTypes = ['veg', 'non-veg', 'non veg', 'nonveg', 'egg'];
                                        if ($value !== '') {
                                            $normalised = strtolower($value);
                                            if (in_array($normalised, $allowedTypes, true)) {
                                                if ($normalised === 'veg') {
                                                    $item['type'] = 'Veg';
                                                } elseif ($normalised === 'egg') {
                                                    $item['type'] = 'Egg';
                                                } else {
                                                    $item['type'] = 'Non-Veg';
                                                }
                                            }
                                        }
                                        break;
                                    case 'category':
                                        $item['category'] = $value === '' ? 'Uncategorized' : $value;
                                        break;
                                    case 'spicy':
                                        if ($value !== '' && is_numeric($value)) {
                                            $level = (int) $value;
                                            $item['spicy'] = max(0, min(3, $level));
                                        }
                                        break;
                                    case 'cuisine':
                                        $item['cuisine'] = $value;
                                        break;
                                    case 'tags':
                                        if ($value !== '') {
                                            $rawTags = preg_split('/[;,]/', $value) ?: [];
                                            $item['tags'] = array_values(array_filter(array_map(
                                                static fn(string $tag) => trim($tag),
                                                $rawTags
                                            )));
                                        }
                                        break;
                                    case 'addons':
                                        if ($value !== '') {
                                            $addons = [];
                                            foreach (explode(';', $value) as $addonRow) {
                                                $addonRow = trim($addonRow);
                                                if ($addonRow === '') {
                                                    continue;
                                                }

                                                [$addonName, $addonPrice] = array_pad(
                                                    array_map('trim', explode('|', $addonRow, 2)),
                                                    2,
                                                    ''
                                                );

                                                if ($addonName === '') {
                                                    continue;
                                                }

                                                $addonAmount = preg_replace('/[^0-9.]/', '', $addonPrice);

                                                $addons[] = [
                                                    'name' => $addonName,
                                                    'price' => $addonPrice,
                                                    'price_amount' => $addonAmount === '' ? null : (float) $addonAmount,
                                                ];
                                            }

                                            if ($addons !== []) {
                                                $item['addons'] = $addons;
                                            }
                                        }
                                        break;
                                }
                            }

                            if ($item['name'] === '' && $item['price'] === '') {
                                continue;
                            }

                            $parsedItems[] = $item;
                        }

                        fclose($handle);

                        if ($parsedItems === []) {
                            $uploadError = 'No menu items were found in the CSV file.';
                        } else {
                            $menuItems = $parsedItems;
                            save_menu_items($menuItems);
                            $uploadSuccess = sprintf('Imported %d menu item(s) successfully.', count($menuItems));
                        }
                    }
                }
            }
        }
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dione Menu Admin</title>
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
        <a class="navbar-brand" href="admin.php">Dione Admin</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">View Menu</a>
        </div>
    </div>
</nav>
<div class="container">
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title h4 mb-3">Import menu from CSV</h2>
                    <?php if ($uploadError !== null): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($uploadError, ENT_QUOTES) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($uploadSuccess !== null): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($uploadSuccess, ENT_QUOTES) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="menu_csv" class="form-label">Menu CSV file</label>
                            <input class="form-control" type="file" id="menu_csv" name="menu_csv" accept=".csv" required>
                            <div class="form-text">
                                Required columns: <code>name</code> and <code>price</code>. Optional: <code>description</code>, <code>category</code>.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title h4 mb-3">Reset menu</h2>
                    <p class="card-text">Remove all currently stored menu items.</p>
                    <form method="post">
                        <input type="hidden" name="reset_menu" value="1">
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to clear the menu?');">
                            Clear menu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title h4 mb-3">Current menu preview</h2>
            <?php if ($menuItems === []): ?>
                <p class="text-muted">No menu items have been imported yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Category</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['category'] ?? 'Uncategorized', ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($item['price'] ?? '', ENT_QUOTES) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
