<?php

function sendTelegramMessage($message) {
    $botToken = "8501355564:AAHwLckLcyqtuEe5y-H_kwuGXEs027O_JmU";
    $chatId = "7351125111";

    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    file_get_contents($url, false, $context);
}
