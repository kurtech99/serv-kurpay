@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-wrapper">

    @if(Auth::check() && Auth::user()->role === 'superadmin')
    <!-- Dashboard Superadmin -->
    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="margin: 0; font-size: 24px; font-weight: 800; color: var(--text-main);">Dashboard Super Admin</h2>
            <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 14px;">Ringkasan server CHR, total tenant, dan pendapatan sistem.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('superadmin.vpn.transactions') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; text-decoration: none;">
                <i class="fa-solid fa-file-invoice-dollar"></i> Verifikasi Transaksi
            </a>
            <a href="{{ route('superadmin.vpn.server') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; text-decoration: none;">
                <i class="fa-solid fa-server"></i> Kelola Server
            </a>
        </div>
    </div>

    <!-- Stat Grid Superadmin -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="glass-card" style="padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-server"></i>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Server VPN</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ $totalServers ?? 0 }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Akun Tenant</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ $totalAccounts ?? 0 }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Transaksi</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ $totalTransactions ?? 0 }}</div>
            </div>
        </div>
        <div class="glass-card" style="padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.12); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Revenue</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">Rp {{ number_format($revenue ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @else
    <!-- Dashboard Client / Tenant -->
    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">Halo, {{ Auth::user()->name }} 👋</h2>
            <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 13.5px;">Selamat datang di portal VPN Remote & Mikhmon Cloud <strong>KURTECH</strong>.</p>
        </div>
        <div class="header-action-btns" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('vpn.transactions.history') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; text-decoration: none; padding: 9px 16px; border-radius: 10px;">
                <i class="fa-solid fa-receipt"></i> Riwayat Transaksi
            </a>
            <a href="{{ route('vpn.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; text-decoration: none; padding: 9px 16px; border-radius: 10px;">
                <i class="fa-solid fa-plus"></i> Beli VPN Baru
            </a>
        </div>
    </div>

    @if(isset($pendingTrx) && $pendingTrx)
    <div class="glass-card" style="margin-bottom: 20px; border-left: 4px solid var(--warning); background: rgba(245, 158, 11, 0.08); padding: 14px 18px; border-radius: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.2); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <strong style="color: var(--text-main); font-size: 13.5px; display: block;">Ada Pesanan Menunggu Pembayaran ({{ $pendingTrx->trx_id }})</strong>
                    <span style="color: var(--text-muted); font-size: 12px;">Total: <strong style="color: var(--brand-primary);">Rp {{ number_format($pendingTrx->amount, 0, ',', '.') }}</strong></span>
                </div>
            </div>
            <a href="{{ route('vpn.payment', $pendingTrx->trx_id) }}" class="btn btn-primary" style="background: var(--warning); color: #000; font-weight: 700; font-size: 12px; padding: 7px 16px; text-decoration: none; border-radius: 8px;">
                <i class="fa-solid fa-credit-card" style="margin-right: 4px;"></i> Bayar Sekarang
            </a>
        </div>
    </div>
    @endif

    <!-- Stat Cards Client -->
    <div class="dashboard-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="glass-card stat-card-compact" style="padding: 18px 20px; border-radius: 16px; display: flex; align-items: center; gap: 14px;">
            <div class="stat-icon-wrap" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <div style="overflow: hidden;">
                <div class="stat-label" style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">VPN Aktif</div>
                <div class="stat-val" style="font-size: 20px; font-weight: 800; color: var(--text-main); white-space: nowrap;">{{ $activeAccounts ?? 0 }} <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">/ {{ $totalAccounts ?? 0 }}</span></div>
            </div>
        </div>

        <div class="glass-card stat-card-compact" style="padding: 18px 20px; border-radius: 16px; display: flex; align-items: center; gap: 14px;">
            <div class="stat-icon-wrap" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div style="overflow: hidden;">
                <div class="stat-label" style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Transaksi</div>
                <div class="stat-val" style="font-size: 20px; font-weight: 800; color: var(--text-main); white-space: nowrap;">{{ $totalTransactions ?? 0 }} <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Trx</span></div>
            </div>
        </div>

        <div class="glass-card stat-card-compact" style="padding: 18px 20px; border-radius: 16px; display: flex; align-items: center; gap: 14px;">
            <div class="stat-icon-wrap" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div style="overflow: hidden;">
                <div class="stat-label" style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Belanja</div>
                <div class="stat-val" style="font-size: 20px; font-weight: 800; color: var(--text-main); white-space: nowrap;">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Section Akun VPN Saya (Muncul Langsung di Dashboard) -->
    <div style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-shield-halved text-primary"></i> Akun VPN Remote Saya
            </h3>
            <a href="{{ route('vpn.index') }}" style="font-size: 12.5px; color: var(--brand-primary); font-weight: 700; text-decoration: none;">
                Kelola Port & Fitur Lengkap <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="vpn-account-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 16px;">
            @forelse($vpnAccounts ?? [] as $acc)
            @php
                $serverHost = $acc->server ? ($acc->server->domain ?? $acc->server->host) : 'id1.kurtech.my.id';
                $apiPort = $acc->ports->firstWhere('local_port', 8728);
                $winboxPort = $acc->ports->firstWhere('local_port', 8291);
                $mikhmonUrl = $acc->is_mikhmon ? $acc->launch_url : null;
            @endphp
            <div class="glass-card" style="padding: 20px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fa-solid fa-network-wired"></i>
                        </div>
                        <strong style="font-size: 15px; color: var(--text-main);">{{ $acc->username }}</strong>
                        @if($acc->is_genieacs)
                            <span style="font-size: 9.5px; background: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.35); padding: 1px 6px; border-radius: 6px; font-weight: 800;">GENIEACS + BILLING</span>
                        @elseif($acc->has_billing)
                            <span style="font-size: 9.5px; background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); padding: 1px 6px; border-radius: 6px; font-weight: 800;">BILLING</span>
                        @elseif($acc->is_mikhmon)
                            <span style="font-size: 9.5px; background: rgba(37, 99, 235, 0.12); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.3); padding: 1px 6px; border-radius: 6px; font-weight: 800;">MIKHMON</span>
                        @endif
                    </div>
                    <div>
                        @if($acc->status === 'active')
                            <span class="badge badge-success" style="font-size: 10.5px; font-weight: 700;">ACTIVE</span>
                        @elseif($acc->status === 'blocked')
                            <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 10.5px; font-weight: 800;">TERKUNCI</span>
                        @else
                            <span class="badge badge-warning" style="font-size: 10.5px; font-weight: 700;">{{ strtoupper($acc->status) }}</span>
                        @endif
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12.5px; margin-bottom: 14px;">
                    <div><span style="color: var(--text-muted);">Host Server:</span> <br><strong style="color: var(--text-main);">{{ $serverHost }}</strong></div>
                    <div><span style="color: var(--text-muted);">IP Remote:</span> <br><strong style="color: var(--brand-primary);">{{ $acc->remote_ip ?: 'Menunggu Dial' }}</strong></div>
                    <div><span style="color: var(--text-muted);">Masa Aktif:</span> <br><strong style="color: var(--text-main);">{{ $acc->expired_at ? $acc->expired_at->format('d M Y') : '-' }}</strong></div>
                    <div>
                        <span style="color: var(--text-muted);">Akses Winbox:</span> <br>
                        @if($winboxPort)
                            <strong style="color: var(--brand-primary);">:{{ $winboxPort->public_port }}</strong>
                        @else
                            <strong style="color: var(--text-muted);">-</strong>
                        @endif
                    </div>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ route('vpn.index') }}" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px; font-weight: 600; text-decoration: none; flex: 1; text-align: center;">
                        <i class="fa-solid fa-gear"></i> Detail & Port
                    </a>
                    @if($acc->is_mikhmon)
                    <a href="{{ $acc->launch_url }}" target="_blank" class="btn {{ $acc->has_billing ? 'btn-success' : 'btn-primary' }}" style="font-size: 12px; padding: 6px 12px; font-weight: 700; text-decoration: none; flex: 1; text-align: center; {{ $acc->has_billing ? 'background: #059669; color: #fff; border-color: #059669;' : '' }}">
                        <i class="fa-solid {{ $acc->has_billing ? 'fa-receipt' : 'fa-wifi' }}"></i> {{ $acc->has_billing ? 'Buka Billing' : 'Buka Mikhmon' }}
                    </a>
                    @endif
                </div>
                @if($acc->is_mikhmon)
                <div style="margin-top: 10px; padding: 6px 10px; background: rgba(37, 99, 235, 0.06); border: 1px dashed rgba(37, 99, 235, 0.25); border-radius: 8px; font-size: 11px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 4px;">
                    <span style="color: var(--text-muted);"><i class="fa-solid fa-key" style="color: #2563eb;"></i> Login Awal: <strong style="color: var(--text-main);">mikhmon</strong></span>
                    <span style="color: var(--text-muted);">Pass: <strong style="color: var(--text-main);">1234</strong></span>
                </div>
                @endif
            </div>
            @empty
            <div class="glass-card" style="grid-column: 1 / -1; text-align: center; padding: 40px 20px;">
                <i class="fa-solid fa-network-wired" style="font-size: 36px; color: var(--border-color); margin-bottom: 12px; display: block;"></i>
                <h4 style="color: var(--text-main); margin-bottom: 6px; font-weight: 700;">Belum Ada Akun VPN Remote</h4>
                <p style="color: var(--text-muted); font-size: 13.5px; margin-bottom: 16px;">Beli akun VPN Remote pertama Anda untuk meremote MikroTik dari mana saja.</p>
                <a href="{{ route('vpn.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; text-decoration: none;">
                    <i class="fa-solid fa-plus"></i> Beli VPN Sekarang
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Log Activity -->
    <div class="glass-card" style="border-radius: 16px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0;">
                <i class="fa-solid fa-list-check" style="color: var(--brand-primary); margin-right: 8px;"></i> Log Aktivitas Terakhir
            </h3>
            <a href="{{ route('vpn.transactions.history') }}" style="font-size: 12.5px; color: var(--brand-primary); font-weight: 600; text-decoration: none;">
                Buka Riwayat Transaksi <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="table-container" style="overflow-x: auto;">
            <table style="font-size: 12.5px; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 10px 12px; border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted); width: 40px;">No</th>
                        <th style="padding: 10px 12px; border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted); width: 170px;">Waktu</th>
                        <th style="padding: 10px 12px; border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted);">Aktivitas</th>
                        <th style="padding: 10px 12px; border-bottom: 2px solid var(--border-color); text-align: left; color: var(--text-muted); width: 130px;">Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityLogs ?? [] as $index => $log)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 10px 12px; vertical-align: middle; color: var(--text-muted);">{{ $index + 1 }}</td>
                        <td style="padding: 10px 12px; vertical-align: middle; color: var(--text-muted);">{{ $log->created_at->format('d M Y, H:i') }} WIB</td>
                        <td style="padding: 10px 12px; vertical-align: middle; color: var(--text-main);">{{ ucfirst($log->action) }}</td>
                        <td style="padding: 10px 12px; vertical-align: middle;">
                            <span style="background: rgba(16, 185, 129, 0.15); color: var(--success); padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-shield"></i> {{ strtoupper($log->category) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 25px; color: var(--text-muted);">
                            Belum ada log aktivitas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
