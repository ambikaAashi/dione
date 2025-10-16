<?php
require_once __DIR__ . '/includes/menu_functions.php';

$menuItems = load_menu_items();
$groupedMenu = group_menu_items_by_category($menuItems);

/**
 * @param string $value
 */
function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
    $value = preg_replace('/-+/', '-', $value);
    return trim((string) $value, '-');
}

/**
 * @param array<string, mixed> $item
 */
function display_price(array $item): string
{
    $unit = trim((string) ($item['price_unit'] ?? ''));
    $price = trim((string) ($item['price'] ?? ''));

    if ($unit !== '' && $price !== '') {
        return $unit . $price;
    }

    if ($price !== '') {
        return $price;
    }

    return '—';
}

/**
 * @param array<string, mixed> $item
 */
function price_amount(array $item): ?float
{
    if (isset($item['price_amount']) && is_numeric($item['price_amount'])) {
        return (float) $item['price_amount'];
    }

    $price = trim((string) ($item['price'] ?? ''));
    if ($price === '') {
        return null;
    }

    $numeric = preg_replace('/[^0-9.]/', '', $price);
    return $numeric === '' ? null : (float) $numeric;
}

/**
 * @param array<string, mixed> $item
 */
function diet_icon_class(array $item): string
{
    $type = strtolower((string) ($item['type'] ?? ''));

    return match ($type) {
        'veg' => 'veg-dot',
        'non-veg', 'non veg', 'nonveg' => 'nonveg-dot',
        'egg' => 'egg-dot',
        default => '',
    };
}

/**
 * @param array<string, mixed> $item
 */
