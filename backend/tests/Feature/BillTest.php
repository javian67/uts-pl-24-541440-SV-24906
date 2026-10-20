<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BillTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: memastikan login sukses mengembalikan status 200 dan access_token.
     */
    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'user',
            ]);
    }

    /**
     * Test 2: memastikan user terautentikasi bisa membuat tagihan baru dan mengembalikan status 201.
     */
    public function test_authenticated_user_can_create_bill(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'nama_tagihan' => 'Tagihan Listrik PLN',
            'kategori' => 'Utilitas',
            'jumlah' => 250000.00,
            'tanggal_jatuh_tempo' => '2026-11-01',
            'status' => 'Belum Dibayar',
            'catatan' => 'Pembayaran tagihan listrik bulanan',
        ];

        $response = $this->postJson('/api/bills', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nama_tagihan' => 'Tagihan Listrik PLN',
                'kategori' => 'Utilitas',
            ]);

        $this->assertDatabaseHas('bills', [
            'user_id' => $user->id,
            'nama_tagihan' => 'Tagihan Listrik PLN',
            'kategori' => 'Utilitas',
        ]);
    }

    /**
     * Test user registration returns status 201 and token.
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'user',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
        ]);
    }

    /**
     * Test login with invalid credentials returns status 401.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'wrong@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'incorrectpassword',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);
    }

    /**
     * Test bill creation fails with 422 when required fields are missing.
     */
    public function test_create_bill_validation_fails(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/bills', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama_tagihan', 'kategori', 'jumlah', 'tanggal_jatuh_tempo']);
    }

    /**
     * Test user can get list of their own bills.
     */
    public function test_authenticated_user_can_get_bills(): void
    {
        $user = User::factory()->create();
        Bill::create([
            'user_id' => $user->id,
            'nama_tagihan' => 'Sewa Kos',
            'kategori' => 'Akomodasi',
            'jumlah' => 1200000.00,
            'tanggal_jatuh_tempo' => '2026-11-05',
            'status' => 'Belum Dibayar',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/bills');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test user can update their own bill.
     */
    public function test_authenticated_user_can_update_bill(): void
    {
        $user = User::factory()->create();
        $bill = Bill::create([
            'user_id' => $user->id,
            'nama_tagihan' => 'Langganan Netflix',
            'kategori' => 'Langganan',
            'jumlah' => 186000.00,
            'tanggal_jatuh_tempo' => '2026-11-15',
            'status' => 'Belum Dibayar',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/bills/{$bill->id}", [
            'status' => 'Sudah Dibayar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'Sudah Dibayar');

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'Sudah Dibayar',
        ]);
    }

    /**
     * Test user can delete their own bill.
     */
    public function test_authenticated_user_can_delete_bill(): void
    {
        $user = User::factory()->create();
        $bill = Bill::create([
            'user_id' => $user->id,
            'nama_tagihan' => 'Air PDAM',
            'kategori' => 'Utilitas',
            'jumlah' => 50000.00,
            'tanggal_jatuh_tempo' => '2026-11-20',
            'status' => 'Belum Dibayar',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/bills/{$bill->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('bills', [
            'id' => $bill->id,
        ]);
    }

    /**
     * Test user cannot access another user's bill (returns 404).
     */
    public function test_user_cannot_access_other_users_bill(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $bill = Bill::create([
            'user_id' => $user1->id,
            'nama_tagihan' => 'Tagihan Wifi',
            'kategori' => 'Langganan',
            'jumlah' => 350000.00,
            'tanggal_jatuh_tempo' => '2026-11-10',
            'status' => 'Belum Dibayar',
        ]);

        Sanctum::actingAs($user2);

        $response = $this->getJson("/api/bills/{$bill->id}");

        $response->assertStatus(404);
    }
}
