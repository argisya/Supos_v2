@php
    $isTempo = $trx->payment_status === 'tempo';
    $diffDays = $trx->due_date
        ? (int) now('Asia/Jakarta')->startOfDay()->diffInDays($trx->due_date->startOfDay(), false)
        : null;
    $isOverdue = $isTempo && $diffDays !== null && $diffDays < 0;

    $detailData = [
        'no_faktur' => $trx->no_faktur,
        'received_at' => $trx->received_at->translatedFormat('d F Y'),
        'created_time' => $trx->created_at->wib()->format('H:i') . ' WIB',
        'supplier_name' => $trx->supplier->name,
        'supplier_type' => $trx->supplier->type ?? 'Supplier Umum',
        'product_name' => $trx->product->name,
        'product_sku' => $trx->product->sku,
        'ratio' => $trx->product->box_pcs_ratio,
        'current_stock' => $trx->product->stock_pcs,
        'box_qty' => $trx->box_qty,
        'total_pcs' => $trx->total_pcs,
        'price_per_box' => (int) $trx->price_per_box,
        'price_per_pcs' => (int) $trx->price_per_pcs,
        'subtotal' => (int) $trx->subtotal,
        'payment_status' => $trx->payment_status,
        'due_date' => $trx->due_date?->translatedFormat('d F Y') ?? '—',
    ];
@endphp

<tr class="bg-surface-container-low hover:bg-surface-container-high/40 transition-colors group">
    {{-- 1. No. Faktur & Waktu --}}
    <td class="py-3 px-3 whitespace-nowrap">
        <div class="flex flex-col">
            <span
                class="font-mono text-xs text-on-surface font-bold group-hover:text-primary transition-colors">{{ $trx->no_faktur }}</span>
            <span class="text-[11px] text-on-surface-variant">
                {{ $trx->received_at->translatedFormat('d M Y') }} • {{ $trx->created_at->wib()->format('H:i') }}
            </span>
        </div>
    </td>

    {{-- 2. Supplier --}}
    <td class="py-3 px-3 max-w-[165px]">
        <div class="flex flex-col min-w-0">
            <span class="font-semibold text-on-surface text-xs leading-snug truncate"
                title="{{ $trx->supplier->name }}">{{ $trx->supplier->name }}</span>
            <span class="text-[11px] text-on-surface-variant truncate">{{ $trx->supplier->type ?? 'Supplier' }}</span>
        </div>
    </td>

    {{-- 3. Nama Barang & SKU --}}
    <td class="py-3 px-3 max-w-[190px]">
        <div class="flex flex-col min-w-0">
            <span class="font-headline-sm text-xs font-bold text-on-surface leading-snug truncate"
                title="{{ $trx->product->name }}">{{ $trx->product->name }}</span>
            <span class="text-[11px] text-tertiary font-medium truncate">
                {{ $trx->product->sku }} • {{ $trx->product->box_pcs_ratio }} Pcs/Box
            </span>
        </div>
    </td>

    {{-- 4. Kuantitas Masuk --}}
    <td class="py-3 px-3 text-center whitespace-nowrap">
        <div class="inline-flex flex-col items-center">
            <span class="font-headline-sm text-xs font-bold text-on-surface">{{ $num($trx->box_qty) }} Box</span>
            <span class="text-[11px] font-bold text-primary">+{{ $num($trx->total_pcs) }} Pcs</span>
        </div>
    </td>

    {{-- 5. Harga Modal --}}
    <td class="py-3 px-3 text-right whitespace-nowrap">
        <div class="inline-flex flex-col items-end">
            <span class="font-semibold text-on-surface text-xs">
                {{ $fmt($trx->price_per_box) }}<span
                    class="text-on-surface-variant text-[10px] font-normal">/Box</span>
            </span>
            <span class="text-[11px] font-bold text-primary">
                {{ $fmt($trx->price_per_pcs) }}<span
                    class="font-normal text-on-surface-variant text-[10px]">/Pcs</span>
            </span>
        </div>
    </td>

    {{-- 6. Total Transaksi --}}
    <td class="py-3 px-3 text-right whitespace-nowrap">
        <span class="font-headline-sm text-xs font-extrabold text-on-surface">{{ $fmt($trx->subtotal) }}</span>
    </td>

    {{-- 7. Status & Tempo --}}
    <td class="py-3 px-3 text-center whitespace-nowrap">
        @if ($trx->payment_status === 'lunas')
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-primary/15 text-primary text-[11px] font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                Lunas
            </span>
        @else
            <div class="inline-flex flex-col items-center">
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-error/15 text-error text-[11px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                    Tempo {{ $trx->due_date?->translatedFormat('d M') }}
                </span>
                @if ($isOverdue)
                    <span class="text-[10px] text-error font-bold mt-0.5">Telat {{ abs($diffDays) }} hr</span>
                @elseif($diffDays === 0)
                    <span class="text-[10px] text-error font-bold mt-0.5">Hari Ini</span>
                @else
                    <span class="text-[10px] text-on-surface-variant mt-0.5">Sisa {{ $diffDays }} hr</span>
                @endif
            </div>
        @endif
    </td>

    {{-- 8. Aksi (Isi Rata Kanan, Header Rata Tengah) --}}
    <td class="py-3 px-3 text-right whitespace-nowrap">
        <div class="inline-flex items-center justify-end gap-1">
            @if ($trx->payment_status === 'tempo')
                <form method="POST" action="{{ route('incoming-goods.pay', $trx) }}" id="payForm-{{ $trx->id }}"
                    class="inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="payment_account" value="kas_tunai">
                    <button type="button"
                        onclick="confirmPayAction('payForm-{{ $trx->id }}', @js($trx->no_faktur), @js($trx->supplier->name), @js($fmt($trx->subtotal)))"
                        class="h-7 px-2 rounded-lg bg-primary-container text-on-primary-container text-[11px] font-bold inline-flex items-center gap-1 cursor-pointer hover:bg-primary transition-colors"
                        title="Lunasi Faktur">
                        <span class="material-symbols-outlined text-[14px]">price_check</span>
                        <span>Bayar</span>
                    </button>
                </form>
            @endif

            <button type="button" onclick='openDetailFakturModal(@json($detailData))'
                class="w-7 h-7 rounded-lg bg-surface-container hover:bg-surface-bright inline-flex items-center justify-center text-on-surface-variant hover:text-tertiary transition-colors cursor-pointer"
                title="Lihat Detail Faktur">
                <span class="material-symbols-outlined text-[15px]">visibility</span>
            </button>

            <form method="POST" action="{{ route('incoming-goods.rollback', $trx) }}"
                id="rollbackForm-{{ $trx->id }}" class="inline">
                @csrf @method('DELETE')
                <button type="button"
                    onclick="confirmRollbackAction('rollbackForm-{{ $trx->id }}', @js($trx->no_faktur), @js($trx->product->name), @js($num($trx->total_pcs)))"
                    class="w-7 h-7 rounded-lg bg-surface-container hover:bg-error/20 inline-flex items-center justify-center text-on-surface-variant hover:text-error transition-colors cursor-pointer"
                    title="Hapus Faktur">
                    <span class="material-symbols-outlined text-[15px]">delete</span>
                </button>
            </form>
        </div>
    </td>
</tr>