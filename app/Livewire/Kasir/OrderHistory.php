<?php

namespace App\Livewire\Kasir;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class OrderHistory extends Component
{
    #[Url]
    public string $search = '';

    public string $historyFrom = '';
    public string $historyTo = '';

    #[Computed]
    public function historyOrders()
    {
        $query = Order::query()
            ->with(['table:id,number', 'orderItems.product:id,name'])
            ->whereNotNull('paid_at')
            ->latest('paid_at')->latest('id');

        $this->applySearch($query);

        if ($this->historyFrom !== '') {
            $query->whereDate('paid_at', '>=', $this->historyFrom);
        }

        if ($this->historyTo !== '') {
            $query->whereDate('paid_at', '<=', $this->historyTo);
        }

        return $query->limit(100)->get();
    }

    private function applySearch(Builder $query): void
    {
        $search = trim($this->search);
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder->where('customer_name', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhereHas('table', fn(Builder $table): Builder => $table->where('number', 'like', "%{$search}%"));
        });
    }

    public function render()
    {
        return view('livewire.kasir.order-history');
    }
}
