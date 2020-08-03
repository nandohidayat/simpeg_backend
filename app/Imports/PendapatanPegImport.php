<?php
namespace App\Imports;

// use App\HasilImport\M_hasil_import_gaji;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
Use Maatwebsite\Excel\Concerns\WithStartRow;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class PendapatanPegImport implements ToCollection, WithStartRow, WithChunkReading, ShouldQueue
{
    public function startRow():int {
        return 1;
    }

    public function collection(Collection $rows)
    {
        return $rows;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
    
}

?>