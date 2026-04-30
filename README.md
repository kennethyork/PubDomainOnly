# Public Domain Lyrics Database

**Free lyrics for everyone.**

- **1,347 verified public domain songs** - all published 1928 or earlier (US public domain)
- **2,726 CC0 original songs** - free for any use, no strings attached
- **93% have source URLs** - verified against Wikisource/Project Gutenberg

No accounts. No paywalls. No terms. Just songs.

## Two Databases

### Public Domain (1,347 songs)
- Published 1928 or earlier (US public domain)
- Source URLs linking to Wikisource/Project Gutenberg
- Artist, Year, and Category metadata

### CC0 Original (2,726 songs)
- All original compositions (2024)
- Released under CC0 - no rights reserved
- Safe for anyone to use however they want

## Why Verified?

Anyone can scrape Wikisource. We did the work:

- **Removed books and prose** - songs only
- **Removed duplicates** - one entry per song
- **Removed dialect** - Scots/Irish Gaelic filtered
- **Removed non-songs** - textbooks, magazines, Latin hymns
- **Removed verse numbers** - clean lyrics
- **Added source URLs** - 93% verified

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
Hymns, Folk songs, Sea songs, Spirituals, Work songs, Patriotic songs, Love songs, Children's songs, Ballads, Christmas carols, Drinking songs

### CC0 Original
Folk ballads, Gospel hymns, Sea shanties, Ragtime, Early blues, Barbershop quartets, Vaudeville, Patriotic, Children's songs, Comic songs, Work songs, Waltzes, Lullabies

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
Upload to any PHP host and visit index.php.

### JSON API
```javascript
fetch('lyrics_data.json')
  .then(r => r.json())
  .then(songs => console.log(songs));

fetch('cc0_lyrics_data.json')
  .then(r => r.json())
  .then(songs => console.log(songs));
```

## License

### Public Domain
Public domain in the United States. No rights reserved.

### CC0 Original
CC0 Public Domain Dedication. No rights reserved, no attribution required.

## Disclaimer

Year data is approximate (based on category). Verify before critical use. Source URLs provided when available.