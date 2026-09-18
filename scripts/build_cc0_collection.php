<?php
declare(strict_types=1);

/**
 * Cleans the original CC0 dataset and deterministically builds the 2026
 * expansion. Run from the repository root:
 *
 *     php scripts/build_cc0_collection.php
 *
 * Existing community edits are preserved by default. To intentionally replace
 * the complete generated expansion, run with --rebuild-expansion.
 */

$root = dirname(__DIR__);
$jsonPath = $root . '/cc0_lyrics_data.json';
$expansionAuthor = 'Original Work (CC0 2026 Expansion)';
$rebuildExpansion = in_array('--rebuild-expansion', $argv, true);

$songs = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
$expansionCount = count(array_filter(
    $songs,
    static fn(array $song): bool => ($song['author'] ?? '') === $expansionAuthor
));
$buildExpansion = $rebuildExpansion || $expansionCount === 0;
if ($buildExpansion) {
    $songs = array_values(array_filter(
        $songs,
        static fn(array $song): bool => ($song['author'] ?? '') !== $expansionAuthor
    ));
}

// Repair the leaked template marker while keeping the intended opening grammar.
$openingRepairs = [
    'In the early days %s ' => 'In the early days, a ',
    'As I walked out %s ' => 'As I walked out, I met a ',
    'Away out West %s ' => 'Away out West, a ',
    'From the mountains %s ' => 'From the mountains came a ',
];

foreach ($songs as &$song) {
    $lyrics = (string) ($song['lyrics'] ?? '');
    foreach ($openingRepairs as $broken => $fixed) {
        if (str_starts_with($lyrics, $broken)) {
            $lyrics = $fixed . substr($lyrics, strlen($broken));
            break;
        }
    }
    $lyrics = str_replace('%s ', '', $lyrics);
    $lyrics = str_replace('%s', '', $lyrics);
    $lyrics = preg_replace(
        '/^(?:Folk ballads|Gospel hymns|Sea shanties|Ragtime pieces|Early blues|Barbershop quartets)\s+\d+\s+calls/m',
        'This old song calls',
        $lyrics
    );
    $song['lyrics'] = trim((string) $lyrics);
}
unset($song);

// Repeated titles contain different lyrics. Keep the works and make later titles
// unambiguous instead of discarding valid content.
$seenTitles = [];
foreach ($songs as &$song) {
    $baseTitle = trim((string) $song['title']);
    $key = strtolower($baseTitle);
    if (!isset($seenTitles[$key])) {
        $seenTitles[$key] = 1;
        continue;
    }

    ++$seenTitles[$key];
    $candidate = $baseTitle . ' (Alternate)';
    $suffix = 2;
    while (isset($seenTitles[strtolower($candidate)])) {
        $candidate = $baseTitle . ' (Alternate ' . $suffix . ')';
        ++$suffix;
    }
    $song['title'] = $candidate;
    $seenTitles[strtolower($candidate)] = 1;
}
unset($song);

