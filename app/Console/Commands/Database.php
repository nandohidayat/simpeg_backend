<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Database extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi Lur';

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
        $date = Carbon::create(2020, 6);
        $first = $date->copy()->firstOfMonth();
        $last = $first->copy()->addMonth();

        $data = DB::connection('mysql2')
            ->table('log_presensi')
            ->whereBetween('DateTime', [$first, $last])
            ->get();

        foreach ($data as $d) {
            DB::table('presensis')
                ->updateOrInsert(
                    ['pin' => $d->PIN, 'datetime' => $d->DateTime],
                    ['verified' => $d->Verified, 'status' => $d->Status, 'source' => $d->Source]
                );
        }

        error_log('SUCCESS BRO');
    }
}
