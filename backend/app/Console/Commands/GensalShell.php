<?php

namespace App\Console\Commands;
use App\Http\Components\KafkaComponent;

/**
 * @property KafkaComponent KafkaComponent
 * */

class GensalShell extends BaseShell
{
    protected $signature    = 'gensal { function } {args?*}';
    protected $description  = 'Run big job';
}
