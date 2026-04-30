# Public Domain Lyrics Database

**The largest verified English public domain lyrics database with commercial CC0 backup.**

- **1,347 verified public domain songs** - all published 1928 or earlier (US public domain)
- **2,726 CC0 original songs** - commercial-safe for AI training, resale, and any use
- **93% have source URLs** - verified against Wikisource/Project Gutenberg

## Two Databases

### Public Domain (1,347 songs)
- Published 1928 or earlier (US public domain)
- Source URLs linking to Wikisource/Project Gutenberg
- Artist, Year, and Category metadata
- **No rights reserved** - free for any use

### CC0 Original (2,726 songs)
- All original compositions (2024)
- Released under CC0 Public Domain Dedication
- **No attribution required** - no rights reserved
- Safe for commercial AI training and resale

## Why Verified?

Anyone can scrape Wikisource. We did the work:

- **Removed books and prose** - songs only
- **Removed duplicates** - one entry per song
- **Removed dialect** - Scots/Irish Gaelic filtered
- **Removed non-songs** - textbooks, magazines, Latin hymns
- **Removed verse numbers** - clean lyrics
- **Added source URLs** - 93% verified

Result: **Clean, verified, free** lyrics - not scraped noise.

## Data Format

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
- `lyrics_data.json` - Full database in JSON
- `data.php` - PHP array for fast loading

### CC0 Original
- `cc0_lyrics_data.json` - CC0 songs in JSON
- `cc0_data.php` - PHP array for fast loading

### Web Interface
- `index.php` - Searchable web interface with PD/CC0 tabs

## Categories

### Public Domain
| Category | Count |
|----------|-------|
| Hymns | ~400 |
| Folk songs | ~350 |
| Sea songs | ~120 |
| Spirituals | ~80 |
| Work songs | ~60 |
| Patriotic songs | ~50 |
| Love songs | ~50 |
| Children's songs | ~40 |
| Ballads | ~40 |
| Christmas carols | ~30 |
| Drinking songs | ~25 |

### CC0 Original
Includes: Folk ballads, Gospel hymns, Sea shanties, Ragtime, Early blues, Barbershop quartets, Vaudeville, Patriotic, Children's songs, Comic songs, Work songs, Waltzes, Lullabies

## Sources

### Public Domain
All songs from:
- **Wikisource** (en.wikisource.org)
- **Project Gutenberg** (gutenberg.org)

Verified public domain in the United States.

### CC0 Original
All original compositions released under CC0 Public Domain Dedication.

## Usage

### Web Interface
Upload to any PHP host. Use tabs to switch between Public Domain and CC0.

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
**No rights reserved.** Public domain in the United States.

### CC0 Original
**No rights reserved.** CC0 Public Domain Dedication - no attribution required.

## Disclaimer

Year data is approximate (based on category). Verify before critical use. Source URLs provided when available.