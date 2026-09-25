<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\TableResource;

class TableApiController extends Controller
{
    public function index()
    {
        return TableResource::collection(Table::with(['orders' => fn ($q) => $q->where('status', '!=', 'served')->latest()])
            ->orderBy('id')
            ->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:20', 'unique:tables,number'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $data['qr_token'] = Str::random(32);
        $data['status'] = 'available';

        $table = Table::create($data);

        return response()->json(['message' => 'Table created.', 'data' => new TableResource($table)], 201);
    }

    public function show(Table $table)
    {
        return new TableResource($table->load(['orders' => fn ($q) => $q->where('status', '!=', 'served')->latest()]));
    }

    public function update(Request $request, Table $table)
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:20', 'unique:tables,number,'.$table->id],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $table->update($data);

        return response()->json(['message' => 'Table updated.', 'data' => new TableResource($table)]);
    }

    public function destroy(Table $table)
    {
        if ($table->orders()->where('status', '!=', 'served')->exists()) {
            return response()->json(['error' => "Table \"{$table->number}\" has active order(s) and cannot be deleted."], 422);
        }

        $table->delete();

        return response()->json(['message' => "Table deleted."]);
    }

    public function regenQr(Table $table)
    {
        $table->update(['qr_token' => Str::random(32)]);
        
        return response()->json([
            'message' => 'QR regenerated.',
            'qr_url' => $table->qr_url, 
        ]);
    }
}
