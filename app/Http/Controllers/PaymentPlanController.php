<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentPlanController extends Controller
{
    /**
     * Display a listing of payment plans.
     */
    public function index()
    {
        $plans = PaymentPlan::ordered()->get();

        return view('admin.payment-plans.index', ['plans' => $plans]);
    }

    /**
     * Show the form for creating a new payment plan.
     */
    public function create()
    {
        return view('admin.payment-plans.create');
    }

    /**
     * Store a newly created payment plan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                     => 'required|string|max:255|unique:payment_plans',
            'description'              => 'nullable|string',
            'price'                    => 'required|numeric|min:0',
            'billing_cycle'            => 'required|in:monthly,yearly,lifetime',
            'features'                 => 'required|array|min:1',
            'features.*'               => 'required|string',
            'max_tickets_per_month'    => 'required|integer|min:0',
            'max_concurrent_purchases' => 'required|integer|min:1',
            'max_platforms'            => 'required|integer|min:0',
            'priority_support'         => 'boolean',
            'advanced_analytics'       => 'boolean',
            'automated_purchasing'     => 'boolean',
            'is_active'                => 'boolean',
            'sort_order'               => 'integer|min:0',
        ]);

        PaymentPlan::create([
            'name'                     => $request->name,
            'slug'                     => Str::slug($request->name),
            'description'              => $request->description,
            'price'                    => $request->price,
            'billing_cycle'            => $request->billing_cycle,
            'features'                 => array_filter($request->features),
            'max_tickets_per_month'    => $request->max_tickets_per_month,
            'max_concurrent_purchases' => $request->max_concurrent_purchases,
            'max_platforms'            => $request->max_platforms,
            'priority_support'         => $request->boolean('priority_support'),
            'advanced_analytics'       => $request->boolean('advanced_analytics'),
            'automated_purchasing'     => $request->boolean('automated_purchasing'),
            'is_active'                => $request->boolean('is_active', TRUE),
            'sort_order'               => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.payment-plans.index')
            ->with('success', 'Payment plan created successfully.');
    }

    /**
     * Display the specified payment plan.
     */
    public function show(PaymentPlan $paymentPlan)
    {
        $paymentPlan->load(['subscriptions.user']);

        return view('admin.payment-plans.show', ['paymentPlan' => $paymentPlan]);
    }

    /**
     * Show the form for editing the specified payment plan.
     */
    public function edit(PaymentPlan $paymentPlan)
    {
        return view('admin.payment-plans.edit', ['paymentPlan' => $paymentPlan]);
    }

    /**
     * Update the specified payment plan.
     */
    public function update(Request $request, PaymentPlan $paymentPlan)
    {
        $request->validate([
            'name'                     => 'required|string|max:255|unique:payment_plans,name,' . $paymentPlan->id,
            'description'              => 'nullable|string',
            'price'                    => 'required|numeric|min:0',
            'billing_cycle'            => 'required|in:monthly,yearly,lifetime',
            'features'                 => 'required|array|min:1',
            'features.*'               => 'required|string',
            'max_tickets_per_month'    => 'required|integer|min:0',
            'max_concurrent_purchases' => 'required|integer|min:1',
            'max_platforms'            => 'required|integer|min:0',
            'priority_support'         => 'boolean',
            'advanced_analytics'       => 'boolean',
            'automated_purchasing'     => 'boolean',
            'is_active'                => 'boolean',
            'sort_order'               => 'integer|min:0',
        ]);

        $paymentPlan->update([
            'name'                     => $request->name,
            'slug'                     => Str::slug($request->name),
            'description'              => $request->description,
            'price'                    => $request->price,
            'billing_cycle'            => $request->billing_cycle,
            'features'                 => array_filter($request->features),
            'max_tickets_per_month'    => $request->max_tickets_per_month,
            'max_concurrent_purchases' => $request->max_concurrent_purchases,
            'max_platforms'            => $request->max_platforms,
            'priority_support'         => $request->boolean('priority_support'),
            'advanced_analytics'       => $request->boolean('advanced_analytics'),
            'automated_purchasing'     => $request->boolean('automated_purchasing'),
            'is_active'                => $request->boolean('is_active', TRUE),
            'sort_order'               => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.payment-plans.index')
            ->with('success', 'Payment plan updated successfully.');
    }

    /**
     * Remove the specified payment plan.
     */
    public function destroy(PaymentPlan $paymentPlan)
    {
        // Check if plan has active subscriptions
        if ($paymentPlan->activeSubscriptions()->count() > 0) {
            return redirect()->route('admin.payment-plans.index')
                ->with('error', 'Cannot delete payment plan with active subscriptions.');
        }

        $paymentPlan->delete();

        return redirect()->route('admin.payment-plans.index')
            ->with('success', 'Payment plan deleted successfully.');
    }
}
