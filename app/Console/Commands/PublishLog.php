<?php

namespace App\Console\Commands;

use App\LogDepartemen;
use App\SIMDataPegawai;
use App\SIMDepartment;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublishLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishing log dept';

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
        $data = SIMDataPegawai::join('f_department', 'f_department.id_dept', '=', DB::raw('ANY(f_data_pegawai.id_dept)'))->get();

        foreach ($data as $d) {
            $log = new LogDepartemen();
            $log->id_pegawai = $d->id_pegawai;
            $log->id_dept = $d->id_dept;
            $log->type = 0;
            $log->tgl = new Carbon('first day of January 2020', 'Asia/Jakarta');

            $log->save();
        }
    }
}
