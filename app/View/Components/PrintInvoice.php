<?php

namespace App\View\Components;

use App\Models\Transaction; // Pastikan Anda mengimpor model Transaction
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PrintInvoice extends Component
{
    public $transaction;

    /**
     * Create a new component instance.
     *
     * @param \App\Models\Transaction $transaction // Menerima objek Transaction langsung
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        // dd($transaction);
        $this->transaction = $transaction;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('components.print-invoice');
    }
}