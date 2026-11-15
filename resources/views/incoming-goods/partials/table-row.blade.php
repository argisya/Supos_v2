@php
    $isTempo   = $trx->payment_status === 'tempo';
    $diffDays  = $trx->due_date
        ? (int) now('Asia/Jakarta')->startOfDay()->diffInDays($trx->due_date->startOfDay(), false)
        : null;
    $isOverdue = $isTempo && $diffDays !== null && $diffDays < 0;
    $isUrgent  = $isTempo && $diffDays !== null && $diffDays <= 7;

    $detailData = [
        'no_faktur'      => $trx->no_faktur,
        'received_at'    => $trx->received_at->translatedFormat('d F Y'),
        'created_time'   => $trx->created_at->wib()->format('H:i') . ' WIB',
        'supplier_name'  => $trx->supplier->name,
        'supplier_type'  => $trx->supplier->type ?? 'Supplier Umum',
        'product_name'   => $trx->product->name,
        'product_sku'    => $trx->product->sku,
        'ratio'          => $trx->product->box_pcs_ratio,
        'current_stock'  => $trx->product->stock_pcs,
        'box_qty'        => $trx->box_qty,
        'total_pcs'      => $trx->total_pcs,
        'price_per_box'  => (int) $trx->price_per_box,
        'price_per_pcs'  => (int) $trx->price_per_pcs,
        'subtotal'       => (int) $trx->subtotal,
        'payment_status' => $trx->payment_status,
        'due_date'       => $trx->due_date?->translatedFormat('d F Y') ?? '—',
    ];
@endphp

<tr class="hover:bg-surface-container-high/40 transition-colors group {{ $isUrgent ? 'bg-error/5' : '' }}">
    <td class="py-3.5 px-space-md">
        <div class="flex flex-col">
            <span class="font-label-md text-label-md text-on-surface font-bold group-hover:text-primary transition-colors">{{ $trx->no_faktur }}</span>
            <span class="text-[11px] text-on-surface-variant font-label-sm">
                {{ $trx->received_at->translatedFormat('d M Y') }} • {{ $trx->created_at->wib()->format('H:i') }} WIB
            </span>
        </div>
    </td>
    <td class="py-3.5 px-space-md">
        <div class="flex items-center gap-space-xs">
            <div class="w-7 h-7 rounded-lg bg-surface-container flex items-center justify-center text-on-surface-variant text-label-sm uppercase font-bold shrink-0">
                {{ $trx->supplier->initials ?? mb_substr($trx->supplier->name, 0, 2) }}
            </div>
            <div class="flex flex-col">
                <span class="font-semibold text-on-surface">{{ $trx->supplier->name }}</span>
                <span class="text-[10px] text-on-surface-variant">{{ $trx->supplier->type }}</span>
            </div>
        </div>
    </td>
    <td class="py-3.5 px-space-md">
        <div class="flex flex-col">
            <div class="flex items-center gap-1.5">
                <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $trx->product->name }}</span>
                <span class="px-1.5 py-0.2 rounded bg-surface-container-highest text-[9px] font-label-sm text-on-surface-variant">BOX</span>
            </div>
            <span class="text-label-sm font-label-sm text-tertiary">{{ $trx->product->sku }} • 1 Box = {{ $trx->product->box_pcs_ratio }} Pcs</span>
        </div>
    </td>
    <td class="py-3.5 px-space-md text-center">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container">
            <span class="font-bold text-on-surface font-headline-sm text-body-md">{{ $num($trx->box_qty) }} Box</span>
            <span class="text-on-surface-variant text-[11px]">→</span>
            <span class="font-bold text-primary font-label-md text-label-md">{{ $num($trx->total_pcs) }} Pcs</span>
        </div>
    </td>
    <td class="py-3.5 px-space-md text-right">
        <div class="flex flex-col items-end">
            <span class="font-bold text-on-surface font-label-md text-label-md">
                {{ $fmt($trx->price_per_box) }} <span class="text-on-surface-variant text-[10px] font-normal">/Box</span>
            </span>
            <div class="flex items-center gap-1 mt-0.5">
                <span class="px-1.5 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-bold">{{ $fmt($trx->price_per_pcs) }}/Pcs</span>
                <span class="material-symbols-outlined text-[13px] text-primary" title="HPP Terkunci">verified</span>
            </div>
        </div>
    </td>
    <td class="py-3.5 px-space-md text-right">
        <span class="font-headline-sm text-body-md font-bold text-on-surface">{{ $fmt($trx->subtotal) }}</span>
    </td>
    <td class="py-3.5 px-space-md text-center">
        @if($trx->payment_status === 'lunas')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/15 text-primary text-label-sm font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                Lunas
            </span>
        @else
            <div class="inline-flex flex-col items-center">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-error/20 text-error text-[10px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span>
                    Tempo {{ $trx->due_date?->translatedFormat('d M') }}
                </span>
                @if($isOverdue)
                    <span class="text-[9px] text-error font-bold mt-0.5">Terlambat {{ abs($diffDays) }} hari</span>
                @elseif($diffDays === 0)
                    <span class="text-[9px] text-error font-bold mt-0.5">Jatuh Tempo Hari Ini</span>
                @else
                    <span class="text-[9px] text-error/80 mt-0.5">Sisa {{ $diffDays }} hari</span>
                @endif
            </div>
        @endif
    </td>
    <td class="py-3.5 px-space-md text-center">
        <div class="flex items-center justify-center gap-1">
            @if($trx->payment_status === 'tempo')
                <form method="POST" action="{{ route('incoming-goods.pay', $trx) }}" id="payForm-{{ $trx->id }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="button"
                            onclick="confirmPayAction('payForm-{{ $trx->id }}', @js($trx->no_faktur), @js($trx->supplier->name), @js($fmt($trx->subtotal)))"
                            class="px-2 py-1 rounded-lg bg-primary-container text-on-primary-container text-[11px] font-bold flex items-center gap-1 cursor-pointer hover:bg-primary transition-colors"
                            title="Bayar Sekarang">
                        <span class="material-symbols-outlined text-[14px]">price_check</span>
                        <span>Bayar</span>
                    </button>
                </form>
            @endif

            <button type="button"
                    onclick='openDetailFakturModal(@json($detailData))'
                    class="w-8 h-8 rounded-lg bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:text-tertiary transition-colors cursor-pointer"
                    title="Detail Faktur">
                <span class="material-symbols-outlined text-[16px]">visibility</span>
            </button>

            <form method="POST" action="{{ route('incoming-goods.rollback', $trx) }}" id="rollbackForm-{{ $trx->id }}" class="inline">
                @csrf @method('DELETE')
                <button type="button"
                        onclick="confirmRollbackAction('rollbackForm-{{ $trx->id }}', @js($trx->no_faktur), @js($trx->product->name), @js($num($trx->total_pcs)))"
                        class="w-8 h-8 rounded-lg bg-surface-container hover:bg-error/20 flex items-center justify-center text-on-surface-variant hover:text-error transition-colors cursor-pointer"
                        title="Rollback Transaksi">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                </button>
            </form>
        </div>
    </td>
</tr>