@extends('layouts.authenticated', ['showBackButton' => true])

@section('title', 'Riwayat Penjualan')

@push('appbar_actions')
    <button class="btn btn-link text-white text-decoration-none px-2" id="btnClearFilter" style="display: none;" title="Hapus Filter">
        <i class="bi bi-funnel-fill text-warning"></i>
    </button>
    <button class="btn btn-link text-white text-decoration-none px-2" id="btnFilterDate" title="Filter Tanggal">
        <i class="bi bi-calendar-range"></i>
    </button>
@endpush

@section('content')
<div class="container-fluid py-3" style="padding-bottom: 88px;">
    
    <!-- Active Filter Indicator -->
    <div id="filterIndicator" class="alert alert-primary py-2 mb-3 d-flex justify-content-between align-items-center" style="display: none !important;">
        <span class="fw-bold fs-7 text-primary" id="filterText"></span>
        <button type="button" class="btn btn-sm btn-link text-primary p-0" id="btnResetFilterIndicator">Reset</button>
    </div>

    <!-- Hidden inputs for dates -->
    <input type="hidden" id="startDate">
    <input type="hidden" id="endDate">
    
    <div class="mb-3">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="searchSale" placeholder="Cari berdasarkan nama barang...">
        </div>
    </div>
    
    <div id="saleList">
        <div class="text-center py-5 text-secondary">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-3">Memuat riwayat penjualan...</div>
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <a href="/sales/create" class="btn btn-primary rounded-circle shadow position-fixed" style="bottom: 24px; right: 24px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; z-index: 1000;">
        <i class="bi bi-plus-lg fs-4"></i>
    </a>
</div>

<!-- Modal Date Range -->
<div class="modal fade" id="dateFilterModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filter Tanggal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" class="form-control" id="modalStartDate">
        </div>
        <div class="mb-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" class="form-control" id="modalEndDate">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnApplyFilter">Terapkan</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    let allSales = [];

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    };

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    };

    const fetchSales = () => {
        let url = '/api/sales';
        const start = $('#startDate').val();
        const end = $('#endDate').val();
        
        if (start && end) {
            url += `?start_date=${start}&end_date=${end}`;
            $('#filterText').text(`Filter: ${formatDate(start)} - ${formatDate(end)}`);
            $('#filterIndicator').attr('style', 'display: flex !important;');
            $('#btnClearFilter').show();
        } else {
            $('#filterIndicator').attr('style', 'display: none !important;');
            $('#btnClearFilter').hide();
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
                allSales = res.data || res;
                renderSales();
            },
            error: function() {
                $('#saleList').html('<div class="text-center py-5 text-danger">Gagal memuat penjualan.</div>');
                showError('Gagal memuat riwayat penjualan dari server.');
            }
        });
    };

    const renderSales = () => {
        const query = $('#searchSale').val().toLowerCase();
        
        const filtered = allSales.filter(sale => {
            if (!query) return true;
            // Search inside items
            return sale.items && sale.items.some(item => {
                const name = item.product_name ? item.product_name.toLowerCase() : '';
                return name.includes(query);
            });
        });

        if(filtered.length === 0) {
            $('#saleList').html(`
                <div class="d-flex flex-column align-items-center justify-content-center text-secondary py-5 mt-5">
                    <i class="bi bi-receipt" style="font-size: 64px; color: #ccc;"></i>
                    <h5 class="mt-3">Belum ada penjualan.</h5>
                </div>
            `);
            return;
        }

        let html = '';
        filtered.forEach(sale => {
            let itemsHtml = '';
            if (sale.items) {
                sale.items.forEach(item => {
                    itemsHtml += `
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-dark" style="font-size: 14px;">${item.product_name} (x${item.quantity})</span>
                            <span class="fw-medium text-dark" style="font-size: 14px;">${formatRupiah(item.subtotal_revenue)}</span>
                        </div>
                    `;
                });
            }

            html += `
                <div class="card mb-3 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-1" style="font-size: 15px;">${sale.sale_code}</h6>
                                <small class="text-muted" style="font-size: 12px;">${formatDate(sale.sale_date)}</small>
                            </div>
                        </div>
                        
                        <hr class="my-3 text-muted opacity-25">
                        
                        <div class="mb-3">
                            ${itemsHtml}
                        </div>
                        
                        <hr class="my-3 text-muted opacity-25">
                        
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="d-flex align-items-baseline">
                                <span class="fw-bold fs-6 me-2">Total:</span>
                                <span class="fw-bold fs-5 text-primary">${formatRupiah(sale.total_revenue)}</span>
                            </div>
                            <div>
                                <a href="/sales/${sale.id}/edit" class="btn btn-sm btn-link text-primary p-1 me-2" title="Edit Penjualan">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                                <button class="btn btn-sm btn-link text-danger p-1 btn-delete" data-id="${sale.id}" data-code="${sale.sale_code}" title="Hapus Penjualan">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#saleList').html(html);
    };

    $(document).ready(function() {
        // Initial fetch
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('startDate') && urlParams.has('endDate')) {
            $('#startDate').val(urlParams.get('startDate'));
            $('#endDate').val(urlParams.get('endDate'));
        }
        
        if (urlParams.has('searchQuery')) {
            $('#searchSale').val(urlParams.get('searchQuery'));
        }
        
        fetchSales();

        let searchTimeout;
        $('#searchSale').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(renderSales, 300);
        });

        $('#btnFilterDate').click(function() {
            const today = new Date().toISOString().split('T')[0];
            $('#modalStartDate').val($('#startDate').val() || today);
            $('#modalEndDate').val($('#endDate').val() || today);
            
            const modal = new bootstrap.Modal(document.getElementById('dateFilterModal'));
            modal.show();
        });

        $('#btnApplyFilter').click(function() {
            const s = $('#modalStartDate').val();
            const e = $('#modalEndDate').val();
            if(s && e) {
                $('#startDate').val(s);
                $('#endDate').val(e);
                bootstrap.Modal.getInstance(document.getElementById('dateFilterModal')).hide();
                fetchSales();
            } else {
                showError('Pilih tanggal awal dan akhir.');
            }
        });

        const resetFilter = () => {
            $('#startDate').val('');
            $('#endDate').val('');
            window.history.replaceState({}, document.title, window.location.pathname);
            fetchSales();
        };

        $('#btnClearFilter, #btnResetFilterIndicator').click(resetFilter);

        // Delete logic
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            
            Swal.fire({
                title: 'Hapus Transaksi',
                text: `Apakah Anda yakin ingin menghapus transaksi ${code}? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F44336',
                cancelButtonColor: '#757575',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        url: `/api/sales/${id}`,
                        type: 'DELETE',
                        success: function() {
                            hideLoading();
                            showSuccess(`Transaksi ${code} berhasil dihapus`);
                            fetchSales();
                        },
                        error: function(xhr) {
                            hideLoading();
                            let msg = 'Gagal menghapus transaksi';
                            if(xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showError(msg);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
