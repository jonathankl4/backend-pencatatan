@extends('layouts.authenticated', ['showBackButton' => true, 'backUrl' => url()->previous() !== url()->current() ? url()->previous() : '/sales'])

@section('title', isset($id) ? 'Edit Penjualan' : 'Buat Penjualan')

@section('content')
<div class="d-flex flex-column" style="height: calc(100vh - 56px);"> <!-- Adjust height based on appbar -->
    <!-- Cart Items Area -->
    <div class="flex-grow-1 overflow-auto bg-light" id="cartContainer">
        <div id="emptyCart" class="d-flex h-100 align-items-center justify-content-center text-secondary">
            Belum ada catatan barang. Silakan tambah.
        </div>
        <div id="cartList" class="list-group list-group-flush" style="display: none;">
            <!-- Cart items will be rendered here -->
        </div>
    </div>
    
    <!-- Bottom Action Area -->
    <div class="bg-white shadow-lg p-3" style="box-shadow: 0 -5px 10px rgba(0,0,0,0.05) !important;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fs-5 fw-bold text-dark">Total:</span>
            <span class="fs-4 fw-bold text-primary" id="cartTotal">Rp 0</span>
        </div>
        <div class="row g-2">
            <div class="col-6">
                <button type="button" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center" id="btnAddItem" style="height: 50px;">
                    <i class="bi bi-cart-plus me-2"></i> Tambah Barang
                </button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-primary w-100 d-flex align-items-center justify-content-center" id="btnSaveSale" style="height: 50px;">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Dialog (Modal) -->
<div class="modal fade" id="addItemModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Catatan Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addItemForm">
            <!-- Autocomplete Container -->
            <div class="mb-3 position-relative">
                <label class="form-label text-secondary small">Nama Barang/Deskripsi</label>
                <input type="text" class="form-control" id="productName" placeholder="Cari atau ketik baru (misal: Kresek Tikus)" required autocomplete="off">
                <!-- Dropdown suggestions -->
                <ul class="list-group position-absolute w-100 shadow-sm" id="suggestionList" style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto;">
                </ul>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small">Harga Satuan (Rp)</label>
                <input type="text" class="form-control" id="productPrice" placeholder="Misal: 5000" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary small">Jumlah</label>
                <input type="number" class="form-control" id="productQty" placeholder="Misal: 1" value="1" min="1" required>
            </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary px-4 rounded-pill" id="btnConfirmAdd">Tambah</button>
      </div>
    </div>
  </div>
</div>

<input type="hidden" id="sale_id" value="{{ $id ?? '' }}">
@endsection

