<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Kas Masuk {{ $cashIn->reference_no ?? '' }} - SUPOS | Sinar Utama</title>
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
        $sourceName =
            $cashIn->source?->customer?->name ??
            ($cashIn->source?->product?->name ?? 'Penerimaan Non-Penjualan (Manual)');
        $wibTime = $cashIn->received_at ? $cashIn->received_at->translatedFormat('d/m/Y H:i') . ' WIB' : '-';
    @endphp

    <div class="toolbar">
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Sekarang</button>
        <button type="button" class="btn" onclick="window.close()">Tutup</button>
    </div>

    <div class="sheet">
        <div class="doc-header">
            <div>
                <h1 class="company-name">SINAR UTAMA GROSIR</h1>
                <div class="company-sub">Bukti Penerimaan Kas & Jurnal Keuangan (Cash In Voucher)</div>
            </div>
            <div class="doc-meta">
                <div class="doc-title">{{ $cashIn->reference_no ?: 'JURNAL KAS MASUK' }}</div>
                <div class="doc-date">Tanggal: {{ $wibTime }}</div>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-label">Diterima Dari / Sumber:</div>
                <div class="info-value">{{ $sourceName }}</div>
                <div class="company-sub">Jenis Pencatatan:
                    {{ $cashIn->is_auto ? 'Sinkronisasi Otomatis' : 'Input Manual Admin' }}</div>
            </div>
            <div style="text-align: right;">
                <div class="info-label">Status Jurnal:</div>
                <div class="status-box">{{ $cashIn->is_auto ? 'TERPOSTING' : 'TERCATAT' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 24%;">Kategori Penerimaan</th>
                    <th style="width: 36%;">Keterangan / Rincian</th>
                    <th style="width: 20%;">Akun Penampung</th>
                    <th class="text-right" style="width: 20%;">Nominal Masuk</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>{{ $cashIn->category_label }}</strong></td>
                    <td>{{ $cashIn->notes ?: '-' }}</td>
                    <td>{{ $cashIn->account_label }}</td>
                    <td class="text-right"><strong>Rp {{ number_format((int) $cashIn->amount, 0, ',', '.') }}</strong>
                    </td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL KAS DITERIMA</td>
                    <td class="text-right">Rp {{ number_format((int) $cashIn->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signatures">
            <div class="sig-col">
                <div>Diserahkan Oleh,</div>
                <div class="sig-line">( {{ $sourceName }} )</div>
            </div>
            <div class="sig-col">
                <div>Diperiksa Oleh,</div>
                <div class="sig-line">( Kepala Gudang / Admin )</div>
            </div>
            <div class="sig-col">
                <div>Diterima Oleh,</div>
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