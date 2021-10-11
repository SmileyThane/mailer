<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmailProcessController;
use Illuminate\Console\Command;

class ProcessEmailSending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start campaign ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        (new EmailProcessController())->process();
        return 0;
    }
}
