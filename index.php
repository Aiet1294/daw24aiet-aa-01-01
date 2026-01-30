<?php

require_once 'vendor/autoload.php';

function is_text($text, $min, $max)
{
    $length = strlen($text);
    return $length >= $min && $length <= $max;
}

function is_element_selected($value)
{
    return !empty($value);
}

$hizkuntzak = [
    'euskera'   => 'Euskera',
    'gaztelera' => 'Gaztelera',
    'ingelesa'  => 'Ingelesa',
];

$data = [
    'testua' => '',
    'hizkuntza' => '',
];

$errors = [
    'testua' => '',
    'hizkuntza' => '',
];

$message = '';
$translation = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data['testua'] = $_POST['testua'] ?? '';
    $data['hizkuntza'] = $_POST['hizkuntza'] ?? '';

    $errors['testua'] = is_text(trim($data['testua']), 1, 500)
        ? ''
        : 'Itzuli beharreko testua falta da.';

    $errors['hizkuntza'] = array_key_exists($data['hizkuntza'], $hizkuntzak)
        ? ''
        : 'Aukeratu hizkuntza bat.';

    $invalid = implode($errors);
    if ($invalid) {
        $message = 'Mesedez, zuzendu ondorengo akatsak.';
    } else {
        try {
            $client = OpenAI::client(getenv('OPENAI_API_KEY'));

            $target_lang = match ($data['hizkuntza']) {
                'gaztelera' => 'Spanish',
                'euskera' => 'Basque',
                'ingelesa' => 'English'
            };

            $result = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'Translate the following text into ' . $target_lang . ' only. Respond with just the translated text.'],
                    ['role' => 'user', 'content' => $data['testua']],
                ],
            ]);

            $translationContent = $result->choices[0]->message->content;
            $translation = nl2br(htmlspecialchars($translationContent));
        } catch (\Exception $e) {
            $message = 'Errorea: ' . $e->getMessage();
        }
    }
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__);
$twig = new \Twig\Environment($loader);

echo $twig->render('index.twig', [
    'hizkuntzak' => $hizkuntzak,
    'data' => $data,
    'errors' => $errors,
    'message' => $message,
    'translation' => $translation,
]);


