<?php

namespace App\Console\Commands;

use App\LogDepartemen;
use Illuminate\Console\Command;

class UpdateLogDepartemen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:logdepartemen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating log departemen';

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
        $data = LogDepartemen::all();

        foreach ($data as $d) {
            if ((int)$d->type === 0) {
                $log = LogDepartemen::find($d->id_log_departemen);
                $log->masuk = $d->tgl;
                $log->save();
            }

            if ((int)$d->type === 1) {
                $log = LogDepartemen::where('id_pegawai', $d->id_pegawai)
                    ->where('id_dept', $d->id_dept)
                    ->where('tgl', '<', $d->tgl)
                    ->orderBy('tgl', 'desc')
                    ->first();

                $log = LogDepartemen::find($log->id_log_departemen);
                $log->keluar = $d->tgl;
                $log->save();
            }
        }
    }
}
