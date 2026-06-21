<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $tables = Table::with('currentOrder')->orderBy('number')->paginate(10);

        return view('tables.index', compact('tables'));
    }

    public function create(): View
    {
        return view('tables.form', [
            'table' => new Table(),
            'action' => route('tables.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Table::create($request->validate([
            'number' => ['required', 'integer', 'min:1', 'unique:tables,number'],
        ]));

        return redirect()->route('tables.index')->with('success', 'Table created.');
    }

    public function edit(Table $table): View
    {
        return view('tables.form', [
            'table' => $table,
            'action' => route('tables.update', $table),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Table $table): RedirectResponse
    {
        $table->update($request->validate([
            'number' => ['required', 'integer', 'min:1', 'unique:tables,number,' . $table->id],
        ]));

        return redirect()->route('tables.index')->with('success', 'Table updated.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        if ($table->is_occupied) {
            return back()->with('error', 'Cannot delete an occupied table. Record payment or complete the order first.');
        }

        $table->delete();

        return redirect()->route('tables.index')->with('success', 'Table deleted.');
    }
}
