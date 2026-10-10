@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Create Restaurant</h1>
        <p class="text-sm text-muted-foreground mt-1">Add a new restaurant and assign an owner</p>
    </div>

    <div class="rounded-xl border bg-card shadow-sm p-6">
        <form method="POST" action="{{ route('superadmin.restaurants.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Restaurant Name</label>
                <input name="name" required maxlength="255" value="{{ old('name') }}"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                @error('name')
                    <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Owner (Admin)</label>
                <select name="owner_id" required
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <option value="">Select an owner</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                            {{ $owner->name }} ({{ $owner->email }})
                        </option>
                    @endforeach
                </select>
                @error('owner_id')
                    <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('superadmin.restaurants.index') }}"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer inline-flex items-center">Cancel</a>
                <button type="submit"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Create Restaurant</button>
            </div>
        </form>
    </div>
</div>
@endsection
