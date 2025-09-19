<?php declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\TicketPurchaseValidationMiddleware;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\PaymentPlan;
use App\Services\TicketPurchaseService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\TestCase;

class TicketPurchaseValidationMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private TicketPurchaseValidationMiddleware $middleware;

    private User $customerUser;

    private User $agentUser;

    private Ticket $ticket;

    #[Test]
    public function it_allows_purchase_for_customer_with_active_subscription(): void
    {
        // Create active subscription with a valid payment plan
        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 100]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 2,
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Purchase allowed', 200));

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('Purchase allowed', $response->getContent());
    }

    #[Test]
    public function it_blocks_purchase_for_customer_without_active_subscription(): void
    {
        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 1,
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Active subscription required', $responseData['reasons']);
    }

    #[Test]
    public function it_allows_unlimited_purchases_for_agent(): void
    {
        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 50,  // Large quantity
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->agentUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Agent purchase allowed', 200));

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('Agent purchase allowed', $response->getContent());
    }

    #[Test]
    public function it_blocks_purchase_when_exceeding_ticket_limit(): void
    {
        // Create subscription with low limit via payment plan
        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 3]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 5,  // Exceeds limit
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Would exceed monthly ticket limit', $responseData['reasons']);
    }

    #[Test]
    public function it_blocks_purchase_for_unavailable_ticket(): void
    {
        $unavailableTicket = $this->createTestTicket(['is_available' => FALSE]);

        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 100]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $unavailableTicket->id . '/purchase', 'POST', [
            'quantity'  => 1,
            'ticket_id' => $unavailableTicket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Ticket is not available', $responseData['reasons']);
    }

    #[Test]
    public function it_blocks_purchase_when_quantity_exceeds_availability(): void
    {
        $limitedTicket = $this->createTestTicket([
            'available_quantity' => 2,
            'is_available'       => TRUE,
        ]);

        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 100]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $limitedTicket->id . '/purchase', 'POST', [
            'quantity'  => 5,  // More than available
            'ticket_id' => $limitedTicket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Not enough tickets available', $responseData['reasons']);
    }

    #[Test]
    public function it_handles_missing_quantity_parameter(): void
    {
        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 100]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Quantity parameter is required', $responseData['message']);
    }

    #[Test]
    public function it_handles_invalid_quantity_parameter(): void
    {
        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 100]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 'invalid',
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Quantity must be a valid positive integer', $responseData['message']);
    }

    #[Test]
    public function it_handles_zero_quantity(): void
    {
        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 100]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 0,
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Quantity must be a valid positive integer', $responseData['message']);
    }

    #[Test]
    public function it_handles_missing_ticket_parameter(): void
    {
        $request = Request::create('/tickets/invalid/purchase', 'POST', [
            'quantity' => 1,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        // Not setting ticket parameter to simulate missing ticket

        $request->headers->set('Accept', 'application/json');
        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Ticket not found', $responseData['message']);
    }

    #[Test]
    public function it_handles_unauthenticated_user(): void
    {
        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 1,
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(function (): void {
            // No authenticated user
        });
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Authentication required', $responseData['message']);
    }

    #[Test]
    public function it_provides_eligibility_information_in_response(): void
    {
        $plan = PaymentPlan::factory()->create(['max_tickets_per_month' => 50]);
        UserSubscription::create([
            'user_id'         => $this->customerUser->id,
            'payment_plan_id' => $plan->id,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addMonth(),
        ]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 60,  // Exceeds limit
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('user_info', $responseData);
        $this->assertEquals(50, $responseData['user_info']['ticket_limit']);
        $this->assertEquals(0, $responseData['user_info']['monthly_usage']);
        $this->assertEquals(50, $responseData['user_info']['remaining_tickets']);
    }

    #[Test]
    public function it_respects_free_access_period_for_new_customers(): void
    {
        $newCustomer = $this->createTestUser([
            'role'       => 'customer',
            'created_at' => now()->subDays(3), // Within free access period
        ]);

        config(['subscription.free_access_days' => 7]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 1,
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $newCustomer);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Free access purchase allowed', 200));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Free access purchase allowed', $response->getContent());
    }

    #[Test]
    public function it_blocks_purchase_after_free_access_expires(): void
    {
        $oldCustomer = $this->createTestUser([
            'role'       => 'customer',
            'created_at' => now()->subDays(10), // Beyond free access period
        ]);

        config(['subscription.free_access_days' => 7]);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 1,
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $oldCustomer);
        $request->headers->set('Accept', 'application/json');

        $response = $this->middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertContains('Active subscription required', $responseData['reasons']);
        $this->assertEquals('An active subscription is required to purchase tickets.', $responseData['message']);
    }

    #[Test]
    public function it_handles_service_exceptions_gracefully(): void
    {
        // Mock the service to throw an exception
        $mockService = Mockery::mock(TicketPurchaseService::class);
        $mockService->shouldReceive('checkPurchaseEligibility')
            ->andThrow(new Exception('Service temporarily unavailable'));

        $middleware = new TicketPurchaseValidationMiddleware($mockService);

        $request = Request::create('/tickets/' . $this->ticket->id . '/purchase', 'POST', [
            'quantity'  => 1,
            'ticket_id' => $this->ticket->id,
        ]);
        $request->setUserResolver(fn (): User => $this->customerUser);
        $request->headers->set('Accept', 'application/json');

        $response = $middleware->handle($request, fn ($req): Response => new Response('Should not reach here', 200));

        $this->assertEquals(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), TRUE);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Unable to validate purchase at this time. Please try again later.', $responseData['message']);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new TicketPurchaseValidationMiddleware(
            app(TicketPurchaseService::class),
        );

        $this->customerUser = $this->createTestUser(['role' => 'customer']);
        $this->agentUser = $this->createTestUser(['role' => 'agent']);
        $this->ticket = $this->createTestTicket([
            'price'              => 100.00,
            'available_quantity' => 10,
            'is_available'       => TRUE,
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
