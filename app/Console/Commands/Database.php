<?php

namespace App\Console\Commands;

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
        $data = DB::table('shifts')->get();

        foreach ($data as $d) {
            DB::table('shifts_new')
                ->insert([
                    'id_shift' => $d->id_shift,
                    'mulai' => $d->mulai,
                    'selesai' => $d->selesai,
                    'kode' => $d->kode,
                    'keterangan' => $d->keterangan,
                    'created_at' => $d->created_at,
                    'updated_at' => $d->updated_at
                ]);
        }

        error_log('SUCCESS BRO');
    }
}
