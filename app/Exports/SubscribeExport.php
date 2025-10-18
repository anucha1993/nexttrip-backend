<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class SubscribeExport implements FromView
{
    use Exportable;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function view(): View
    {
        return view('backend.pages.report.subscribe', [
            'data' => $this->data,
        ]);
    }
}
