<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Comandă nouă') }}
        </h2>
    </x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="font-medium text-red-600">
                                {{ __('Ups! Au apărut următoarele erori:') }}
                            </div>

                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                <form method="POST" action="{{ route('orders.store') }}" id="order-form" class="space-y-6">
                    @csrf
                    
                        <!-- Selectare furnizor -->
                    <div>
                            <x-input-label for="supplier_id" :value="__('Furnizor')" />
                            <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Selectează furnizor</option>
                            @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->company_name }}
                                    </option>
                            @endforeach
                        </select>
                            <x-input-error class="mt-2" :messages="$errors->get('supplier_id')" />
                    </div>

                    <!-- Container pentru produse - inițial ascuns -->
                    <div id="products-section" class="hidden space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Selectare produse') }}</h3>
                        
                        <!-- Caută produse -->
                        <div class="flex space-x-2">
                            <input type="text" id="search-products" placeholder="Caută produse..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                        
                        <!-- Lista de produse disponibile -->
                        <div id="products-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Produsele vor fi încărcate dinamic -->
                            <div class="text-center py-8 text-gray-500">
                                Selectați un furnizor pentru a vedea produsele
                                    </div>
                                </div>
                            </div>

                    <!-- Coș cumpărături - va fi afișat după ce utilizatorul adaugă produse -->
                    <div id="cart-section" class="hidden space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Coș de cumpărături') }}</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Cod produs') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Descriere') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Cantitate') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Preț unitar') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Total') }}
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Acțiuni') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items" class="bg-white divide-y divide-gray-200">
                                    <!-- Produsele adăugate vor fi afișate aici -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                            {{ __('Total comandă:') }}
                                        </td>
                                        <td id="cart-total" class="px-4 py-3 text-sm font-medium text-gray-900">
                                            0.00 RON
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                        <!-- Note -->
                        <div>
                            <x-input-label for="notes" :value="__('Note')" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('notes') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div class="flex items-center gap-4">
                        <x-primary-button id="submit-order" disabled>{{ __('Plasează comanda') }}</x-primary-button>
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Anulează') }}
                            </a>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elemente DOM
    const supplierSelect = document.getElementById('supplier_id');
    const productsSection = document.getElementById('products-section');
    const productsList = document.getElementById('products-list');
    const cartSection = document.getElementById('cart-section');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const searchInput = document.getElementById('search-products');
    const submitButton = document.getElementById('submit-order');
    const orderForm = document.getElementById('order-form');
    
    // Variabile pentru starea aplicației
    let products = []; // Lista completă de produse
    let cart = []; // Produsele din coș
    
    // Eveniment la schimbarea furnizorului
    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        console.log('Furnizor selectat:', supplierId);
        
        if (supplierId) {
            // Arată secțiunea de produse
            productsSection.classList.remove('hidden');
            
            // Încarcă produsele furnizorului
            fetchSupplierProducts(supplierId);
        } else {
            // Ascunde secțiunile dacă nu este selectat un furnizor
            productsSection.classList.add('hidden');
            cartSection.classList.add('hidden');
            productsList.innerHTML = '<div class="text-center py-8 text-gray-500">Selectați un furnizor pentru a vedea produsele</div>';
        }
    });
    
    // Eveniment pentru căutarea produselor
    searchInput.addEventListener('input', function() {
        filterProducts(this.value);
    });
    
    // Funcție pentru a încărca produsele furnizorului
    function fetchSupplierProducts(supplierId) {
        // Afișăm mesaj de încărcare
        productsList.innerHTML = '<div class="text-center py-8">Se încarcă produsele...</div>';
        
        console.log('Se încarcă produsele pentru furnizorul:', supplierId);
        
        // Folosim ruta de test pentru produse
        fetch(`/test-products/${supplierId}`)
            .then(response => {
                console.log('Status răspuns API:', response.status);
                if (!response.ok) {
                    throw new Error('Eroare la încărcarea produselor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Produse primite:', data.count);
                products = data.products;
                displayProducts(products);
            })
            .catch(error => {
                console.error('Eroare la încărcarea produselor:', error);
                productsList.innerHTML = `<div class="text-center py-8 text-red-500">Eroare la încărcarea produselor: ${error.message}. Încercați din nou.</div>`;
            });
    }
    
    // Funcție pentru afișarea produselor
    function displayProducts(productsToDisplay) {
        if (productsToDisplay.length === 0) {
            productsList.innerHTML = '<div class="text-center py-8 text-gray-500">Nu s-au găsit produse</div>';
            return;
        }
        
        let html = '';
        productsToDisplay.forEach(product => {
            html += `
                <div class="border rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div class="font-medium">${product.cod_produs}</div>
                    <div class="text-sm text-gray-700 mb-2">${product.description}</div>
                    <div class="flex justify-between items-center">
                        <div class="text-gray-900 font-medium">${parseFloat(product.price).toFixed(2)} RON</div>
                        <div class="text-sm text-gray-600">Stoc: ${product.stock}</div>
                    </div>
                    <div class="mt-3 flex space-x-2">
                        <input type="number" min="1" max="${product.stock}" value="1" 
                            class="product-quantity w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            data-product-id="${product.id}">
                        <button type="button" class="add-to-cart flex-grow px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600"
                            data-product-id="${product.id}">
                            Adaugă în coș
                        </button>
                    </div>
                </div>
            `;
        });
        
        productsList.innerHTML = html;
        
        // Adăugăm event listeners pentru butoanele "Adaugă în coș"
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const quantityInput = document.querySelector(`.product-quantity[data-product-id="${productId}"]`);
                const quantity = parseInt(quantityInput.value);
                
                if (quantity > 0) {
                    addToCart(productId, quantity);
                }
            });
        });
    }
    
    // Funcție pentru filtrarea produselor
    function filterProducts(query) {
        if (!query) {
            displayProducts(products);
            return;
        }
        
        query = query.toLowerCase();
        const filtered = products.filter(product => 
            product.cod_produs.toLowerCase().includes(query) || 
            product.description.toLowerCase().includes(query)
        );
        
        displayProducts(filtered);
    }
    
    // Funcție pentru adăugarea unui produs în coș
    function addToCart(productId, quantity) {
        const product = products.find(p => p.id == productId);
        
        if (!product) return;
        
        // Verificăm dacă produsul este deja în coș
        const existingItemIndex = cart.findIndex(item => item.product.id == productId);
        
        if (existingItemIndex >= 0) {
            // Actualizăm cantitatea dacă produsul există deja
            const newQuantity = cart[existingItemIndex].quantity + quantity;
            
            if (newQuantity > product.stock) {
                alert(`Nu puteți adăuga mai mult decât stocul disponibil (${product.stock}).`);
                return;
            }
            
            cart[existingItemIndex].quantity = newQuantity;
        } else {
            // Adăugăm un produs nou în coș
            if (quantity > product.stock) {
                alert(`Nu puteți adăuga mai mult decât stocul disponibil (${product.stock}).`);
                return;
            }
            
            cart.push({
                product: product,
                quantity: quantity
            });
        }
        
        // Actualizăm afișarea coșului
        updateCartDisplay();
        
        // Arătăm secțiunea de coș
        cartSection.classList.remove('hidden');
        
        // Activăm butonul de plasare comandă
        submitButton.disabled = false;
    }
    
    // Funcție pentru actualizarea afișării coșului
    function updateCartDisplay() {
        if (cart.length === 0) {
            cartItems.innerHTML = '<tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">Coșul este gol</td></tr>';
            cartSection.classList.add('hidden');
            submitButton.disabled = true;
            return;
        }
        
        let html = '';
        let total = 0;
        
        cart.forEach((item, index) => {
            const subtotal = item.quantity * item.product.price;
            total += subtotal;
            
            html += `
                <tr>
                    <td class="px-4 py-3">
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product.id}">
                        ${item.product.cod_produs}
                    </td>
                    <td class="px-4 py-3">${item.product.description}</td>
                    <td class="px-4 py-3">
                        <input type="number" name="items[${index}][quantity]" value="${item.quantity}" min="1" max="${item.product.stock}"
                            class="cart-quantity w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            data-index="${index}">
                    </td>
                    <td class="px-4 py-3">${parseFloat(item.product.price).toFixed(2)} RON</td>
                    <td class="px-4 py-3 subtotal">${parseFloat(subtotal).toFixed(2)} RON</td>
                    <td class="px-4 py-3 text-right">
                        <button type="button" class="remove-from-cart text-red-600 hover:text-red-900" data-index="${index}">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        cartItems.innerHTML = html;
        cartTotal.textContent = parseFloat(total).toFixed(2) + ' RON';
        
        // Adăugăm event listeners pentru cantitatea din coș
        document.querySelectorAll('.cart-quantity').forEach(input => {
            input.addEventListener('change', function() {
                const index = this.getAttribute('data-index');
                const quantity = parseInt(this.value);
                
                if (quantity > 0 && quantity <= cart[index].product.stock) {
                    cart[index].quantity = quantity;
                    updateCartDisplay();
                } else {
                    // Resetăm la valoarea anterioară dacă cantitatea este invalidă
                    this.value = cart[index].quantity;
                    alert(`Cantitatea trebuie să fie între 1 și ${cart[index].product.stock}.`);
                }
            });
        });
        
        // Adăugăm event listeners pentru ștergerea din coș
        document.querySelectorAll('.remove-from-cart').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                cart.splice(index, 1);
                updateCartDisplay();
            });
        });
    }
    
    // Inițializăm prima dată paginile
    if (supplierSelect.value) {
        productsSection.classList.remove('hidden');
        fetchSupplierProducts(supplierSelect.value);
    }
});
</script>
@endpush
</x-app-layout> 