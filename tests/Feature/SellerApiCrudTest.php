<?php

namespace Tests\Feature;

use App\Models\AnalyticsInsight;
use App\Models\ReportNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerApiCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        Sanctum::actingAs($admin);

        return $admin;
    }

    public function test_admin_can_crud_customers_via_api(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/seller/customers', [
            'name' => 'Customer API',
            'email' => 'customer.api@example.com',
            'phone' => '08123456789',
            'password' => 'password123',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.email', 'customer.api@example.com');

        $customerId = $createResponse->json('data.id');

        $this->getJson('/api/v1/seller/customers')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $customerId);

        $this->putJson('/api/v1/seller/customers/' . $customerId, [
            'name' => 'Customer API Updated',
            'email' => 'customer.api.updated@example.com',
            'phone' => '08999999999',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Customer API Updated');

        $this->deleteJson('/api/v1/seller/customers/' . $customerId)
            ->assertOk();

        $this->assertDatabaseMissing('users', [
            'id' => $customerId,
        ]);
    }

    public function test_admin_can_crud_reports_via_api(): void
    {
        $admin = $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/seller/reports', [
            'title' => 'Laporan Mingguan',
            'period' => 'week',
            'content' => 'Penjualan naik.',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Laporan Mingguan');

        $reportId = $createResponse->json('data.id');

        $this->getJson('/api/v1/seller/reports')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $reportId);

        $this->putJson('/api/v1/seller/reports/' . $reportId, [
            'title' => 'Laporan Mingguan Updated',
            'period' => 'week',
            'content' => 'Penjualan naik signifikan.',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Laporan Mingguan Updated');

        $this->assertDatabaseHas('report_notes', [
            'id' => $reportId,
            'created_by' => $admin->id,
        ]);

        $this->deleteJson('/api/v1/seller/reports/' . $reportId)
            ->assertOk();

        $this->assertDatabaseMissing('report_notes', [
            'id' => $reportId,
        ]);
    }

    public function test_admin_can_crud_analytics_via_api(): void
    {
        $admin = $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/v1/seller/analytics', [
            'title' => 'AOV',
            'metric' => 'average_order_value',
            'value' => 175000,
            'trend' => 'up',
            'note' => 'Meningkat bulan ini.',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.metric', 'average_order_value');

        $insightId = $createResponse->json('data.id');

        $this->getJson('/api/v1/seller/analytics')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $insightId);

        $this->putJson('/api/v1/seller/analytics/' . $insightId, [
            'title' => 'AOV Updated',
            'metric' => 'average_order_value',
            'value' => 180000,
            'trend' => 'up',
            'note' => 'Update performa terbaru.',
        ])->assertOk()
            ->assertJsonPath('data.title', 'AOV Updated');

        $this->assertDatabaseHas('analytics_insights', [
            'id' => $insightId,
            'created_by' => $admin->id,
        ]);

        $this->deleteJson('/api/v1/seller/analytics/' . $insightId)
            ->assertOk();

        $this->assertDatabaseMissing('analytics_insights', [
            'id' => $insightId,
        ]);
    }

    public function test_non_admin_cannot_access_seller_api_resources(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        Sanctum::actingAs($user);

        $report = ReportNote::query()->create([
            'title' => 'Report',
            'period' => 'week',
            'content' => 'Content',
            'created_by' => $user->id,
        ]);

        $insight = AnalyticsInsight::query()->create([
            'title' => 'Insight',
            'metric' => 'orders',
            'value' => 12,
            'trend' => 'stable',
            'note' => null,
            'created_by' => $user->id,
        ]);

        $this->getJson('/api/v1/seller/customers')->assertForbidden();
        $this->putJson('/api/v1/seller/reports/' . $report->id, [
            'title' => 'x',
            'period' => 'week',
            'content' => 'x',
        ])->assertForbidden();
        $this->deleteJson('/api/v1/seller/analytics/' . $insight->id)->assertForbidden();
    }
}
