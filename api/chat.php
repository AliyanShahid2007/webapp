<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$message = trim($body['message'] ?? '');
if ($message === '') {
    echo json_encode(['error' => 'Empty message']);
    exit;
}

// Quick intent handling: greetings
if (preg_match('/\b(hi|hello|hey|salam|asalaam|hola)\b/i', $message)){
    echo json_encode(['reply' => "Hello! I'm the Site Assistant. Ask me about gigs, freelancers, browsing, or orders.", 'source'=>'fallback']);
    exit;
}

// Load corpus
$corpusFile = __DIR__ . '/../data/site_corpus.json';
$docs = [];
if (file_exists($corpusFile)) {
    $raw = file_get_contents($corpusFile);
    $docs = json_decode($raw, true) ?: [];
}

function retrieve_top_docs($docs, $query, $top=3){
    $qwords = preg_split('/\s+/', strtolower($query));
    $qset = array_filter($qwords);
    $scores = [];
    foreach ($docs as $i => $d){
        $text = strtolower($d['text'] ?? '');
        $score = 0;
        foreach ($qset as $w){
            if ($w === '') continue;
            if (strpos($text, $w) !== false) $score++;
        }
        $scores[$i] = $score;
    }
    arsort($scores);
    $out = [];
    foreach (array_slice($scores, 0, $top, true) as $idx => $s){
        if ($s <= 0) continue;
        $out[] = ['path' => $docs[$idx]['path'] ?? '', 'text' => $docs[$idx]['text']];
    }
    return $out;
}

$top = retrieve_top_docs($docs, $message, 3);

// Check for OpenAI API key in env or config
// Allow an ephemeral OpenAI key passed in the request body for one-time testing only.
// WARNING: do NOT persist or log this key. It will only be used for this request.
$apiKey = $body['openai_key'] ?? (getenv('OPENAI_API_KEY') ?: (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null));
if ($apiKey && !empty($top)){
    // build system and user messages
    $context = "Context:\n";
    foreach ($top as $t){
        $context .= "- ({$t['path']}) " . substr($t['text'], 0, 800) . "\n\n";
    }

    $system = "You are an assistant for a website. Use only the provided context to answer. If the answer is not in the context, say you don't know and suggest where to look.";
    $user = $context . "\nQuestion: " . $message;

    $payload = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role'=>'system','content'=>$system],
            ['role'=>'user','content'=>$user]
        ],
        'temperature' => 0.2,
        'max_tokens' => 500
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($res){
        $j = json_decode($res, true);
        if (isset($j['choices'][0]['message']['content'])){
            $reply = trim($j['choices'][0]['message']['content']);
            echo json_encode(['reply'=>$reply, 'source'=>'openai', 'citations'=>$top]);
            exit;
        }
    }
    // fall through to local fallback on error
}

// Local fallback: produce a concise, conversational reply by extracting matched sentences
if (!empty($top)){
    // Prefer human-readable pages first (about, index, gig pages, profile, terms)
    $priority = ['about.php','index.php','gig-details.php','freelancer-profile.php','freelancer/profile.php','terms.php'];
    usort($top, function($a, $b) use ($priority){
        $pa = array_search(str_replace('\\','/',$a['path']), $priority);
        $pb = array_search(str_replace('\\','/',$b['path']), $priority);
        $pa = $pa === false ? 999 : $pa;
        $pb = $pb === false ? 999 : $pb;
        return $pa <=> $pb;
    });
    function extract_sentences($text, $query, $max_per_doc = 2){
        $sents = preg_split('/(?<=[\.\?!])\s+/', $text);
        $qwords = array_filter(preg_split('/\s+/', strtolower($query)));
        $out = [];
        foreach ($sents as $s){
            $ls = strtolower($s);
            foreach ($qwords as $w){
                if ($w === '') continue;
                if (strpos($ls, $w) !== false){
                    $candidate = trim($s);
                    if ($candidate !== '') $out[] = $candidate;
                    break;
                }
            }
            if (count($out) >= $max_per_doc) break;
        }
        return $out;
    }

    $collected = [];
    $citations = [];
    foreach ($top as $t){
        $s = isset($t['text']) ? $t['text'] : '';
        $found = extract_sentences($s, $message, 2);
        if (!empty($found)){
            foreach ($found as $f) $collected[] = $f;
            $citations[] = $t['path'] ?? '';
        }
    }

    if (!empty($collected)){
        // choose up to 3 concise sentences from prioritized docs
        $collected = array_slice($collected, 0, 3);
        // clean and trim each sentence
        $collected = array_map(function($s){
            $s = trim(preg_replace('/\s+/', ' ', $s));
            if (strlen($s) > 300) $s = substr($s,0,297) . '...';
            return $s;
        }, $collected);
        $reply_body = implode(' ', $collected);
        $reply = "Summary: " . $reply_body;
        echo json_encode(['reply' => $reply, 'source' => 'local', 'citations' => $citations]);
        exit;
    }
}

echo json_encode(['reply' => "I couldn't find relevant information in the site's corpus. Run the corpus builder (php scripts/build_corpus.php) or enable an OpenAI API key for synthesized answers.", 'source'=>'none']);
