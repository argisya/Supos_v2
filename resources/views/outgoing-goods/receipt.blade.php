<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction->no_invoice }} - SUPOS | Sinar Utama</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            line-height: 1.5;
        }

        .toolbar {
            max-width: 780px;
            margin: 0 auto 16px auto;
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-primary {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
        }

        .sheet {
            max-width: 780px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
        }

        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.3px;
            margin: 0;
        }

        .company-sub {
            font-size: 12px;
            color: #475569;
            margin-top: 2px;
        }

        .doc-meta {
            text-align: right;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
        }

        .doc-date {
            font-size: 12px;
            color: #475569;
            margin-top: 2px;
        }

        .info-grid {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 700;
        }

        .status-box {
            display: inline-block;
            padding: 4px 12px;
            border: 1.5px solid #0f172a;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .due-note {
            margin-top: 6px;
            font-size: 12px;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background: #f8fafc;
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            border-top: 1.5px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-weight: 800;
            font-size: 14px;
            background: #f8fafc;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            text-align: center;
            margin-top: 56px;
            padding: 0 24px;
        }

        .sig-col {
            width: 200px;
        }

        .sig-line {
            margin-top: 64px;
            border-top: 1px solid #0f172a;
            padding-top: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    @php
        $soldAtWib = $transaction->sold_at
            ? $transaction->sold_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB'
            : '-';
    @endphp

    <div class="toolbar">
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Sekarang</button>
        <button type="button" class="btn" onclick="window.close()">Tutup</button>
    </div>

    <div class="sheet">
        <div class="doc-header">
            <div>
                <h1 class="company-name">SINAR UTAMA GROSIR</h1>
                <div class="company-sub">Faktur Penjualan &amp; Surat Jalan Pengiriman Barang</div>
            </div>
            <div class="doc-meta">
                <div class="doc-title">{{ $transaction->no_invoice }}</div>
                <div class="doc-date">Tanggal: {{ $soldAtWib }}</div>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-label">Kepada Yth:</div>
                <div class="info-value">
                    {{ $transaction->customer->name }}
                    @if ($transaction->customer->type)
                        ({{ $transaction->customer->type }})
                    @endif
                </div>
                <div class="company-sub">{{ $transaction->customer->address ?? '-' }}</div>
                <div class="company-sub">Telp: {{ $transaction->customer->phone ?? '-' }}</div>
            </div>
            <div style="text-align: right;">
                <div class="info-label">Status Pembayaran:</div>
                <div class="status-box">{{ strtoupper($transaction->payment_status) }}</div>
                @if ($transaction->payment_status === 'tempo' && $transaction->due_date)
                    <div class="due-note">
                        Jatuh Tempo: <strong>{{ $transaction->due_date->translatedFormat('d M Y') }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 17%;">SKU</th>
                    <th style="width: 31%;">Nama Barang</th>
                    <th class="text-center" style="width: 11%;">Qty Box</th>
                    <th class="text-center" style="width: 11%;">Total Pcs</th>
                    <th class="text-right" style="width: 14%;">Harga / Box</th>
                    <th class="text-right" style="width: 16%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="white-space: nowrap;"><strong>{{ $transaction->product->sku }}</strong></td>
                    <td>
                        <strong>{{ $transaction->product->name }}</strong>
                        <div class="company-sub">Isi {{ $transaction->product->box_pcs_ratio }} Pcs/Box</div>
                    </td>
                    <td class="text-center" style="white-space: nowrap;">
                        <strong>{{ number_format($transaction->box_qty, 0, ',', '.') }} Box</strong></td>
                    <td class="text-center" style="white-space: nowrap;">
                        {{ number_format($transaction->total_pcs, 0, ',', '.') }} Pcs</td>
                    <td class="text-right" style="white-space: nowrap;">Rp
                        {{ number_format($transaction->sell_per_box, 0, ',', '.') }}</td>
                    <td class="text-right" style="white-space: nowrap;"><strong>Rp
                            {{ number_format($transaction->subtotal, 0, ',', '.') }}</strong></td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" class="text-right">TOTAL FAKTUR</td>
                    <td class="text-right" style="white-space: nowrap;">Rp
                        {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signatures">
            <div class="sig-col">
                <div>Penerima / Toko Mitra,</div>
                <div class="sig-line">( {{ $transaction->customer->name }} )</div>
            </div>
            <div class="sig-col">
                <div>Bagian Gudang / Pengirim,</div>
                <div class="sig-line">( Admin Gudang )</div>
            </div>
            <div class="sig-col">
                <div>Hormat Kami,</div>
                <div class="sig-line">( Kasir Grosir )</div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
