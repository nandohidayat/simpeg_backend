<?php

namespace App\Console\Commands;

use App\Presensi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePresensi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:presensi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrating Presensi from SIMCOS to SMS GATEWAY';

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
     * @return mixed
     */
    public function handle()
    {
        $data = DB::connection('mysql2')->table('log_presensi')->limit(50000)->orderBy('DateTime', 'desc')->get();

        foreach ($data as $d) {
            Presensi::updateOrCreate(['datetime' => $d->DateTime, 'source' => $d->Source], ['pin' => $d->PIN, 'verified' => $d->Verified, 'status' => $d->Status]);
        }
    }
}
