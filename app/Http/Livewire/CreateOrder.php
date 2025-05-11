<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CreateOrder extends Component
{
    use WithPagination;

    public $orderType = 'order';
    public $selectedSupplier = null;
    public $shippingAddress;
    public $shippingCity;
    public $shippingPostalCode;
    public $shippingCountry = 'RO';
    public $requestedDeliveryDate;
    public $allowPartialDelivery = false;
    public $currency = 'RON';
    public $search = '';
    public $selectedManufacturer = '';
    public $sortBy = 'code';
    public $quantities = [];
    public $orderItems = [];
    public $total = 0;

    protected $rules = [
        'orderType' => 'required|in:order,quote',
        'selectedSupplier' => 'required|exists:organizations,id',
        'shippingAddress' => 'required|string|max:255',
        'shippingCity' => 'required|string|max:100',
        'shippingPostalCode' => 'required|string|max:20',
        'shippingCountry' => 'required|string|size:2',
        'requestedDeliveryDate' => 'nullable|date|after:today',
        'allowPartialDelivery' => 'boolean',
        'currency' => 'required|in:RON,EUR,USD'
    ];

    public function mount()
    {
        $this->orderItems = collect();
    }

    public function selectSupplier($supplierId)
    {
        $this->selectedSupplier = $supplierId;
        $this->resetPage();
    }

    public function addToOrder($productId)
    {
        $product = Product::findOrFail($productId);
        
        if (!isset($this->quantities[$productId]) || $this->quantities[$productId] < 1) {
            $this->addError('quantity', __('Cantitatea trebuie să fie mai mare de 0'));
            return;
        }

        $quantity = $this->quantities[$productId];

        $orderItem = [
            'product_id' => $product->id,
            'product' => $product,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'total_price' => $product->price * $quantity
        ];

        $this->orderItems = $this->orderItems->put($product->id, (object)$orderItem);
        $this->calculateTotal();
    }

    public function removeItem($productId)
    {
        $this->orderItems = $this->orderItems->forget($productId);
        $this->calculateTotal();
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity < 1) {
            $this->addError('quantity', __('Cantitatea trebuie să fie mai mare de 0'));
            return;
        }

        if ($this->orderItems->has($productId)) {
            $item = $this->orderItems->get($productId);
            $item->quantity = $quantity;
            $item->total_price = $item->unit_price * $quantity;
            $this->orderItems->put($productId, $item);
            $this->calculateTotal();
        }
    }

    private function calculateTotal()
    {
        $this->total = $this->orderItems->sum('total_price');
    }

    public function saveAsDraft()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();

            $order = Order::create([
                'customer_id' => auth()->id(),
                'supplier_id' => $this->selectedSupplier,
                'type' => $this->orderType,
                'status' => 'draft',
                'shipping_address' => $this->shippingAddress,
                'shipping_city' => $this->shippingCity,
                'shipping_postal_code' => $this->shippingPostalCode,
                'shipping_country' => $this->shippingCountry,
                'requested_delivery_date' => $this->requestedDeliveryDate,
                'allow_partial_delivery' => $this->allowPartialDelivery,
                'currency' => $this->currency,
                'total_amount' => $this->total
            ]);

            foreach ($this->orderItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price
                ]);
            }

            DB::commit();
            
            $this->emit('orderCreated', $order->id);
            session()->flash('success', __('Comanda a fost salvată ca ciornă.'));

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', __('A apărut o eroare la salvarea comenzii.'));
        }
    }

    public function submit()
    {
        $this->validate();

        if ($this->orderItems->isEmpty()) {
            session()->flash('error', __('Adăugați cel puțin un produs în comandă.'));
            return;
        }

        try {
            DB::beginTransaction();

            $order = Order::create([
                'customer_id' => auth()->id(),
                'supplier_id' => $this->selectedSupplier,
                'type' => $this->orderType,
                'status' => 'pending',
                'shipping_address' => $this->shippingAddress,
                'shipping_city' => $this->shippingCity,
                'shipping_postal_code' => $this->shippingPostalCode,
                'shipping_country' => $this->shippingCountry,
                'requested_delivery_date' => $this->requestedDeliveryDate,
                'allow_partial_delivery' => $this->allowPartialDelivery,
                'currency' => $this->currency,
                'total_amount' => $this->total
            ]);

            foreach ($this->orderItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price
                ]);
            }

            DB::commit();
            
            $this->emit('orderCreated', $order->id);
            session()->flash('success', __('Comanda a fost creată cu succes.'));

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', __('A apărut o eroare la crearea comenzii.'));
        }
    }

    public function render()
    {
        $suppliers = Organization::suppliers()
            ->whereHas('supplierConnections', function ($query) {
                $query->where('client_id', auth()->id())
                    ->where('status', 'active');
            })
            ->get();
        
        $products = collect();
        if ($this->selectedSupplier) {
            $query = Product::query()
                ->where('supplier_id', $this->selectedSupplier)
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('code', 'like', "%{$this->search}%")
                          ->orWhere('description', 'like', "%{$this->search}%")
                          ->orWhere('manufacturer', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->selectedManufacturer, function ($query) {
                    $query->where('manufacturer', $this->selectedManufacturer);
                });

            switch ($this->sortBy) {
                case 'price':
                    $query->orderBy('price');
                    break;
                case 'manufacturer':
                    $query->orderBy('manufacturer');
                    break;
                default:
                    $query->orderBy('code');
            }

            $products = $query->paginate(10);
        }

        $manufacturers = Product::where('supplier_id', $this->selectedSupplier)
            ->distinct()
            ->pluck('manufacturer');

        $countries = [
            'RO' => 'România',
            'HU' => 'Ungaria',
            'BG' => 'Bulgaria',
            'MD' => 'Moldova'
        ];

        return view('livewire.create-order', [
            'suppliers' => $suppliers,
            'products' => $products,
            'manufacturers' => $manufacturers,
            'countries' => $countries
        ]);
    }
} 