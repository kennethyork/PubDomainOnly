<?php
declare(strict_types=1);

/**
 * Cleans the public-domain dataset:
 *   - strips trailing Wikisource/Wikipedia license boilerplate from lyrics
 *   - removes non-song pages (category listings, author bios, book front matter)
 *   - removes entries whose lyrics are empty after cleaning
 *   - rewrites lyrics_data.json
 *
 * Run from the repository root:
 *
 *     php scripts/clean_pd_collection.php
 *
 * Use --dry-run to report what would change without writing anything.
 */

$root = dirname(__DIR__);
$jsonPath = $root . '/lyrics_data.json';
$dryRun = in_array('--dry-run', $argv, true);

$songs = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
$before = count($songs);

/**
 * Non-song pages scraped from Wikisource metadata namespaces and book front
 * matter. Matched against the start of the lyrics so song text is never hit.
 */
$junkPatterns = [
    '/^Category:/i',
    '/^Template:/i',
    '/^Author:/i',
    '/^Portal:/i',
    '/^\s*This category is populated/i',
    '/^\s*The following \d+ pages are in this category/i',
    '/^\s*Some or all works by this author/i',
    '/^\s*Works in this category were published/i',
    '/^\s*Agents for the sale of the Early English Text Society/i',
    '/^\s*Aperiplus\s*or guidebook/i',
    '/^\s*PULCHRISM/i',
    '/^\s*All rights reserved\./i',
    '/^\s*Edited by .*Woodward/i',
    '/^\s*English novelist, playwright/i',
    '/^\s*pseudonym used by/i',
    '/^\s*Second edition; First edition/i',
    '/^\s*←Versions of/i',
    '/^\s*Versions of .* include/i',
];

/**
 * Entries whose lyrics only contain license text, ISBN data, or other
 * publishing metadata have no lyric content to keep.
 */
$dropPatterns = [
    '/Copyright \d{4}/i',
    '/\bISBN\b/',
    '/All rights reserved/i',
    '/Creative Commons\s+Attribution \d/i',
    '/has a separate copyright status/i',
];

/**
 * Wikisource appends a license block to transcluded pages. Everything from the
 * first "This work ..." paragraph onward is template output, not lyrics.
 */
$licenseBlock = '/\n*This work (?:was published before|is in the|may be in the|is released under)/';
$publicDomainTail = '/\n*Public domain\nPublic domainfalsefalse\s*$/';
$copyrightTail = '/\n*This work may be in the\s*/';

$removedJunk = [];
$removedNoLyrics = [];
$stripped = 0;
$kept = [];

foreach ($songs as $song) {
    $title = (string) ($song['title'] ?? '');
    $lyrics = (string) ($song['lyrics'] ?? '');
    $sourceUrl = (string) ($song['source_url'] ?? '');

    if (preg_match('#/wiki/(?:Author|Category|Template|Portal|Help):#i', $sourceUrl)) {
        $removedJunk[] = $title;
        continue;
    }

    foreach ($junkPatterns as $pattern) {
        if (preg_match($pattern, $lyrics) || preg_match($pattern, $title)) {
            $removedJunk[] = $title;
            continue 2;
        }
    }

    foreach ($dropPatterns as $pattern) {
        if (preg_match($pattern, $lyrics)) {
            $removedJunk[] = $title;
            continue 2;
        }
    }

    $clean = preg_split($licenseBlock, $lyrics)[0];
    $clean = preg_replace($publicDomainTail, '', $clean);
    $clean = preg_replace($copyrightTail, '', $clean);
    $clean = rtrim((string) $clean);

    if ($clean !== rtrim($lyrics)) {
        ++$stripped;
    }

    $lines = array_filter(
        array_map('trim', explode("\n", $clean)),
        static fn (string $line): bool => $line !== ''
    );

    if (count($lines) < 4) {
        $removedNoLyrics[] = $title;
        continue;
    }

    $song['lyrics'] = $clean;
    if (!isset($song['author'])) {
        $song['author'] = '';
    }
    $kept[] = $song;
}

$kept = array_values($kept);

printf(
    "%s: %d songs -> %d kept\n",
    $dryRun ? 'DRY RUN' : 'Cleaned',
    $before,
    count($kept)
);
printf("  lyrics boilerplate stripped: %d\n", $stripped);
printf("  non-song entries removed: %d\n", count($removedJunk));
printf("  entries removed for missing lyrics: %d\n", count($removedNoLyrics));

if ($dryRun) {
    foreach ($removedJunk as $title) {
        echo "  [junk] $title\n";
    }
    foreach ($removedNoLyrics as $title) {
        echo "  [no lyrics] $title\n";
    }
    exit(0);
}

file_put_contents(
    $jsonPath,
    json_encode($kept, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n"
);

echo "Wrote lyrics_data.json\n";
