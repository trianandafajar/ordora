@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Tables</h1>
        <button x-data @click="$dispatch('open-modal', { id: 'create-table' })"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity cursor-pointer">
            Add Table
        </button>
    </div>

    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left font-medium w-10">#</th>
                    <th class="p-4 text-left font-medium">Number</th>
                    <th class="p-4 text-center font-medium">Capacity</th>
                    <th class="p-4 text-center font-medium">Status</th>
                    <th class="p-4 text-center font-medium">QR</th>
                    <th class="p-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tables as $index => $tbl)
                <tr class="border-t last:border-0 hover:bg-accent/30">
                    <td class="p-4 text-muted-foreground">{{ $index + 1 }}</td>
                    <td class="p-4 font-medium">{{ $tbl->number }}</td>
                    <td class="p-4 text-center text-muted-foreground">{{ $tbl->capacity }}</td>
                    <td class="p-4 text-center">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium
                            {{ $tbl->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $tbl->status }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <img src="{{ route('admin.tables.qr', $tbl) }}" alt="QR Table {{ $tbl->number }}"
                            class="h-10 w-10 mx-auto rounded-sm border">
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button x-data @click="$dispatch('open-modal', { id: 'detail-table-{{ $tbl->id }}' })"
                                class="text-primary hover:underline cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                            <button x-data @click="$dispatch('open-modal', { id: 'edit-table-{{ $tbl->id }}' })"
                                class="text-primary hover:underline cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>

                            </button>
                            <button x-data @click="$dispatch('open-modal', { id: 'delete-table-{{ $tbl->id }}' })"
                                class="text-destructive hover:underline cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>

                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-muted-foreground">No tables yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- create table --}}
<x-modal id="create-table" title="New Table">
    <form method="POST" action="{{ route('admin.tables.store') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Table Number</label>
                <input name="number" required maxlength="20"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    placeholder="e.g. 1, A1, VIP-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Capacity</label>
                <input name="capacity" type="number" required min="1" max="50" value="4"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'create-table' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Create
                Table</button>
        </div>
    </form>
</x-modal>

{{-- edit & delete & detail modals --}}
@foreach($tables as $tbl)
<x-modal id="edit-table-{{ $tbl->id }}" title="Edit Table">
    <form method="POST" action="{{ route('admin.tables.update', $tbl) }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Table Number</label>
                <input name="number" required maxlength="20" value="{{ $tbl->number }}"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Capacity</label>
                <input name="capacity" type="number" required min="1" max="50" value="{{ $tbl->capacity }}"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'edit-table-{{ $tbl->id }}' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Save
                Changes</button>
        </div>
    </form>
</x-modal>

<x-modal id="delete-table-{{ $tbl->id }}" title="Delete Table">
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            Are you sure you want to delete table <strong>"{{ $tbl->number }}"</strong>? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'delete-table-{{ $tbl->id }}' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
            <form method="POST" action="{{ route('admin.tables.destroy', $tbl) }}">
                @csrf @method('DELETE')
                <button type="submit"
                    class="rounded-md bg-destructive text-destructive-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Delete</button>
            </form>
        </div>
    </div>
</x-modal>

<x-modal id="detail-table-{{ $tbl->id }}" title="Table Detail — #{{ $tbl->number }}" maxWidth="max-w-2xl">
    <div x-data="{
            qrSrc: '{{ route('admin.tables.qr', $tbl) }}',
            regenUrl: '{{ route('admin.tables.regenQr', $tbl) }}',
            regenerating: false,
            async regenerate() {
                this.regenerating = true;
                try {
                    const res = await fetch(this.regenUrl, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Failed to regenerate');
                    this.qrSrc = data.qr_url + '?t=' + Date.now();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: e.message, type: 'error' } }));
                } finally {
                    this.regenerating = false;
                }
            }
        }" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        {{-- info --}}
        <div class="space-y-4">
            <div>
                <h4 class="text-sm font-medium text-muted-foreground mb-2">Info</h4>
                <div class="rounded-lg border p-3 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Number</span> <span
                            class="font-medium">{{ $tbl->number }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Capacity</span> <span
                            class="font-medium">{{ $tbl->capacity }} people</span></div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Status</span>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium
                            {{ $tbl->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $tbl->status }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Menu URL</span>
                        <a href="{{ $tbl->qr_url }}" target="_blank"
                            class="text-primary text-xs hover:underline truncate max-w-[160px]">{{ $tbl->qr_url }}</a>
                    </div>
                </div>
            </div>

            {{-- active Orders --}}
            <div>
                <h4 class="text-sm font-medium text-muted-foreground mb-2">Active Orders</h4>
                @forelse($tbl->orders as $order)
                <div class="rounded-lg border p-3 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="font-medium">#{{ $order->id }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs capitalize font-medium
                            bg-muted text-muted-foreground">{{ $order->status }}</span>
                    </div>
                    <div class="text-muted-foreground text-xs">Customer: {{ $order->customer_name ?? '—' }}</div>
                    <div class="font-medium">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                    <div class="text-xs text-muted-foreground">{{ $order->created_at->format('d M Y H:i') }}</div>
                </div>
                @empty
                <div class="rounded-lg border p-4 text-center text-sm text-muted-foreground">
                    No active orders
                </div>
                @endforelse
            </div>
        </div>

        {{-- qr code --}}
        <div class="flex flex-col items-center gap-4">
            <h4 class="text-sm font-medium text-muted-foreground">QR Code</h4>
            <div class="rounded-lg border bg-white p-4">
                <img :src="qrSrc" alt="QR Table {{ $tbl->number }}" class="w-48 h-48">
            </div>
            <p class="text-xs text-muted-foreground text-center">Scan to view menu at table {{ $tbl->number }}</p>
            <div class="flex gap-2" x-data="{ downloading: false }">
                <button type="button"
                    @click="downloading = true; window.location.href = qrSrc; setTimeout(() => downloading = false, 2000)"
                    :disabled="downloading"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span x-text="downloading ? 'Downloading...' : 'Download'"></span>
                </button>
                <button type="button" @click="regenerate" :disabled="regenerating"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <svg class="h-4 w-4" :class="regenerating ? 'animate-spin' : ''" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="regenerating ? 'Regenerating...' : 'Regenerate QR'"></span>
                </button>
            </div>
        </div>
    </div>
</x-modal>
@endforeach
@endsection