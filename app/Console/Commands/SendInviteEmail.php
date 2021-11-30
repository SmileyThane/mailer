<?php

namespace App\Console\Commands;

use App\Mail\Invitation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;

class SendInviteEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite {--from=} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'basic invite';

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
        $user = User::query()->firstOrCreate(
            ['email' => $this->option('to')],
            [
                'name' => explode('@', $this->option('to'))[0],
                'password' => Hash::make('qwerty1234')
            ]);
        return MultiMail::to($user)->from($this->option('from'))->send(new Invitation($user));
    }
}
