<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories(): void
    {
        $admin = User::factory()->create();

        // 1. Tambah kategori
        $res = $this->actingAs($admin)->post(route('admin.settings.categories.store'), [
            'name' => 'Logistik BNN',
        ]);
        $res->assertSessionHas('ok');
        $this->assertDatabaseHas('categories', ['name' => 'Logistik BNN']);

        $category = Category::where('name', 'Logistik BNN')->first();

        // 2. Edit kategori
        $res2 = $this->actingAs($admin)->put(route('admin.settings.categories.update', $category), [
            'name' => 'Logistik & Sarpras',
        ]);
        $res2->assertSessionHas('ok');
        $this->assertDatabaseHas('categories', ['name' => 'Logistik & Sarpras']);

        // 3. Hapus kategori
        $res3 = $this->actingAs($admin)->delete(route('admin.settings.categories.destroy', $category));
        $res3->assertSessionHas('ok');
        $this->assertDatabaseMissing('categories', ['name' => 'Logistik & Sarpras']);
    }

    public function test_admin_can_update_attendee(): void
    {
        $admin = User::factory()->create();
        $event = Event::create([
            'slug' => 'rapat-koordinasi-test',
            'title' => 'Rapat Koordinasi Test',
            'event_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $attendee = Attendee::create([
            'event_id' => $event->id,
            'kategori_instansi' => 'internal',
            'nama' => 'Budi Santoso',
            'divisi' => 'Pemberantasan',
            'no_telp' => '081234567890',
            'jenis_kelamin' => 'L',
            'signature_path' => 'events/test/sig.png',
        ]);

        // Edit nama dan no telp peserta
        $res = $this->actingAs($admin)->put(route('admin.attendees.update', $attendee), [
            'kategori_instansi' => 'internal',
            'nama' => 'Budi Santoso, S.H.',
            'divisi' => 'Pemberantasan & Intelijen',
            'no_telp' => '081299998888',
            'jenis_kelamin' => 'L',
        ]);

        $res->assertSessionHas('ok');
        $this->assertDatabaseHas('attendees', [
            'id' => $attendee->id,
            'nama' => 'Budi Santoso, S.H.',
            'divisi' => 'Pemberantasan & Intelijen',
            'no_telp' => '081299998888',
        ]);
    }

    public function test_event_and_attendee_auto_generates_uuid_v7_and_binds_route(): void
    {
        $admin = User::factory()->create();

        $event = Event::create([
            'slug' => 'rapat-uuid7-test',
            'title' => 'Rapat UUID7 Test',
            'event_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $this->assertNotNull($event->uuid);
        $this->assertSame(36, strlen($event->uuid));

        // URL route uses UUID
        $showUrl = route('admin.events.show', $event);
        $this->assertStringContainsString($event->uuid, $showUrl);

        // Can access via UUID
        $res = $this->actingAs($admin)->get($showUrl);
        $res->assertStatus(200);

        // Can also access via fallback integer ID
        $resFallback = $this->actingAs($admin)->get("/admin/events/{$event->id}");
        $resFallback->assertStatus(200);
    }
}
