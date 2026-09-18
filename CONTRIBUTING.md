# Contributing

Thanks for helping improve the Public Domain Lyrics Database. Corrections,
rewrites, metadata improvements, source verification, and new CC0 songs are
welcome.

## Before You Contribute

- Only submit text you wrote and have the right to dedicate to CC0, or text you
  have verified is in the public domain in the United States.
- Do not submit copyrighted lyrics, close rewrites of copyrighted lyrics, or
  text copied from an unverified website.
- Automated CC0 entries are starting points. Human rewrites that improve
  grammar, originality, imagery, or singability are encouraged.
- Include a reliable source URL for public-domain songs whenever possible.

## Report a Problem

Use the [issue forms](https://github.com/kennethyork/PubDomainOnly/issues/new/choose)
to report incorrect lyrics, duplicate songs, questionable rights status, broken
sources, or site problems.

For a rights concern, include the title, author, publication date, source, and
the reason the entry may not be reusable. Removing or disabling a questionable
entry takes priority over keeping the collection count unchanged.

## Edit or Add a Song

The JSON files are the single source of truth.

1. Edit `lyrics_data.json` for verified public-domain material or
   `cc0_lyrics_data.json` for CC0 material.
2. Preserve the existing fields: `title`, `author`, `year`, `category`,
   `lyrics`, and `source_url`.
3. For a new CC0 song, use your chosen contributor name in `author` and use
   `https://creativecommons.org/publicdomain/zero/1.0/` as `source_url` to
   confirm the dedication.
4. Run `php scripts/build_cc0_collection.php` after changing the CC0 JSON. This
   preserves community edits and rewrites the JSON cleanly.
5. Do not use `--rebuild-expansion` unless you intentionally want to replace
   every entry marked `Original Work (CC0 2026 Expansion)`.
6. Run `php scripts/clean_pd_collection.php --dry-run` after changing the PD
   JSON to check for scraped boilerplate or non-song pages.
7. Open a pull request describing what changed and how you checked it.

## Checks

Run these before opening a pull request:

```bash
jq empty lyrics_data.json cc0_lyrics_data.json
php -l index.php
php -l scripts/build_cc0_collection.php
php -l scripts/clean_pd_collection.php
php index.php > /dev/null
```

Also search for an existing title before adding a song and read the rendered
lyrics from beginning to end.

## Contribution Terms

Code contributions are accepted under the MIT License. By submitting original
lyrics to the CC0 collection, you dedicate your contribution under CC0 1.0.
Public-domain submissions remain public domain; contributors must provide enough
information for others to verify that status.
