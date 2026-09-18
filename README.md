# Public Domain Lyrics Database

**Free lyrics for everyone.**

> **Open source and community maintained:** Some CC0
> lyrics were generated with automated templates and may benefit from editing.
> [Report a problem](https://github.com/kennethyork/PubDomainOnly/issues/new/choose)
> or read [CONTRIBUTING.md](CONTRIBUTING.md) to help improve it.

- **1,334 verified public domain songs** - all published 1928 or earlier (US public domain)
- **3,653 CC0 original songs** - free for any use, no strings attached
- **4,987 total songs** across both collections
- **87% have source URLs** - verified against Wikisource/Project Gutenberg

No accounts. No paywalls. No terms. Just songs.

## Two Databases

### Public Domain (1,334 songs)
- Published 1928 or earlier (US public domain)
- Source URLs linking to Wikisource/Project Gutenberg
- Artist, Year, and Category metadata
- Commercial use allowed

### CC0 Original (3,653 songs)
- All original compositions (2024-2026)
- Released under CC0 - no rights reserved
- Includes automatically generated lyrics that are open for community review
- **Commercial use allowed** - sell it, train AI, remix, redistribute
- No attribution required

## Why Verified?

Anyone can scrape Wikisource. We did the work:

- **Removed books and prose** - songs only
- **Removed duplicates** - one entry per song
- **Removed dialect** - Scots/Irish Gaelic filtered
- **Removed non-songs** - textbooks, magazines, Latin hymns
- **Removed verse numbers** - clean lyrics
- **Added source URLs** - 87% verified

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

### Data

The JSON files are the single source of truth. Both the PHP and static
interfaces read them directly.

- `lyrics_data.json` - Public domain database
- `cc0_lyrics_data.json` - CC0 database
- `scripts/clean_pd_collection.php` - strips wiki boilerplate and removes non-song pages from the PD data
- `scripts/build_cc0_collection.php` - validates/rebuilds the deterministic CC0 expansion

### Web Interface
- `index.php` - Searchable PHP web interface with PD/CC0 tabs (any PHP host)
- `docs/index.html` - Static version for GitHub Pages (search, filters, and pagination run in the browser)

## GitHub Pages

The static site is in `docs/` and deploys automatically from `main` via
`.github/workflows/pages.yml`.

Live URL: https://kennethyork.github.io/PubDomainOnly/

To enable it the first time: **Settings → Pages → Build and deployment → Source:
GitHub Actions**, then push to `main` (or run the "Deploy to GitHub Pages"
workflow manually). The workflow copies `docs/index.html` plus the full JSON
databases (`lyrics_data.json`, `cc0_lyrics_data.json`) into the published site,
so all songs are searchable client-side. Navigation uses hash URLs like
`#type=cc0&q=river&page=2`, so pages can be bookmarked and shared.

Both interfaces read the same data files - update the JSON and both stay in sync.

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

See [LICENSE.md](LICENSE.md) for the complete terms.

### Source Code
MIT License.

### Public Domain
Public domain in the United States. No rights reserved. Commercial use allowed.

### CC0 Original
CC0 Public Domain Dedication. No rights reserved. Commercial use allowed. No attribution required.

## Contributing

Corrections, rewrites, metadata improvements, and new CC0 songs are welcome.
See [CONTRIBUTING.md](CONTRIBUTING.md), or
[open an issue](https://github.com/kennethyork/PubDomainOnly/issues/new/choose).

## Disclaimer

Year data is approximate (based on category). Verify before critical use. Source URLs provided when available.
