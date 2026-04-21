<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('All good.');
})->purpose('Display an inspiring quote');