function spicy_level(array $item): int
{
    if (!isset($item['spicy'])) {
        return 0;
    }

    $level = (int) $item['spicy'];
    return max(0, min(3, $level));
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dione — Platters &amp; Bar | Menu</title>
    <meta name="description" content="Dione — Platters &amp; Bar. Explore curated platters, starters, mains, desserts, and bar favourites." />
    <link rel="canonical" href="https://example.com/dione-menu" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0F0C0A;
            --paper:#1a1411;
            --accent:#D49E6E;
            --accentSoft:#FFE3C2;
            --text:#F7F3EE;
            --textMuted:#C7BAB0;
            --ink:#35150E;
            --chipBorder:#D49E6E33;
            --chipBorderActive:#D49E6E;
        }
        body { background:var(--bg); color:var(--text); }
        .font-head { font-family:'Playfair Display', ui-serif, Georgia, serif; }
        .font-body { font-family:'Josefin Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .chip { border:1px solid var(--chipBorder); background:var(--paper); color:var(--accentSoft); }
        .chip.active { border-color:var(--chipBorderActive); background:var(--ink); }
        .pill { background:var(--paper); border:1px solid #D49E6E22; }
        .card { background:var(--paper); border:1px solid #D49E6E22; }
        .veg-dot, .nonveg-dot, .egg-dot { display:inline-flex; width:12px; height:12px; border-radius:999px; }
        .veg-dot { background:#138A36; }
        .nonveg-dot { background:#A01414; }
        .egg-dot { background:#C67C00; }
    </style>
</head>
<body class="min-h-screen font-body">
<header class="sticky top-0 z-40 backdrop-blur supports-[backdrop-filter]:bg-black/40" style="border-bottom:1px solid #D49E6E22">
    <div class="mx-auto max-w-7xl px-4 py-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-xl" style="background:var(--accent)"></div>
                <div>
                    <h1 class="text-xl font-head tracking-wide" style="color:var(--accentSoft)">Dione — Platters &amp; Bar</h1>
                    <p class="text-xs text-neutral-400">Where Moments Breathe</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 rounded-xl px-3 py-2 pill">
                    <i data-lucide="search" class="w-4 h-4" aria-hidden="true"></i>
                    <input id="search" placeholder="Search dishes or tags" class="bg-transparent outline-none text-sm" aria-label="Search" />
                </div>
                <div class="hidden sm:flex items-center gap-2 rounded-xl px-3 py-2 pill">
                    <label class="sr-only" for="diet">Diet filter</label>
                    <select id="diet" class="bg-transparent outline-none text-sm" aria-label="Diet filter">
                        <option value="all">All</option>
                        <option value="veg">Veg</option>
                        <option value="non-veg">Non-Veg</option>
                        <option value="egg">Egg</option>
                    </select>
                    <span class="text-xs text-neutral-400">Max</span>
                    <label class="sr-only" for="maxPrice">Max price</label>
                    <input id="maxPrice" type="number" min="0" inputmode="numeric" placeholder="₹" class="bg-transparent outline-none text-sm w-20" />
                </div>
                <a href="admin.php" class="chip px-3 py-2 rounded-xl text-sm">Admin Panel</a>
            </div>
        </div>
        <nav class="mt-3 overflow-x-auto whitespace-nowrap pb-2" id="sectionNav" aria-label="Menu sections">
            <button type="button" data-section="all" class="chip inline-flex items-center gap-2 rounded-full px-3 py-1.5 mr-2 text-sm active">All</button>
            <?php foreach ($groupedMenu as $category => $items): ?>
                <?php $sectionId = slugify($category) ?: 'section-' . md5($category); ?>
                <button type="button" data-section="<?= htmlspecialchars($sectionId, ENT_QUOTES) ?>" class="chip inline-flex items-center gap-2 rounded-full px-3 py-1.5 mr-2 text-sm">
                    <?= htmlspecialchars($category, ENT_QUOTES) ?>
                </button>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-7xl px-4 pb-20 pt-6">
    <?php if ($menuItems === []): ?>
        <div class="rounded-2xl border border-dashed border-amber-500/40 bg-black/30 px-6 py-12 text-center">
            <h2 class="font-head text-2xl" style="color:var(--accentSoft)">Menu coming soon</h2>
            <p class="mt-2 text-sm text-neutral-300">No dishes have been imported yet. Upload a CSV from the admin panel to get started.</p>
        </div>
    <?php else: ?>
        <div id="menuSections" class="space-y-12">
            <?php foreach ($groupedMenu as $category => $items): ?>
                <?php $sectionId = slugify($category) ?: 'section-' . md5($category); ?>
                <section id="<?= htmlspecialchars($sectionId, ENT_QUOTES) ?>" class="scroll-mt-32" data-section-wrapper>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="font-head text-2xl" style="color:var(--accentSoft)"><?= htmlspecialchars($category, ENT_QUOTES) ?></h2>
                        <span class="text-xs uppercase tracking-wider text-neutral-400"><?= count($items) ?> item(s)</span>
                    </div>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-section-grid>
                        <?php foreach ($items as $item): ?>
                            <?php
                                $diet = strtolower((string) ($item['type'] ?? ''));
                                $tags = $item['tags'] ?? [];
                                $priceAmount = price_amount($item);
                                $addons = $item['addons'] ?? [];
                                $spiceLevel = spicy_level($item);
                            ?>
                            <article
                                class="card rounded-2xl p-5 transition duration-200 hover:-translate-y-1 hover:shadow-xl"
                                data-card
                                data-section="<?= htmlspecialchars($sectionId, ENT_QUOTES) ?>"
                                data-diet="<?= htmlspecialchars($diet ?: 'unknown', ENT_QUOTES) ?>"
                                data-price="<?= htmlspecialchars($priceAmount !== null ? (string) $priceAmount : '', ENT_QUOTES) ?>"
                                data-search="<?= htmlspecialchars(strtolower(($item['name'] ?? '') . ' ' . ($item['description'] ?? '') . ' ' . implode(' ', $tags)), ENT_QUOTES) ?>"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <?php $dietClass = diet_icon_class($item); ?>
                                            <?php if ($dietClass !== ''): ?>
                                                <span class="<?= $dietClass ?>" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <h3 class="text-lg font-head tracking-wide" style="color:var(--accentSoft)"><?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES) ?></h3>
                                            <?php if (in_array('signature', $tags, true)): ?>
                                                <i data-lucide="sparkles" class="w-4 h-4" aria-hidden="true"></i>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['description'])): ?>
                                            <p class="text-sm text-neutral-300 leading-relaxed"><?= nl2br(htmlspecialchars((string) $item['description'], ENT_QUOTES)) ?></p>
                                        <?php endif; ?>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-neutral-400">
                                            <?php if (!empty($item['cuisine'])): ?>
                                                <span><?= htmlspecialchars((string) $item['cuisine'], ENT_QUOTES) ?></span>
                                            <?php endif; ?>
                                            <?php if ($spiceLevel > 0): ?>
                                                <span class="inline-flex items-center gap-1" aria-label="Spice level <?= $spiceLevel ?> out of 3">
                                                    <?php for ($i = 0; $i < $spiceLevel; $i++): ?>
                                                        <i data-lucide="flame-kindling" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                                    <?php endfor; ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php foreach ($tags as $tag): ?>
                                                <span class="rounded-full border border-amber-200/20 px-2 py-0.5 text-[11px] uppercase tracking-wide text-amber-200/80">
                                                    <?= htmlspecialchars($tag, ENT_QUOTES) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-xl font-head" style="color:var(--accent)"><?= htmlspecialchars(display_price($item), ENT_QUOTES) ?></p>
                                    </div>
                                </div>
                                <?php if ($addons !== []): ?>
                                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-neutral-300">
                                        <?php foreach ($addons as $addon): ?>
                                            <span class="rounded-full border border-amber-200/15 px-2 py-1">
                                                <?= htmlspecialchars($addon['name'] ?? '', ENT_QUOTES) ?>
                                                <?php if (!empty($addon['price'])): ?>
                                                    · <?= htmlspecialchars((string) $addon['price'], ENT_QUOTES) ?>
                                                <?php endif; ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <footer class="mt-20 flex flex-col gap-3 border-t border-amber-100/10 pt-6 text-xs text-neutral-400 sm:flex-row sm:items-center sm:justify-between">
        <span class="flex items-center gap-2">
            <i data-lucide="leaf" class="w-3.5 h-3.5" aria-hidden="true"></i>
            Premium casual · Noon–Midnight
        </span>
        <span class="flex items-center gap-1">
            Powered by Dione
            <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
        </span>
    </footer>
</main>
<script>
(function() {
    const searchEl = document.getElementById('search');
    const dietEl = document.getElementById('diet');
    const maxPriceEl = document.getElementById('maxPrice');
    const sectionNav = document.getElementById('sectionNav');
    const cards = Array.from(document.querySelectorAll('[data-card]'));
    const sectionWrappers = Array.from(document.querySelectorAll('[data-section-wrapper]'));
    let activeSection = 'all';

    function normalisePrice(value) {
        if (!value) return null;
        const parsed = parseFloat(value);
        return Number.isNaN(parsed) ? null : parsed;
    }

    function applyFilters() {
        const query = (searchEl?.value || '').trim().toLowerCase();
        const diet = (dietEl?.value || 'all').toLowerCase();
        const maxPrice = normalisePrice(maxPriceEl?.value || '');

        cards.forEach(card => {
            const cardSection = card.getAttribute('data-section') || 'all';
            const cardDiet = (card.getAttribute('data-diet') || 'unknown').toLowerCase();
            const cardPrice = normalisePrice(card.getAttribute('data-price') || '');
            const cardSearch = card.getAttribute('data-search') || '';

            let visible = true;

            if (activeSection !== 'all' && cardSection !== activeSection) {
                visible = false;
            }

            if (visible && query !== '') {
                visible = cardSearch.includes(query);
            }

            if (visible && diet !== 'all') {
                visible = cardDiet === diet;
            }

            if (visible && maxPrice !== null && cardPrice !== null) {
                visible = cardPrice <= maxPrice;
            }

            card.classList.toggle('hidden', !visible);
        });

        sectionWrappers.forEach(section => {
            const hasVisible = Array.from(section.querySelectorAll('[data-card]')).some(card => !card.classList.contains('hidden'));
            section.classList.toggle('hidden', !hasVisible);
        });
    }

    sectionNav?.addEventListener('click', (event) => {
        const target = event.target instanceof HTMLElement ? event.target : null;
        const button = target?.closest('[data-section]');
        if (!button) {
            return;
        }
        activeSection = button.getAttribute('data-section') || 'all';
        sectionNav.querySelectorAll('[data-section]').forEach(el => {
            el.classList.toggle('active', el === button);
        });
        applyFilters();
    });

    searchEl?.addEventListener('input', applyFilters);
    dietEl?.addEventListener('change', applyFilters);
    maxPriceEl?.addEventListener('input', applyFilters);

    document.addEventListener('DOMContentLoaded', () => {
        applyFilters();
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    });
})();
</script>
</body>
</html>