$profiles = [
    'Vaudeville' => [
        'adjectives' => ['Bright', 'Velvet', 'Dapper', 'Golden', 'Jaunty', 'Painted', 'Midnight', 'Silver', 'Lucky', 'Grand'],
        'nouns' => ['Curtain', 'Footlight', 'Playbill', 'Top Hat', 'Encore', 'Spotlight', 'Trunk', 'Cane', 'Balcony', 'Marquee'],
        'regions' => ['Bijou Hall', 'Market Street', 'the Palace Stage'],
        'subjects' => ['The dancing twins', 'A quick-change comic', 'The patient stagehand', 'A red-coated drummer', 'The touring soprano', 'The soft-shoe team', 'A pocket magician', 'The balcony crowd', 'The house pianist', 'A bright-eyed juggler'],
        'actions' => ['crossed the boards in time', 'waited smiling in the wings', 'tipped a hat toward the crowd', 'kept the lively chorus moving', 'turned a stumble into style', 'shared the final bow', 'sent a laugh across the hall', 'made the old piano ring', 'raised the curtain with a grin', 'found one more trick to show'],
        'sounds' => ['A brass refrain', 'The tapping shoes', 'A rolling snare', 'The gallery laughter', 'A warm piano chord', 'The closing applause', 'A comic whistle', 'The orchestra bell', 'A backstage murmur', 'The opening fanfare'],
        'places' => ['the painted wings', 'the crowded balcony', 'the old stage door', 'the orchestra rail', 'the lamplit lobby', 'the center boards', 'the ticket window', 'the dressing room', 'the velvet curtain', 'the touring train'],
        'values' => ['good humor', 'courage', 'fellowship', 'wonder', 'kindness', 'practice', 'patience', 'delight', 'showmanship', 'hope'],
        'refrain' => 'Raise the curtain, let the whole room sing',
    ],
    'Patriotic songs' => [
        'adjectives' => ['Common', 'Steadfast', 'Open', 'Brave', 'Peaceful', 'Union', 'Neighborly', 'Free', 'Faithful', 'Morning'],
        'nouns' => ['Banner', 'Harbor', 'Promise', 'Homeland', 'Bridge', 'Bell', 'Field', 'Torch', 'River', 'Welcome'],
        'regions' => ['Liberty Square', 'the Western Hills', 'Harbor Green'],
        'subjects' => ['The town hall neighbors', 'A line of young volunteers', 'The harbor workers', 'The hillside families', 'The returning travelers', 'The village choir', 'The patient builders', 'The classroom children', 'The orchard keepers', 'The morning watch'],
        'actions' => ['stood together in the rain', 'shared the labor and the gain', 'raised a song for peaceful days', 'kept the public lantern bright', 'made a place for every voice', 'walked the long road side by side', 'carried home a hopeful word', 'joined their hands across the square', 'guarded freedom with their care', 'welcomed strangers through the gate'],
        'sounds' => ['A courthouse bell', 'The many voices', 'A river whistle', 'The schoolhouse chorus', 'A steady marching drum', 'The harbor siren', 'A summer crowd', 'The morning bugle', 'A farmhouse radio', 'The council song'],
        'places' => ['the public square', 'the wheat-gold valley', 'the harbor wall', 'the mountain road', 'the meeting house', 'the river bridge', 'the village green', 'the open border', 'the city steps', 'the quiet memorial'],
        'values' => ['liberty', 'shared duty', 'equal justice', 'peace', 'neighborly care', 'honest labor', 'civic courage', 'welcome', 'unity', 'hope'],
        'refrain' => 'Many are the voices, but the promise makes us one',
    ],
    "Children's songs" => [
        'adjectives' => ['Bouncy', 'Little', 'Polka-Dot', 'Sunny', 'Wiggly', 'Curious', 'Pocket', 'Laughing', 'Rainbow', 'Sleepy'],
        'nouns' => ['Turtle', 'Kite', 'Teacup', 'Cricket', 'Wagon', 'Button', 'Dinosaur', 'Puddle', 'Bumblebee', 'Moonbeam'],
        'regions' => ['Playground Lane', 'Picnic Hill', 'the Storybook Wood'],
        'subjects' => ['Three little ducklings', 'A rabbit in red boots', 'The playground band', 'A shy green turtle', 'Two cardboard sailors', 'The children next door', 'A pocket-sized dragon', 'The garden beetles', 'A bear with a blue bow', 'The skipping-rope crew'],
        'actions' => ['counted clouds from one to ten', 'marched around the yard again', 'built a castle out of chairs', 'shared a basket full of pears', 'made a map with purple ink', 'stopped beside the pond to think', 'taught the cat a funny tune', 'sent a paper ship to June', 'found a button by the gate', 'danced until the clock struck eight'],
        'sounds' => ['A tiny silver whistle', 'The clapping game', 'A friendly kitchen spoon', 'The rainy-window rhythm', 'A humming bumblebee', 'The sandbox chorus', 'A bright toy trumpet', 'The robin in the hedge', 'A row of wooden blocks', 'The bicycle bell'],
        'places' => ['the apple tree', 'the blanket fort', 'the garden gate', 'the puddle shore', 'the bedroom rug', 'the playground slide', 'the cookie tin', 'the library steps', 'the crooked fence', 'the picnic table'],
        'values' => ['sharing', 'curiosity', 'friendship', 'bravery', 'kindness', 'imagination', 'patience', 'laughter', 'helpfulness', 'wonder'],
        'refrain' => 'Clap your hands and count to three',
    ],
    'Comic songs' => [
        'adjectives' => ['Crooked', 'Mixed-Up', 'Backward', 'Wobbling', 'Upside-Down', 'Absent-Minded', 'Runaway', 'Mismatched', 'Ticklish', 'Impossible'],
        'nouns' => ['Umbrella', 'Mustache', 'Suitcase', 'Supper', 'Trolley', 'Alarm Clock', 'Goose', 'Waistcoat', 'Sandwich', 'Doorbell'],
        'regions' => ['Nonsense Row', 'the Odd Fellows Hall', 'Bumbleberry Square'],
        'subjects' => ['The absent-minded barber', 'A very formal goose', 'The mayor and his goldfish', 'A cook in roller skates', 'The undertaker’s parrot', 'A proud but clumsy waiter', 'The neighborhood inventor', 'A sleepwalking policeman', 'The tailor’s little donkey', 'A tourist with six maps'],
        'actions' => ['put the pepper in his shoe', 'missed the door and walked on through', 'ordered breakfast late at night', 'wore two left socks with delight', 'mailed a pancake to the moon', 'swept the sidewalk with a spoon', 'called a cab and caught a chair', 'lost a hat that was not there', 'bought a ticket for the rain', 'asked the clock to catch the train'],
        'sounds' => ['A hiccupping tuba', 'The scandalized chickens', 'A squeaky rocking chair', 'The upside-down doorbell', 'A laughing trombone', 'The kettle in the hallway', 'A syncopated sneeze', 'The neighbor’s old kazoo', 'A very puzzled rooster', 'The runaway phonograph'],
        'places' => ['the barber shop', 'the courthouse roof', 'the crowded trolley', 'the corner cafe', 'the upstairs pantry', 'the village pond', 'the tailor shop', 'the station clock', 'the garden shed', 'the banquet hall'],
        'values' => ['good humor', 'comic timing', 'cheerful chaos', 'a second chance', 'plain silliness', 'quick thinking', 'happy accidents', 'shared laughter', 'a graceful exit', 'one last joke'],
        'refrain' => 'Laugh till the rafters rattle and the chandeliers all sway',
    ],
    'Work songs' => [
        'adjectives' => ['Steady', 'Iron', 'Morning', 'Union', 'Rolling', 'Hammer', 'Foundry', 'Harvest', 'Timber', 'Railroad'],
        'nouns' => ['Shift', 'Whistle', 'Anvil', 'Shovel', 'Lantern', 'Crew', 'Furnace', 'Wagon', 'Toolbox', 'Workday'],
        'regions' => ['Milltown Yard', 'the Northern Line', 'Harvest Valley'],
        'subjects' => ['The early-shift crew', 'A line of track workers', 'The orchard pickers', 'The foundry team', 'The bridge builders', 'The dockside loaders', 'The hillside loggers', 'The garment cutters', 'The night-train firemen', 'The harvest hands'],
        'actions' => ['kept a strong and measured pace', 'passed the heavy load along', 'matched each hammer to the song', 'shared the water and the shade', 'checked the work that they had made', 'held the line through heat and rain', 'cleared the snow beside the train', 'raised the beam and set it true', 'finished what they came to do', 'left the tools prepared for dawn'],
        'sounds' => ['The five o’clock whistle', 'A hammer on the iron', 'The turning mill wheel', 'A shovel in the gravel', 'The loading-chain rhythm', 'A saw across the timber', 'The tractor in the barley', 'A needle through the fabric', 'The fire beneath the boiler', 'The foreman’s morning call'],
        'places' => ['the loading yard', 'the orchard rows', 'the engine shed', 'the factory floor', 'the half-built bridge', 'the forest grade', 'the harvest field', 'the cutting room', 'the riverside dock', 'the mountain tunnel'],
        'values' => ['solidarity', 'fair dealing', 'craft', 'endurance', 'shared effort', 'safety', 'dignity', 'fair wages', 'skill', 'rest well earned'],
        'refrain' => 'Hand to hand, we pass the work along',
    ],
    'Waltzes' => [
        'adjectives' => ['Blue', 'Autumn', 'Moonlit', 'Garden', 'Crystal', 'Tender', 'Evening', 'Whispering', 'Old-Fashioned', 'Silver'],
        'nouns' => ['Waltz', 'Rose', 'Memory', 'Gazebo', 'Invitation', 'Ribbon', 'Portrait', 'Promenade', 'Locket', 'Orchestra'],
        'regions' => ['Willow Terrace', 'the Grand Hotel', 'Starlight Pier'],
        'subjects' => ['The last two dancers', 'A violin and cello', 'The guests in the garden', 'A pair of old sweethearts', 'The lamplight orchestra', 'The dancers by the window', 'A traveler and a dreamer', 'The bride’s smiling parents', 'The quiet ballroom keeper', 'Two shadows on the terrace'],
        'actions' => ['turned beneath the chandelier', 'counted gently, one-two-three', 'crossed the polished floor with grace', 'held a quiet moment near', 'followed where the violins led', 'let the evening drift away', 'found the old familiar step', 'watched the candles burning low', 'shared a promise without words', 'bowed before the final chord'],
        'sounds' => ['A tender violin', 'The measured orchestra', 'A distant cello phrase', 'The ballroom piano', 'A soft cornet answer', 'The rustle of a gown', 'A lilting three-beat tune', 'The final sweet cadence', 'A melody remembered', 'The terrace string trio'],
        'places' => ['the marble stair', 'the garden wall', 'the ballroom door', 'the mirrored hall', 'the moonlit pier', 'the willow walk', 'the hotel terrace', 'the fountain court', 'the candlelit room', 'the quiet promenade'],
        'values' => ['tenderness', 'trust', 'old friendship', 'devotion', 'grace', 'remembrance', 'new love', 'forgiveness', 'gentleness', 'hope'],
        'refrain' => 'One-two-three, let the turning music flow',
    ],
    'Lullabies' => [
        'adjectives' => ['Quiet', 'Little', 'Starlit', 'Silver', 'Drowsy', 'Gentle', 'Moonlit', 'Feather', 'Dreaming', 'Twilight'],
        'nouns' => ['Cradle', 'Sparrow', 'Cloud', 'Lantern', 'Blanket', 'Harbor', 'Willow', 'Moon', 'Night Wind', 'Dream'],
        'regions' => ['Slumber Bay', 'the Willow Room', 'Starry Meadow'],
        'subjects' => ['The smallest sleepy sparrow', 'A child beneath the quilt', 'The moon above the chimney', 'A cradle by the window', 'The lambs beyond the meadow', 'The night-light on the table', 'A little boat of dreaming', 'The stars above the rooftop', 'The wind among the willows', 'A firefly in the garden'],
        'actions' => ['settled softly for the night', 'closed its eyes beneath the light', 'floated past the windowpane', 'rested after evening rain', 'heard the quiet clock downstairs', 'left the daylight to its cares', 'sailed beyond the silver hill', 'breathed until the room was still', 'found a nest of folded blue', 'kept a gentle watch for you'],
        'sounds' => ['A low and tender humming', 'The rain upon the rooftop', 'A far-off evening train', 'The whispering curtains', 'A small bedside music box', 'The wind beyond the window', 'A cricket by the doorstep', 'The waves along the harbor', 'A night bird in the willow', 'The old hall clock'],
        'places' => ['the bedroom door', 'the quiet eaves', 'the folded quilt', 'the window ledge', 'the willow shade', 'the moonlit floor', 'the nursery chair', 'the garden wall', 'the harbor shore', 'the chimney top'],
        'values' => ['safety', 'rest', 'tender care', 'peace', 'comfort', 'belonging', 'sweet dreams', 'warmth', 'stillness', 'love'],
        'refrain' => 'Sleep now, little heart, till morning fills the sky',
    ],
];

