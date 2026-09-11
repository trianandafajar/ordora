<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::with(['orders' => fn ($q) => $q->where('status', '!=', 'paid')->latest()])
            ->latest('number')
            ->get();

        return view('admin.table.index', compact('tables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:20', 'unique:tables,number'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $data['qr_token'] = Str::random(32);
        $data['status'] = 'available';

        Table::create($data);

        return back()->with('success', 'Table created.');
    }

    public function update(Request $request, Table $table)
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:20', 'unique:tables,number,'.$table->id],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $table->update($data);

        return back()->with('success', 'Table updated.');
    }

    public function destroy(Table $table)
    {
        if ($table->orders()->where('status', '!=', 'paid')->exists()) {
            return back()->with('error', "Table \"{$table->number}\" has active order(s) and cannot be deleted.");
        }

        $number = $table->number;
        $table->delete();

        return back()->with('success', "Table \"{$number}\" deleted.");
    }

    public function regenQr(Table $table)
    {
        $table->update(['qr_token' => Str::random(32)]);

        return back()->with('success', 'QR token regenerated.');
    }

    public function qr(Table $table)
    {
        return response(QrCode::format('png')->size(300)->generate($table->qr_url))
            ->header('Content-Type', 'image/png');
    }
}