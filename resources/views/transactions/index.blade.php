@extends('layouts.app')

@section('page_title', 'Daftar Transaksi')

@section('content')
<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="margin: 0; color: var(--text-color);">Riwayat Transaksi</h2>
            <p style="margin: 5px 0 0 0; color: var(--text-muted);">Pantau pesanan dan persetujuan transfer manual.</p>
        </div>
        <div>
            <form method="GET" action="{{ route('transactions.index') }}" style="display: flex; gap: 8px; margin: 0;">
                <select name="month" onchange="this.form.submit()" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 8px; height: 36px; padding: 0 12px; font-size: 13px; color: var(--text-color); outline: none; cursor: pointer;">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }} style="color: black;">{{ $name }}</option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 8px; height: 36px; padding: 0 12px; font-size: 13px; color: var(--text-color); outline: none; cursor: pointer;">
                    <option value="all" {{ $selectedYear == 'all' ? 'selected' : '' }} style="color: black;">Semua Tahun</option>
                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }} style="color: black;">{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(46, 204, 113, 0.3);">
            <i class="fa-solid fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(231, 76, 60, 0.3);">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i> {{ session('error') }}
        </div>
    @endif

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>ID Transaksi</th>
                    <th>Nomor WA</th>
                    <th>Paket</th>
                    <th>Metode</th>
                    <th>Total</th>
                    <th>Bukti TF</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr>
                    <td>{{ $trx->created_at->format('d M Y, H:i') }}</td>
                    <td><span style="font-family: monospace; color: var(--primary-color);">{{ $trx->trx_id }}</span></td>
                    <td>{{ $trx->customer->phone ?? 'Anonim' }}</td>
                    <td>{{ $trx->package->name ?? 'Paket Terhapus' }}</td>
                    <td>
                        <span style="text-transform: uppercase; font-weight: bold; color: var(--text-color);">
                            {{ $trx->payment_method }}
                        </span>
                    </td>
                    <td>Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                    <td>
                        @if($trx->proof_image)
                            <a href="#" onclick="showProof('{{ asset($trx->proof_image) }}')" style="color: var(--primary-color); text-decoration: none;">
                                <i class="fa-solid fa-image"></i> Lihat
                            </a>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td>
                        @if($trx->status == 'unpaid')
                            <span style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">UNPAID</span>
                        @elseif($trx->status == 'pending')
                            <span style="background: rgba(241, 196, 15, 0.2); color: #f1c40f; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">MENUNGGU ACC</span>
                        @elseif($trx->status == 'paid')
                            <span style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">PAID (LUNAS)</span>
                        @endif
                    </td>
                    <td>
                        @if($trx->status == 'pending' && in_array($trx->payment_method, ['dana', 'bca']))
                            <form action="{{ route('transactions.approve', $trx->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin dana sudah masuk dan ingin mencetak voucher untuk nomor ini?')" style="padding: 6px 12px; font-size: 0.8rem;">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </button>
                            </form>
                        @elseif($trx->status == 'paid')
                            <button class="btn" style="background: rgba(255,255,255,0.1); color: var(--text-muted); padding: 6px 12px; font-size: 0.8rem; cursor: not-allowed;">
                                Selesai
                            </button>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">Belum ada transaksi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal untuk melihat bukti transfer -->
<div id="proofModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: var(--card-bg); padding: 20px; border-radius: 16px; max-width: 90%; max-height: 90%; overflow: auto; position: relative; border: 1px solid var(--border-color); text-align: center;">
        <button onclick="document.getElementById('proofModal').style.display='none'" style="position: absolute; top: 10px; right: 10px; background: none; border: none; color: var(--text-color); font-size: 1.5rem; cursor: pointer;">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3 style="margin-top: 0; margin-bottom: 15px; color: var(--text-color); text-align: left;">Bukti Transfer</h3>
        <a id="proofLink" href="" target="_blank" title="Klik untuk Zoom / Buka Ukuran Asli">
            <img id="proofImage" src="" style="max-width: 100%; max-height: 70vh; border-radius: 8px; cursor: zoom-in; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        </a>
        <div style="margin-top: 12px;">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;"><i class="fa-solid fa-magnifying-glass-plus"></i> Klik gambar untuk Zoom (Melihat ukuran penuh)</p>
        </div>
    </div>
</div>

<script>
function showProof(imageUrl) {
    document.getElementById('proofImage').src = imageUrl;
    document.getElementById('proofLink').href = imageUrl;
    document.getElementById('proofModal').style.display = 'flex';
}
</script>
@endsection
