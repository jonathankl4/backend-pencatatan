@extends('layouts.authenticated')

@section('title', 'Daftar Produk')

@section('content')
<div class="container-fluid py-3" style="padding-bottom: 88px;">
    
    <div id="productList">
        <div class="text-center py-5 text-secondary">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-3">Memuat data produk...</div>
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <a href="/products/create" class="btn btn-primary rounded-circle shadow position-fixed" style="bottom: 24px; right: 24px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; z-index: 1000;">
        <i class="bi bi-plus-lg fs-4"></i>
    </a>
</div>
@endsection

@push('scripts')
<script>
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    };

    const fetchProducts = () => {
        $.ajax({
            url: '/api/products',
            type: 'GET',
            success: function(res) {
                const products = res.data || res; // depending on pagination wrapper
                
                if(!products || products.length === 0) {
                    $('#productList').html(`
                        <div class="d-flex flex-column align-items-center justify-content-center text-secondary py-5 mt-5">
                            <i class="bi bi-box-seam" style="font-size: 64px; color: #ccc;"></i>
                            <h5 class="mt-3">Belum ada produk.</h5>
                        </div>
                    `);
                    return;
                }

                let html = '<div class="list-group border-0 shadow-sm rounded-3">';
                products.forEach(product => {
                    html += `
                        <div class="list-group-item d-flex align-items-center py-3 border-0 border-bottom">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-box text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold">${product.name}</h6>
                                <small class="text-muted">Modal: ${formatRupiah(product.cost_price)}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark fs-6">${formatRupiah(product.sell_price)}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                $('#productList').html(html);
            },
            error: function() {
                $('#productList').html('<div class="text-center py-5 text-danger">Gagal memuat produk.</div>');
                showError('Gagal memuat data produk dari server.');
            }
        });
    };

    $(document).ready(function() {
        fetchProducts();
    });
</script>
@endpush
