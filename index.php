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
                    ['role' => 'system', 'content' => 'Translate the following text into ' . $target_lang . 'be only. Respond with just the translated text.'],
                    ['role' => 'user', 'content' => $data['testua']],
                ],
            ]);

            $translationContent = $result->choices[0]->message->content;
            $translation = 'Itzulpena:<br>' . nl2br(htmlspecialchars($translationContent));
        } catch (\Exception $e) {
            $message = 'Errorea: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testuen itzultzailea</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            background-color: #f0f0f0;
            padding: 20px;
        }

        h1 {
            color: #000;
            text-align: center;
            margin-bottom: 20px;
        }


        table {
            border: 3px solid #777;
            border-spacing: 5px;
            background-color: #eee;
            margin: 0 auto;
            width: 400px;
        }

        td {
            border: 3px solid #777;
            padding: 5px;
            background-color: #eee;
            vertical-align: middle;
        }

        .col-left {
            width: 80px;
            text-align: center;
            font-weight: bold;
        }

        .col-right {
            text-align: left;
            font-size: 18px;
        }

        textarea {
            width: 95%;
            height: 50px;
            resize: vertical;
        }

        button {
            background-color: #e0e0e0;
            border: 1px solid #777;
            padding: 2px 10px;
            font-weight: bold;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 1px 1px 1px #aaa;
        }

        .error {
            color: #d9534f;
            font-size: 0.9em;
            display: block;
            margin-top: 5px;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>

    <h1>Testuen itzultzailea</h1>

    <div class="message">
        <?php if ($message): ?>
            <span class="error"><?= $message ?></span><br>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <?php if ($error): ?>
                <span class="error"><?= $error ?></span><br>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <form action="index.php" method="POST">
        <table>
            <tr>
                <td class="col-left">Testua</td>
                <td class="col-right">
                    <textarea name="testua"><?= htmlspecialchars($data['testua']) ?></textarea>
                </td>
            </tr>

            <?php foreach ($hizkuntzak as $key => $label): ?>
                <tr>
                    <td class="col-left">
                        <input type="radio" name="hizkuntza" value="<?= $key ?>" <?= ($data['hizkuntza'] === $key) ? 'checked' : '' ?>>
                    </td>
                    <td class="col-right"><?= $label ?></td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <td class="empty"></td>
                <td class="col-right">
                    <button type="submit">Itzuli</button>
                </td>
            </tr>
        </table>
    </form>

    <div class="message">
        <?php if ($translation): ?>
            <p><?= $translation ?></p>
        <?php endif; ?>
    </div>

</body>

</html>