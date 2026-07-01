<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mail = new \App\Mail\PipelineMail('Subject', "Dear Hasib,\n\nTest **bold**\n\nThanks");
echo $mail->render();
