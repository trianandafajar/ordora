<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::latest('number')->get();

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

        return back()->with('success', 'Table created. QR token: '.$data['qr_token']);
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
        $table->delete();

        return back()->with('success', 'Table deleted.');
    }

    public function regenQr(Table $table)
    {
        $table->update(['qr_token' => Str::random(32)]);

        return back()->with('success', 'QR token regenerated.');
    }
}