$targetCounts = [
    'Vaudeville' => 265,
    'Patriotic songs' => 265,
    "Children's songs" => 265,
    'Comic songs' => 265,
    'Work songs' => 265,
    'Waltzes' => 264,
    'Lullabies' => 264,
];

if ($buildExpansion) {
    foreach ($profiles as $category => $profile) {
        for ($i = 0; $i < $targetCounts[$category]; ++$i) {
            $a = $i % 10;
            $b = intdiv($i, 10) % 10;
            $c = intdiv($i, 100) % 3;
            $d = ($i * 3 + 1) % 10;
            $e = ($i * 7 + 4) % 10;
            $f = ($i * 9 + 2) % 10;

            $title = $profile['adjectives'][$a] . ' ' . $profile['nouns'][$b] . ' of ' . $profile['regions'][$c];
            if (isset($seenTitles[strtolower($title)])) {
                $title .= ' (' . $category . ')';
            }
            $seenTitles[strtolower($title)] = 1;

            $lyrics = implode("\n", [
                $profile['subjects'][$a] . ' ' . $profile['actions'][$d] . ',',
                $profile['sounds'][$b] . ' answered from ' . $profile['places'][$e] . ';',
                'Through the hours they carried ' . $profile['values'][$f] . ',',
                'And made a song from all that brought them there.',
                '',
                'Chorus:',
                $profile['refrain'] . ',',
                'Carry “' . $title . '” wherever voices go;',
                'Let every listening neighbor take a part,',
                'And keep its honest measure in the heart.',
                '',
                $profile['subjects'][$e] . ' ' . $profile['actions'][$b] . ',',
                $profile['sounds'][$f] . ' traveled past ' . $profile['places'][$a] . ';',
                'No one there would trade away ' . $profile['values'][$d] . ',',
                'For every careful voice had something true to share.',
                '',
                'When evening settled over ' . $profile['places'][$c + 2] . ',',
                $profile['subjects'][$f] . ' ' . $profile['actions'][$e] . ';',
                'They left behind a little ' . $profile['values'][$b] . ',',
                'A tune for any traveler passing there.',
                '',
                'Chorus:',
                $profile['refrain'] . ',',
                'Carry “' . $title . '” wherever voices go;',
                'Let every listening neighbor take a part,',
                'And keep its honest measure in the heart.',
            ]);

            $songs[] = [
                'title' => $title,
                'author' => $expansionAuthor,
                'year' => '2026',
                'category' => $category,
                'lyrics' => $lyrics,
                'source_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            ];
        }
    }
}

file_put_contents(
    $jsonPath,
    json_encode($songs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n"
);

$expansionCount = count(array_filter(
    $songs,
    static fn(array $song): bool => ($song['author'] ?? '') === $expansionAuthor
));
printf(
    "Synchronized %d CC0 songs (%d community/legacy + %d generated expansion).\n",
    count($songs),
    count($songs) - $expansionCount,
    $expansionCount
);
