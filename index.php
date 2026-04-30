<?php
// Public Domain + CC0 Lyrics Database - PHP Version

$PER_PAGE = 20;

// Load public domain database
$pdSongs = [];
if (file_exists('data.php')) {
    include 'data.php';
    $pdSongs = $SONGS ?? [];
} elseif (file_exists('lyrics_data.json')) {
    $json = file_get_contents('lyrics_data.json');
    $pdSongs = json_decode($json, true) ?: [];
}

// Load CC0 database
$cc0Songs = [];
if (file_exists('cc0_data.php')) {
    include 'cc0_data.php';
    $cc0Songs = $CC0_SONGS ?? [];
} elseif (file_exists('cc0_lyrics_data.json')) {
    $json = file_get_contents('cc0_lyrics_data.json');
    $cc0Songs = json_decode($json, true) ?: [];
}

$totalPD = count($pdSongs);
$totalCC0 = count($cc0Songs);
$totalSongs = $totalPD + $totalCC0;

// Get search parameters
$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$songId = isset($_GET['song']) ? intval($_GET['song']) : null;
$type = isset($_GET['type']) ? $_GET['type'] : 'pd';

// Determine which database to use
if ($type === 'cc0') {
    $currentSongs = $cc0Songs;
    $totalSongs = $totalCC0;
} else {
    $currentSongs = $pdSongs;
    $totalSongs = $totalPD;
}

// Get all categories
$categories = [];
foreach ($currentSongs as $song) {
    $cat = $song['category'] ?? 'Unknown';
    if (!isset($categories[$cat])) {
        $categories[$cat] = 0;
    }
    $categories[$cat]++;
}
ksort($categories);

// Filter songs
$filtered = $currentSongs;
if ($query !== '') {
    $filtered = array_filter($filtered, function($song) use ($query) {
        $title = strtolower($song['title'] ?? '');
        $author = strtolower($song['author'] ?? '');
        $lyrics = strtolower($song['lyrics'] ?? '');
        return strpos($title, $query) !== false
            || strpos($author, $query) !== false
            || strpos($lyrics, $query) !== false;
    });
}

if ($category !== '') {
    $filtered = array_filter($filtered, function($song) use ($category) {
        return ($song['category'] ?? '') === $category;
    });
}

$filtered = array_values($filtered);
$totalFiltered = count($filtered);
$totalPages = max(1, ceil($totalFiltered / $PER_PAGE));

if ($page > $totalPages) {
    $page = $totalPages;
}

$start = ($page - 1) * $PER_PAGE;
$pageResults = array_slice($filtered, $start, $PER_PAGE);

$singleSong = null;
if ($songId !== null && $songId >= 0 && $songId < count($filtered)) {
    $singleSong = $filtered[$songId];
}

