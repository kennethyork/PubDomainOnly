<?php
// Public Domain Lyrics Database - PHP Version
// No JavaScript required, fully server-side rendering

$PER_PAGE = 20;

// Load database from compiled PHP array (faster than json_decode)
$songs = [];
if (file_exists('data.php')) {
    include 'data.php';
    $songs = $SONGS ?? [];
} elseif (file_exists('lyrics_data.json')) {
    $json = file_get_contents('lyrics_data.json');
    $songs = json_decode($json, true) ?: [];
}

$totalSongs = count($songs);

// Get search parameters
$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$songId = isset($_GET['song']) ? intval($_GET['song']) : null;

// Get all categories
$categories = [];
foreach ($songs as $song) {
    $cat = $song['category'] ?? 'Unknown';
    if (!isset($categories[$cat])) {
        $categories[$cat] = 0;
    }
    $categories[$cat]++;
}
ksort($categories);

// Filter songs
$filtered = $songs;
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

$filtered = array_values($filtered); // Re-index
$totalFiltered = count($filtered);
$totalPages = max(1, ceil($totalFiltered / $PER_PAGE));

// Ensure page is valid
if ($page > $totalPages) {
    $page = $totalPages;
}

// Get current page results
$start = ($page - 1) * $PER_PAGE;
$pageResults = array_slice($filtered, $start, $PER_PAGE);

// Get single song if requested
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
    <title>Public Domain Lyrics Database - Free Song Lyrics Search</title>
    <meta name="description" content="Search free public domain song lyrics. Folk songs, hymns, carols, spirituals, and more. 100% free to use.">
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
        .error { text-align: center; padding: 2rem; color: #ff6666; background: rgba(255,0,0,0.1); border-radius: 8px; }
        .disclaimer {
            background: rgba(255,200,0,0.1);
            border: 1px solid rgba(255,200,0,0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #ffcc00;
            font-size: 0.9rem;
        }
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
        .page-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Public Domain Lyrics</h1>
            <p class="subtitle">Free, searchable database of public domain song lyrics</p>
        </header>

        <div class="disclaimer">
            All lyrics in this database are verified public domain. Sources include Wikisource and Project Gutenberg.
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?php echo number_format($totalSongs); ?></div>
                <div class="stat-label">Songs</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo count($categories); ?></div>
                <div class="stat-label">Categories</div>
            </div>
            <div class="stat">
                <div class="stat-number">100%</div>
                <div class="stat-label">Free to Use</div>
            </div>
        </div>

        <?php if ($singleSong): ?>
            <!-- Single Song View -->
            <a href="?<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page; ?>" class="back-link">&larr; Back to results</a>
            
            <div class="song-card">
                <div class="song-header">
                    <div class="song-title"><?php echo escapeHtml($singleSong['title']); ?></div>
                    <span class="song-category"><?php echo escapeHtml($singleSong['category']); ?></span>
                </div>
                <div class="song-meta"><?php echo $singleSong['author'] ? 'By ' . escapeHtml($singleSong['author']) : 'Unknown author'; ?></div>
                <div class="song-lyrics full"><?php echo nl2br(escapeHtml($singleSong['lyrics'])); ?></div>
                <?php if ($singleSong['source_url'] || $singleSong['author'] || $singleSong['year']): ?>
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
            <!-- Search Form -->
            <div class="search-container">
                <form method="GET" action="">
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
                            <a href="?" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div class="results-status">
                <?php if ($query || $category): ?>
                    <?php echo number_format($totalFiltered); ?> songs found
                    <?php if ($totalPages > 1): ?>
                        (page <?php echo $page; ?> of <?php echo $totalPages; ?>)
                    <?php endif; ?>
                <?php else: ?>
                    Start searching above or browse all <?php echo number_format($totalSongs); ?> songs
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
                            <h3>Start searching</h3>
                            <p>Enter a search term or select a category</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php foreach ($pageResults as $index => $song): ?>
                        <?php $globalIndex = $start + $index; ?>
                        <div class="song-card">
                            <div class="song-header">
                                <div class="song-title">
                                    <a href="?song=<?php echo $globalIndex; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page; ?>" style="color: #00d9ff; text-decoration: none;">
                                        <?php echo escapeHtml($song['title']); ?>
                                    </a>
                                </div>
                                <span class="song-category"><?php echo escapeHtml($song['category']); ?></span>
                            </div>
                            <div class="song-meta">
                                <?php if ($song['author']): ?>By <?php echo escapeHtml($song['author']); ?><?php endif; ?>
                                <?php if ($song['year']): ?> (<?php echo escapeHtml($song['year']); ?>)<?php endif; ?>
                            </div>
                            <div class="song-lyrics">
                                <?php echo nl2br(escapeHtml(truncateLyrics($song['lyrics'] ?? '', 500))); ?>
                                <?php if (strlen($song['lyrics'] ?? '') > 500): ?>
                                    <div style="margin-top: 0.5rem;">
                                        <a href="?song=<?php echo $globalIndex; ?>&<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page; ?>" style="color: #00d9ff; font-size: 0.9rem;">Show full lyrics &rarr;</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page - 1; ?>" class="page-btn">&larr; Prev</a>
                    <?php else: ?>
                        <span class="page-btn" style="opacity: 0.3; cursor: not-allowed;">&larr; Prev</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <?php if ($i == $page): ?>
                                <span class="page-btn-current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $i; ?>" class="page-btn"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <span style="color: #666;">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo $query ? 'q=' . urlencode($query) . '&' : ''; echo $category ? 'category=' . urlencode($category) . '&' : ''; ?>page=<?php echo $page + 1; ?>" class="page-btn">Next &rarr;</a>
                    <?php else: ?>
                        <span class="page-btn" style="opacity: 0.3; cursor: not-allowed;">Next &rarr;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="sources">
            <h3>Data Sources</h3>
            <ul>
                <li>Wikisource (en.wikisource.org) - Free library of public domain texts</li>
                <li>Project Gutenberg (gutenberg.org) - 70,000+ free e-books</li>
            </ul>
        </div>

        <footer>
            <p>All lyrics are in the public domain in the United States (pre-1928)</p>
            <p>Powered by PHP + JSON</p>
        </footer>
    </div>
</body>
</html>
