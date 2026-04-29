# Public Domain Lyrics Database

**1,517 verified public domain songs** with full lyrics, metadata, and source URLs.

## What's Included

- **1,517 songs** - all published 1928 or earlier (US public domain)
- **Full lyrics** with proper line breaks and stanza formatting
- **Artist/Author**, **Year**, and **Category** metadata for each song
- **Source URLs** linking to original Wikisource/Project Gutenberg entries
- **Verified clean** - no book excerpts, no prose, no duplicates

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

- `lyrics_data.json` - Full database in JSON format
- `data.php` - PHP array version for fast loading
- `index.php` - Searchable web interface

## Sources

All songs are from:
- **Wikisource** (en.wikisource.org) - Primary source
- **Project Gutenberg** (gutenberg.org) - Additional songs

All content is verified public domain in the United States.

## Categories

| Category | Est. Count |
|----------|------------|
| Hymns | ~400 |
| Folk songs | ~350 |
| Work songs | ~150 |
| Sea songs | ~120 |
| Patriotic songs | ~100 |
| Spirituals | ~80 |
| Love songs | ~70 |
| Children's songs | ~60 |
| Ballads | ~50 |
| Christmas carols | ~40 |
| Drinking songs | ~30 |

## Usage

### Web Interface
Upload to any PHP-enabled web host and visit index.php.

### JSON API
Access `lyrics_data.json` directly:
```javascript
fetch('lyrics_data.json')
  .then(r => r.json())
  .then(songs => {
    songs.forEach(song => {
      console.log(`${song.title} by ${song.author}`);
    });
  });
```

## License

All songs are in the **public domain** in the United States. No rights reserved.

## Disclaimer

Year data is approximate (based on category). Verify before critical use. Source URLs provided when available.