function escapeHtml($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function truncateLyrics($lyrics, $length = 300) {
    if (strlen($lyrics) <= $length) {
        return $lyrics;
    }
    return substr($lyrics, 0, $length) . '...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Song Lyrics - Public Domain & CC0 Search</title>
    <meta name="description" content="Search free public domain and CC0 song lyrics. Folk songs, hymns, carols, spirituals, and more.">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            color: #eee;
            min-height: 100vh;
            line-height: 1.6;
        }
        .container { max-width: 1000px; margin: 0 auto; padding: 2rem; }
        header { text-align: center; padding: 3rem 0; }
        h1 {
            font-size: 3rem;
            background: linear-gradient(90deg, #00d9ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        .subtitle { color: #aaa; font-size: 1.2rem; margin-bottom: 2rem; }
        .search-container {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .search-box {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        input[type="text"] {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            background: rgba(0,0,0,0.4);
            color: #fff;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #00d9ff;
        }
        input[type="text"]::placeholder { color: #666; }
        button, .btn {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,217,255,0.4);
        }
        .filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        select {
            padding: 0.5rem 1rem;
            background: rgba(0,0,0,0.4);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            cursor: pointer;
        }
        .type-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        .type-tab {
            padding: 0.8rem 2rem;
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #888;
            text-decoration: none;
            transition: all 0.3s;
        }
        .type-tab:hover {
            border-color: #00d9ff;
            color: #00d9ff;
        }
        .type-tab.active {
            background: rgba(0,217,255,0.15);
            border-color: #00d9ff;
            color: #00d9ff;
        }
        .type-tab.cc0-active {
            background: rgba(0,255,136,0.15);
            border-color: #00ff88;
            color: #00ff88;
        }
        .type-tab small {
            display: block;
            font-size: 0.75rem;
            color: #666;
            margin-top: 0.2rem;
        }
        .type-tab.active small,
        .type-tab:hover small {
            color: inherit;
        }
        .stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .stat {
            background: rgba(255,255,255,0.05);
            padding: 1.5rem 2rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #00d9ff; }
        .stat-label { color: #888; font-size: 0.9rem; }
        .stat.cc0 .stat-number { color: #00ff88; }
        .results { display: flex; flex-direction: column; gap: 1.5rem; }
        .song-card {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
        }
        .song-card:hover {
            border-color: #00d9ff;
            background: rgba(255,255,255,0.08);
        }
        .song-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem; }
        .song-title { font-size: 1.3rem; color: #00d9ff; font-weight: bold; }
        .song-category {
            background: rgba(0,217,255,0.2);
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #00d9ff;
            white-space: nowrap;
        }
        .song-meta { color: #888; font-size: 0.9rem; margin-bottom: 1rem; }
        .song-lyrics {
            color: #ddd;
            white-space: pre-wrap;
            line-height: 1.8;
            background: rgba(0,0,0,0.2);
            padding: 1rem;
            border-radius: 8px;
            max-height: 400px;
            overflow-y: auto;
            font-size: 0.95rem;
        }
        .song-lyrics.full {
            max-height: none;
        }
        .no-results { text-align: center; padding: 3rem; color: #888; }
        .no-results h3 { color: #666; margin-bottom: 0.5rem; }
        .sources {
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .sources h3 { color: #888; margin-bottom: 1rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .sources a { color: #00d9ff; text-decoration: none; }
        .sources a:hover { text-decoration: underline; }
        .sources ul { list-style: none; color: #666; font-size: 0.9rem; }
        .sources li { margin-bottom: 0.5rem; }
        footer {
            text-align: center;
            padding: 3rem 0;
            color: #666;
            font-size: 0.9rem;
        }
        footer a { color: #00d9ff; text-decoration: none; }
        .disclaimer {
            background: rgba(255,200,0,0.1);
            border: 1px solid rgba(255,200,0,0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #ffcc00;
            font-size: 0.9rem;
        }
        .cc0-disclaimer {
            background: rgba(0,255,136,0.1);
            border: 1px solid rgba(0,255,136,0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #00ff88;
            font-size: 0.9rem;
        }
        .cc0-disclaimer a { color: #00ff88; }
        .results-status { color: #888; margin-bottom: 1rem; }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .page-btn, .page-btn-current {
            padding: 0.5rem 1rem;
            background: rgba(0,0,0,0.4);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }
        .page-btn:hover {
            border-color: #00d9ff;
        }
        .page-btn-current {
            background: rgba(0,217,255,0.2);
            border-color: #00d9ff;
            color: #00d9ff;
            cursor: default;
        }
        .back-link {
            color: #00d9ff;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            h1 { font-size: 2rem; }
            .search-box { flex-direction: column; }
            .stats { gap: 1rem; }
            .stat { padding: 1rem 1.5rem; }
            .stat-number { font-size: 1.8rem; }
            .type-tabs { flex-direction: column; }
        }
    </style>
</head>
<script src="https://quge5.com/88/tag.min.js" data-zone="234802" async data-cfasync="false"></script>
<body>
    <div class="container">
        <header>
            <h1>Free Song Lyrics</h1>
            <p class="subtitle">Public Domain + CC0 Original searchable database</p>
        </header>

        <div class="type-tabs">
            <a href="?type=pd" class="type-tab <?php echo $type === 'pd' ? 'active' : ''; ?>">
                Public Domain
                <small><?php echo number_format($totalPD); ?> songs - No restrictions</small>
            </a>
            <a href="?type=cc0" class="type-tab <?php echo $type === 'cc0' ? 'cc0-active' : ''; ?>">
                CC0 Original
                <small><?php echo number_format($totalCC0); ?> songs - Free to use</small>
            </a>
        </div>

        <?php if ($type === 'cc0'): ?>
            <div class="cc0-disclaimer">
                These lyrics are <strong>CC0 (Public Domain Dedication)</strong> - free for any use including commercial. No attribution required. <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank">Learn more</a>
            </div>
        <?php else: ?>
            <div class="disclaimer">
                All lyrics in this database are verified public domain. Sources include Wikisource and Project Gutenberg.
            </div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat <?php echo $type === 'cc0' ? 'cc0' : ''; ?>">
                <div class="stat-number"><?php echo number_format($totalFiltered); ?></div>
                <div class="stat-label"><?php echo $type === 'cc0' ? 'CC0 Songs' : 'Songs'; ?></div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo count($categories); ?></div>
                <div class="stat-label">Categories</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo $type === 'cc0' ? 'CC0' : '100%'; ?></div>
                <div class="stat-label"><?php echo $type === 'cc0' ? 'License' : 'Free'; ?></div>
            </div>
        </div>

        <?php if ($singleSong): ?>
            <a href="?type=<?php echo $type; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page; ?>" class="back-link">&larr; Back to results</a>

            <div class="song-card">
                <div class="song-header">
                    <div class="song-title"><?php echo escapeHtml($singleSong['title']); ?></div>
                    <span class="song-category"><?php echo escapeHtml($singleSong['category'] ?? 'Unknown'); ?></span>
                </div>
                <div class="song-meta">
                    <?php if ($singleSong['author']): ?>By <?php echo escapeHtml($singleSong['author']); ?><?php endif; ?>
                </div>
                <div class="song-lyrics full"><?php echo nl2br(escapeHtml($singleSong['lyrics'])); ?></div>
                <?php if ($type === 'cc0'): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: rgba(0,255,136,0.1); border-radius: 8px; font-size: 0.85rem;">
                        <strong style="color: #00ff88;">CC0 License</strong>
                        <p style="margin-top: 0.5rem; color: #aaa;">
                            This song is released under CC0 (Public Domain Dedication). You may use it for any purpose, including commercially.
                        </p>
                    </div>
                <?php elseif ($singleSong['source_url'] || $singleSong['author'] || $singleSong['year']): ?>
                    <div style="margin-top: 1rem; font-size: 0.85rem; color: #888; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                        <?php if ($singleSong['author']): ?>
                            <div>Artist: <?php echo escapeHtml($singleSong['author']); ?></div>
                        <?php endif; ?>
                        <?php if ($singleSong['year']): ?>
                            <div>Year: <?php echo escapeHtml($singleSong['year']); ?></div>
                        <?php endif; ?>
                        <?php if ($singleSong['source_url']): ?>
                            <div>Source: <a href="<?php echo escapeHtml($singleSong['source_url']); ?>" target="_blank" style="color: #00d9ff;"><?php echo escapeHtml($singleSong['source_url']); ?></a></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="search-container">
                <form method="GET" action="">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <div class="search-box">
                        <input type="text" name="q" placeholder="Search by title, author, or lyrics..." value="<?php echo escapeHtml($query); ?>">
                        <button type="submit">Search</button>
                    </div>
                    <div class="filters">
                        <select name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat => $count): ?>
                                <option value="<?php echo escapeHtml($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo escapeHtml($cat); ?> (<?php echo number_format($count); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($query || $category): ?>
                            <a href="?type=<?php echo $type; ?>" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="results-status">
                <?php if ($query || $category): ?>
                    <?php echo number_format($totalFiltered); ?> songs found
                    <?php if ($totalPages > 1): ?>
                        (page <?php echo $page; ?> of <?php echo $totalPages; ?>)
                    <?php endif; ?>
                <?php else: ?>
                    Browse all <?php echo number_format($totalFiltered); ?> songs
                <?php endif; ?>
            </div>

            <div class="results">
                <?php if (empty($pageResults)): ?>
                    <?php if ($query || $category): ?>
                        <div class="no-results">
                            <h3>No songs found</h3>
                            <p>Try a different search term or category</p>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <h3>No songs available</h3>
                            <p>Check back later or switch to a different type</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php foreach ($pageResults as $index => $song): ?>
                        <?php $globalIndex = $start + $index; ?>
                        <div class="song-card">
                            <div class="song-header">
                                <div class="song-title">
                                    <a href="?type=<?php echo $type; ?>&song=<?php echo $globalIndex; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page; ?>" style="color: #00d9ff; text-decoration: none;">
                                        <?php echo escapeHtml($song['title']); ?>
                                    </a>
                                </div>
                                <span class="song-category"><?php echo escapeHtml($song['category'] ?? 'Unknown'); ?></span>
                            </div>
                            <div class="song-meta">
                                <?php if ($song['author']): ?>By <?php echo escapeHtml($song['author']); ?><?php endif; ?>
                            </div>
                            <div class="song-lyrics">
                                <?php echo nl2br(escapeHtml(truncateLyrics($song['lyrics'] ?? '', 500))); ?>
                                <?php if (strlen($song['lyrics'] ?? '') > 500): ?>
                                    <div style="margin-top: 0.5rem;">
                                        <a href="?type=<?php echo $type; ?>&song=<?php echo $globalIndex; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page; ?>" style="color: #00d9ff; font-size: 0.9rem;">Show full lyrics &rarr;</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?type=<?php echo $type; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page - 1; ?>" class="page-btn">&larr; Prev</a>
                    <?php else: ?>
                        <span class="page-btn" style="opacity: 0.3; cursor: not-allowed;">&larr; Prev</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <?php if ($i == $page): ?>
                                <span class="page-btn-current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?type=<?php echo $type; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $i; ?>" class="page-btn"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <span style="color: #666;">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?type=<?php echo $type; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page + 1; ?>" class="page-btn">Next &rarr;</a>
                    <?php else: ?>
                        <span class="page-btn" style="opacity: 0.3; cursor: not-allowed;">Next &rarr;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="sources">
            <h3>Data Sources</h3>
            <ul>
                <li><strong>Public Domain:</strong> Wikisource (en.wikisource.org) - Free library of public domain texts</li>
                <li><strong>Public Domain:</strong> Project Gutenberg (gutenberg.org) - 70,000+ free e-books</li>
                <li><strong>CC0 Original:</strong> Original creations by opencode AI - Released under CC0 dedication</li>
            </ul>
        </div>

        <footer>
            <p><?php echo number_format($totalPD); ?> Public Domain songs + <?php echo number_format($totalCC0); ?> CC0 Original songs</p>
            <p>PD songs: no restrictions | CC0 songs: free for any use including commercially</p>
        </footer>
    </div>
</body>
</html>