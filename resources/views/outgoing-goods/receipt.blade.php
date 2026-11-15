<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->no_invoice }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; margin: 24px; font-size: 13px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: 800; letter-spacing: 0.5px; }
        .meta { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta td { padding: 3px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th, table.items td { border: 1px solid #333; padding: 8px; text-align: left; }
        table.items th { background: #f3f4f6; }
        .right { text-align: right !important; }
        .center { text-align: center !important; }
        .badge { display: inline-block; padding: 3px 8px; border: 1px solid #111; font-weight: 700; text-transform: uppercase; font-size: 11px; }
        .sign { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .sign div { width: 180px; }
        .line { border-top: 1px solid #111; margin-top: 52px; padding-top: 4px; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 16px;">
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer;">Tutup</button>
    </div>

    <div class="header">
        <div>
            <div class="title">SINAR UTAMA GROSIR</div>
            <div>Faktur Penjualan & Surat Jalan Pengiriman Barang</div>
        </div>
        <div style="text-align: right;">
            <div class="title">{{ $transaction->no_invoice }}</div>
            <div>Tanggal: {{ $transaction->sold_at->format('d/m/Y H:i') }} WIB</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td width="55%">
                <strong>Kepada Yth:</strong><br>
                {{ $transaction->customer->name }} @if($transaction->customer->type)({{ $transaction->customer->type }})@endif<br>
                {{ $transaction->customer->address ?? '-' }}<br>
                Telp: {{ $transaction->customer->phone ?? '-' }}
            </td>
            <td width="45%" style="text-align: right;">
                <strong>Status Pembayaran:</strong><br>
                <span class="badge">{{ strtoupper($transaction->payment_status) }}</span><br>
                @if($transaction->payment_status === 'tempo' && $transaction->due_date)
                    <div style="margin-top: 4px;">Jatuh Tempo: <strong>{{ $transaction->due_date->format('d M Y') }}</strong></div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th class="center">Qty Box</th>
                <th class="center">Total Pcs</th>
                <th class="right">Harga / Box</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $transaction->product->sku }}</td>
                <td>{{ $transaction->product->name }} (Isi {{ $transaction->product->box_pcs_ratio }} Pcs/Box)</td>
                <td class="center"><strong>{{ number_format($transaction->box_qty, 0, ',', '.') }} Box</strong></td>
                <td class="center">{{ number_format($transaction->total_pcs, 0, ',', '.') }} Pcs</td>
                <td class="right">Rp {{ number_format($transaction->sell_per_box, 0, ',', '.') }}</td>
                <td class="right"><strong>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="right">TOTAL FAKTUR</th>
                <th class="right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="sign">
        <div>
            <div>Penerima / Toko Mitra,</div>
            <div class="line">( {{ $transaction->customer->name }} )</div>
        </div>
        <div>
            <div>Bagian Gudang / Pengirim,</div>
            <div class="line">( Admin Gudang )</div>
        </div>
        <div>
            <div>Hormat Kami,</div>
            <div class="line">( Kasir Grosir )</div>
        </div>
    </div>
</body>
</html>