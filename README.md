# Public Domain Lyrics Database

**1,347 verified public domain songs** with full lyrics, metadata, and source URLs.
**1,606 CC0 original songs** for commercial use without restrictions.

## Two Databases

### Public Domain (1,347 songs)
- Published 1928 or earlier (US public domain)
- Source URLs linking to Wikisource/Project Gutenberg
- Artist, Year, and Category metadata
- No rights reserved - free for any use

### CC0 Original (1,606 songs)
- All original compositions (2024)
- Released under CC0 - no rights reserved, no attribution required
- Same format and metadata structure
- Safe for commercial AI training and resale

## Data Format

Each entry contains:
```json
{
  "title": "Song Title",
  "author": "Artist Name",
  "year": "1850",
  "category": "Folk songs",
  "lyrics": "Full lyrics text...",
  "source_url": "https://source.url"
}
```

## Files

### Public Domain
- `lyrics_data.json` - Full database in JSON format
- `data.php` - PHP array version for fast loading

### CC0 Original
- `cc0_lyrics_data.json` - CC0 songs in JSON format
- `cc0_data.php` - PHP array version for fast loading

### Web Interface
- `index.php` - Searchable web interface with PD/CC0 tab switching

## Categories

| Category | PD Count | CC0 Count |
|----------|----------|----------|
| Hymns | ~400 | ~100 |
| Folk songs | ~350 | ~600 |
| Sea songs | ~120 | ~200 |
| Spirituals | ~80 | ~50 |
| Work songs | ~60 | ~50 |
| Love songs | ~50 | ~50 |
| Patriotic songs | ~50 | ~50 |
| Children's songs | ~40 | ~50 |
| Ballads | ~40 | ~50 |
| Christmas carols | ~30 | ~50 |
| Drinking songs | ~25 | ~50 |

## Sources

### Public Domain
All songs are from:
- **Wikisource** (en.wikisource.org) - Primary source
- **Project Gutenberg** (gutenberg.org) - Additional songs

All content is verified public domain in the United States.

### CC0 Original
All original compositions released under CC0 Public Domain Dedication.

## Usage

### Web Interface
Upload to any PHP-enabled web host and visit index.php. Use tabs to switch between Public Domain and CC0 databases.

### JSON API
```javascript
// Public Domain
fetch('lyrics_data.json')
  .then(r => r.json())
  .then(songs => console.log(songs.length, 'PD songs'));

// CC0 Original
fetch('cc0_lyrics_data.json')
  .then(r => r.json())
  .then(songs => console.log(songs.length, 'CC0 songs'));
```

## License

### Public Domain
All songs are in the **public domain** in the United States. No rights reserved.

### CC0 Original
Released under **CC0 Public Domain Dedication**. No rights reserved, no attribution required.

## Disclaimer

Year data is approximate (based on category). Verify before critical use. Source URLs provided when available.