@push('scripts')
<script>
    let cart = [];
    let suggestions = [];
    let selectedProductId = null;
    let originalSaleDate = null;

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    };

    const updateCartUI = () => {
        if(cart.length === 0) {
            $('#emptyCart').removeClass('d-none').addClass('d-flex');
            $('#cartList').hide();
            $('#cartTotal').text('Rp 0');
            return;
        }

        $('#emptyCart').removeClass('d-flex').addClass('d-none');
        $('#cartList').show();

        let total = 0;
        let html = '';
        
        cart.forEach((item, index) => {
            const subtotal = item.sell_price * item.quantity;
            total += subtotal;
            
            html += `
                <div class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-medium">${item.product_name}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-secondary">${formatRupiah(item.sell_price)}</small>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-link text-secondary p-0 text-decoration-none fs-4 me-3 btn-qty" data-index="${index}" data-action="minus">
                                <i class="bi bi-dash-circle"></i>
                            </button>
                            <span class="fs-6 fw-bold" style="min-width: 20px; text-align: center;">${item.quantity}</span>
                            <button class="btn btn-link text-secondary p-0 text-decoration-none fs-4 ms-3 btn-qty" data-index="${index}" data-action="plus">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#cartList').html(html);
        $('#cartTotal').text(formatRupiah(total));
    };

    const fetchSuggestions = () => {
        $.ajax({
            url: '/api/sales/suggestions',
            type: 'GET',
            success: function(res) {
                suggestions = res.data || res;
            }
        });
    };

    const loadExistingSale = (id) => {
        showLoading();
        $.ajax({
            url: `/api/sales/${id}`,
            type: 'GET',
            success: function(res) {
                hideLoading();
                const sale = res.data || res;
                originalSaleDate = sale.sale_date;
                if(sale.items) {
                    cart = sale.items.map(item => ({
                        product_id: item.product_id,
                        product_name: item.product_name,
                        sell_price: item.sell_price,
                        quantity: item.quantity
                    }));
                }
                updateCartUI();
            },
            error: function() {
                hideLoading();
                showError('Gagal memuat data penjualan');
            }
        });
    };

    $(document).ready(function() {
        fetchSuggestions();

        const saleId = $('#sale_id').val();
        if(saleId) {
            loadExistingSale(saleId);
        }

        // Handle Add Item Button
        $('#btnAddItem').click(function() {
            $('#productName').val('');
            $('#productPrice').val('');
            $('#productQty').val('1');
            selectedProductId = null;
            
            const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
            modal.show();
        });

        // Handle AutoComplete logic
        $('#productName').on('input', function() {
            const query = $(this).val().toLowerCase();
            const $list = $('#suggestionList');
            
            // Check exact match for product ID logic
            const exactMatch = suggestions.find(s => s.product_name.toLowerCase() === query);
            selectedProductId = exactMatch ? exactMatch.product_id : null;

            if(!query) {
                $list.hide();
                return;
            }

            const matches = suggestions.filter(s => s.product_name.toLowerCase().includes(query));
            
            if(matches.length === 0) {
                $list.hide();
                return;
            }

            let html = '';
            matches.forEach(m => {
                html += `
                    <button type="button" class="list-group-item list-group-item-action suggestion-item" 
                        data-id="${m.product_id}" 
                        data-name="${m.product_name}" 
                        data-price="${m.sell_price}">
                        ${m.product_name}
                    </button>
                `;
            });
            $list.html(html).show();
        });

        // Hide suggestions when clicking outside
        $(document).click(function(e) {
            if(!$(e.target).closest('.position-relative').length) {
                $('#suggestionList').hide();
            }
        });

        // Handle suggestion click
        $(document).on('click', '.suggestion-item', function() {
            $('#productName').val($(this).data('name'));
            $('#productPrice').val($(this).data('price'));
            selectedProductId = $(this).data('id');
            $('#suggestionList').hide();
        });

        // Simple Math evaluation handler for price (like Flutter's MathEvaluator)
        $('#productPrice').on('blur', function() {
            let val = $(this).val().trim();
            if(!val) return;
            try {
                // Warning: eval is used for simple math string like "5000*2".
                // In production, a proper math parser should be used.
                // Sanitize input to only allow numbers and basic math operators
                if (/^[\d\.\+\-\*\/\(\)\s]+$/.test(val)) {
                    const result = eval(val);
                    if(!isNaN(result)) {
                        $(this).val(Math.round(result));
                    }
                }
            } catch(e) {}
        });

        // Confirm Add
        $('#btnConfirmAdd').click(function() {
            // Trigger blur to evaluate math if focused
            $('#productPrice').blur();
            
            if(!$('#addItemForm')[0].checkValidity()) {
                $('#addItemForm')[0].reportValidity();
                return;
            }

            const name = $('#productName').val().trim();
            const price = parseFloat($('#productPrice').val());
            const qty = parseInt($('#productQty').val());

            // Check if already in cart
            const existingIndex = cart.findIndex(i => i.product_name.toLowerCase() === name.toLowerCase());
            
            if(existingIndex >= 0) {
                cart[existingIndex].quantity += qty;
                if(selectedProductId) {
                    cart[existingIndex].product_id = selectedProductId;
                    cart[existingIndex].product_name = name; // Update casing
                }
            } else {
                cart.push({
                    product_id: selectedProductId,
                    product_name: name,
                    sell_price: price,
                    quantity: qty
                });
            }

            updateCartUI();
            bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
        });

        // Cart Item +/- actions
        $(document).on('click', '.btn-qty', function() {
            const index = $(this).data('index');
            const action = $(this).data('action');
            
            if(action === 'plus') {
                cart[index].quantity++;
            } else if (action === 'minus') {
                if(cart[index].quantity > 1) {
                    cart[index].quantity--;
                } else {
                    cart.splice(index, 1);
                }
            }
            updateCartUI();
        });

        // Save Sale
        $('#btnSaveSale').click(function() {
            if(cart.length === 0) {
                showError('Keranjang kosong!');
                return;
            }

            showLoading();

            const payload = {
                sale_date: saleId && originalSaleDate ? originalSaleDate : new Date().toISOString(),
                items: cart
            };

            const url = saleId ? `/api/sales/${saleId}` : '/api/sales';
            const method = saleId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: JSON.stringify(payload),
                contentType: 'application/json',
                success: function(res) {
                    hideLoading();
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan',
                        text: 'Penjualan berhasil disimpan',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/sales';
                    });
                },
                error: function(xhr) {
                    hideLoading();
                    let msg = 'Gagal menyimpan penjualan';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showError(msg);
                }
            });
        });
    });
</script>
@endpush
