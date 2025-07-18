<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

function generateQuestion() {
    $a = rand(1, 50);
    $b = rand(1, 50);
    $type = rand(0, 1);
    if ($type === 1 && $a < $b) $a += $b;

    $_SESSION['a'] = $a;
    $_SESSION['b'] = $b;
    $_SESSION['type'] = $type;
    $_SESSION['question'] = $type === 0 ? "$a + $b" : "$a - $b";
    $_SESSION['answer'] = $type === 0 ? $a + $b : $a - $b;
    $_SESSION['attempts'] = 0;
}

function getAvatar($lvl) {
    if ($lvl >= 5) return "dragon-legend.png";
    if ($lvl >= 3) return "dragon-young.png";
    return "dragon-baby.png";
}

function explain($a, $b, $type, $showAnswer = false) {
    $operation = $type === 0 ? '+' : '-';
    $result = $type === 0 ? ($a + $b) : ($a - $b);
    $answerLine = $showAnswer ? "  $result" : "  ???";

    return "<div class='alert alert-info'>
        <h5>💡 Як розрахувати $a $operation $b</h5>
        <pre style='text-align: left; font-size: 1.2em'>
  $a
$operation $b
-----
$answerLine
        </pre>
    </div>";
}



if (!isset($_SESSION['xp'])) {
    $_SESSION['xp'] = 0;
    $_SESSION['level'] = 1;
    $_SESSION['attempts'] = 0;
    $_SESSION['message'] = '';
    $_SESSION['flash'] = false;
    generateQuestion();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restart'])) {
        session_destroy();
        exit;
    }

    $ans = intval($_POST['answer']);
    if ($ans === $_SESSION['answer']) {
        $_SESSION['xp'] += 10;
        if ($_SESSION['xp'] >= 50) {
            $_SESSION['level']++;
            $_SESSION['xp'] = 0;
        }
        $_SESSION['message'] = "<div class='alert alert-success'>🎉 Правильно! XP: {$_SESSION['xp']}, рівень: {$_SESSION['level']}</div>";
        $_SESSION['flash'] = true;
        generateQuestion();
    } else {
        $_SESSION['attempts']++;
        $_SESSION['flash'] = false;
        if ($_SESSION['attempts'] === 1) {
            $_SESSION['message'] = explain($_SESSION['a'], $_SESSION['b'], $_SESSION['type'], false);
        } elseif ($_SESSION['attempts'] >= 2) {
            $_SESSION['message'] = explain($_SESSION['a'], $_SESSION['b'], $_SESSION['type'], true);
            generateQuestion();
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger'>❌ Невірно. Спробуй ще раз!</div>";
        }
    }
}

    echo json_encode([
    'question' => $_SESSION['question'],
    'xp' => $_SESSION['xp'],
    'level' => $_SESSION['level'],
    'xp_percent' => ($_SESSION['xp'] / 50) * 100,
    'avatar' => getAvatar($_SESSION['level']),
    'message' => $_SESSION['message'],
    'flash' => $_SESSION['flash'],
    'victory' => $_SESSION['level'] >= 5
]);
