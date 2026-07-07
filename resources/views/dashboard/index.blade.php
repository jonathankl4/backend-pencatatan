@extends('layouts.authenticated')

@section('title', 'Dashboard')

@push('appbar_actions')
    <button class="btn btn-link text-white text-decoration-none px-2" id="btnLogoutHeader">
        <i class="bi bi-box-arrow-right"></i>
    </button>
@endpush

@section('content')
<div class="container-fluid py-4" style="padding-bottom: 88px;">
    
    <!-- Header / Date Filter -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold text-truncate" id="dateRangeText">Ringkasan Hari Ini</h5>
        <div>
            <button class="btn btn-sm btn-link text-secondary" id="btnClearFilter" style="display: none;" title="Hapus Filter">
                <i class="bi bi-funnel-fill text-danger"></i>
            </button>
            <button class="btn btn-sm btn-light border" id="btnFilterDate" title="Filter Tanggal">
                <i class="bi bi-calendar-range text-primary"></i>
            </button>
        </div>
    </div>
    
    <!-- Hidden inputs for dates -->
    <input type="hidden" id="startDate">
    <input type="hidden" id="endDate">
    
    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Pendapatan -->
        <div class="col-6">
            <div class="card h-100 mb-0 shadow-sm border-0" style="border-radius: 12px; cursor: pointer;" onclick="window.location.href='/sales'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-graph-up-arrow text-primary-custom" style="font-size: 20px;"></i>
                        <span class="ms-2 text-secondary fw-medium" style="font-size: 13px;">Pendapatan</span>
                    </div>
                    <div class="fw-bold text-primary-custom fs-5 text-truncate" id="valRevenue">Rp 0</div>
                </div>
            </div>
        </div>
        
        <!-- Pengeluaran -->
        <div class="col-6">
            <div class="card h-100 mb-0 shadow-sm border-0" style="border-radius: 12px; cursor: pointer;" onclick="window.location.href='/expenses'">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-graph-down-arrow text-danger" style="font-size: 20px;"></i>
                        <span class="ms-2 text-secondary fw-medium" style="font-size: 13px;">Pengeluaran</span>
                    </div>
                    <div class="fw-bold text-danger fs-5 text-truncate" id="valExpenses">Rp 0</div>
                </div>
            </div>
        </div>
        
        <!-- Laba Kotor -->
        <div class="col-6">
            <div class="card h-100 mb-0 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-wallet2" style="color: var(--warning); font-size: 20px;"></i>
                        <span class="ms-2 text-secondary fw-medium" style="font-size: 13px;">Laba Kotor</span>
                    </div>
                    <div class="fw-bold fs-5 text-truncate" style="color: var(--warning);" id="valGrossProfit">Rp 0</div>
                </div>
            </div>
        </div>
        
        <!-- Laba Bersih -->
        <div class="col-6">
            <div class="card h-100 mb-0 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-cash-coin text-success" style="font-size: 20px;"></i>
                        <span class="ms-2 text-secondary fw-medium" style="font-size: 13px;">Laba Bersih</span>
                    </div>
                    <div class="fw-bold text-success fs-5 text-truncate" id="valNetProfit">Rp 0</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rekap Barang Terjual -->
    <h6 class="fw-bold fs-5 mb-2">Rekap Barang Terjual</h6>
    
    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="searchRecap" placeholder="Cari barang terjual...">
        </div>
    </div>
    
    <div id="recapList">
        <div class="text-center py-4 text-secondary">
            <div class="spinner-border spinner-border-sm" role="status"></div> Memuat data...
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <a href="/sales/create" class="btn btn-primary rounded-circle shadow position-fixed" style="bottom: 24px; right: 24px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; z-index: 1000;">
        <i class="bi bi-plus-lg fs-4"></i>
    </a>
</div>

<!-- Modal Date Range (Using SweetAlert or standard Bootstrap modal) -->
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
    let itemRecapData = [];

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

    const fetchDashboard = () => {
        let url = '/api/dashboard';
        const start = $('#startDate').val();
        const end = $('#endDate').val();
        
        if (start && end) {
            url += `?start_date=${start}&end_date=${end}`;
            $('#dateRangeText').text(`${formatDate(start)} - ${formatDate(end)}`);
            $('#btnClearFilter').show();
        } else {
            $('#dateRangeText').text('Ringkasan Hari Ini');
            $('#btnClearFilter').hide();
        }

        $('#recapList').html('<div class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm" role="status"></div> Memuat data...</div>');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
                const data = res.data || res;
                // Parse summary
                if(data.summary) {
                    $('#valRevenue').text(formatRupiah(data.summary.total_revenue));
                    $('#valExpenses').text(formatRupiah(data.summary.total_expenses));
                    $('#valGrossProfit').text(formatRupiah(data.summary.total_gross_profit));
                    $('#valNetProfit').text(formatRupiah(data.summary.net_profit));
                }
                
                // Store and render recap
                itemRecapData = data.item_recap || [];
                renderRecap();
            },
            error: function(xhr) {
                $('#recapList').html('<div class="text-center py-4 text-danger">Gagal memuat data.</div>');
                showError('Gagal memuat data dashboard.');
            }
        });
    };

    const renderRecap = () => {
        const query = $('#searchRecap').val().toLowerCase();
        const filtered = itemRecapData.filter(item => item.product_name.toLowerCase().includes(query));
        
        // Sort alphabetically
        filtered.sort((a, b) => a.product_name.localeCompare(b.product_name));

        if(filtered.length === 0) {
            $('#recapList').html('<div class="text-center py-4 text-secondary">Belum ada barang terjual pada periode ini.</div>');
            return;
        }

        let html = '<div class="list-group list-group-flush border-top mt-2">';
        filtered.forEach(item => {
            html += `
                <a href="/sales?searchQuery=${encodeURIComponent(item.product_name)}" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-bottom">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-bar-chart-line text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-semibold">${item.product_name}</h6>
                        <small class="text-muted">${item.total_quantity} pcs</small>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-primary">${formatRupiah(item.total_revenue)}</span>
                    </div>
                </a>
            `;
        });
        html += '</div>';
        
        $('#recapList').html(html);
    };

    $(document).ready(function() {
        // Appbar logout
        $('#btnLogoutHeader').click(function() {
             $('#btnLogout').click(); // trigger the drawer logout
        });

        // Initialize dashboard
        fetchDashboard();

        // Search recap
        $('#searchRecap').on('input', renderRecap);

        // Date Filter interactions
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
                fetchDashboard();
            } else {
                showError('Pilih tanggal awal dan akhir.');
            }
        });

        $('#btnClearFilter').click(function() {
            $('#startDate').val('');
            $('#endDate').val('');
            fetchDashboard();
        });
    });
</script>
@endpush
