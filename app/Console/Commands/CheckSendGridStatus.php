<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmailProcessController;
use Illuminate\Console\Command;

class CheckSendGridStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendgrid:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check sendGrid status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        (new EmailProcessController())->checkSendGridTransferStatus();
        return 0;
    }
}
