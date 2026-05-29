<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $expenses = $request->user()->expenses()->latest('expense_date')->latest('created_at')->get();
        return ExpenseResource::collection($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = $request->user()->expenses()->create($request->validated());
        return new ExpenseResource($expense);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $expense = $request->user()->expenses()->findOrFail($id);
        return new ExpenseResource($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreExpenseRequest $request, string $id)
    {
        $expense = $request->user()->expenses()->findOrFail($id);
        $expense->update($request->validated());
        return new ExpenseResource($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $expense = $request->user()->expenses()->findOrFail($id);
        $expense->delete();
        return response()->noContent();
    }
}
