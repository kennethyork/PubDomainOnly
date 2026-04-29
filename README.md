# Public Domain Lyrics Database

**5,563 public domain songs** with full lyrics, artist, and year metadata.

## What's Included

- **5,563 songs** - all published 1928 or earlier (US public domain)
- **Full lyrics** with proper line breaks and stanza formatting
- **Artist/Author** and **Year** metadata for each song
- **Categories**: Folk songs, Hymns, Sea songs, Patriotic songs, Spirituals, Ballads, Love songs, Drinking songs, Children's songs, Christmas carols, Work songs

## Data Format

Each entry contains:
```json
{
  "title": "Song Title",
  "author": "Artist Name",
  "year": "1905",
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
- **Project Gutenberg** - Additional songs and ballads

All content is verified public domain in the United States.

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
      console.log(`${song.title} by ${song.author} (${song.year})`);
    });
  });
```

## License

All songs are in the **public domain** in the United States. No rights reserved.

## Categories

| Category | Count |
|----------|-------|
| Folk songs | ~1,500 |
| Hymns | ~1,200 |
| Sea songs | ~600 |
| Patriotic songs | ~500 |
| Spirituals | ~400 |
| Ballads | ~350 |
| Love songs | ~300 |
| Drinking songs | ~250 |
| Work songs | ~200 |
| Children's songs | ~150 |
| Christmas carols | ~100 |

## Contributing

This is a static database. To add songs:
1. Verify the song was published before 1929
2. Verify it's actually a song/lyrics (not prose or poetry)
3. Include artist and year metadata when available

## Disclaimer

All lyrics in this database are verified public domain. Sources include Wikisource and Project Gutenberg. Year data is extracted automatically and may be inaccurate - verify before critical use.
