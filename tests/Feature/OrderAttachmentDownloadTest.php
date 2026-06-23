<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderAttachmentDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_order_staff_can_download_a_private_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('orders.view', 'web'));

        $attachment = $this->createAttachment();
        Storage::disk('local')->put($attachment->file_path, 'private attachment');

        $this->actingAs($user)
            ->get(route('admin.orders.attachments.download', $attachment))
            ->assertOk()
            ->assertDownload('payment-proof.pdf');
    }

    public function test_user_without_order_permission_cannot_download_a_private_attachment(): void
    {
        Storage::fake('local');

        $attachment = $this->createAttachment();
        Storage::disk('local')->put($attachment->file_path, 'private attachment');

        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.attachments.download', $attachment))
            ->assertForbidden();
    }

    private function createAttachment()
    {
        $order = Order::query()->forceCreate([
            'order_number' => 'ORD-ATTACHMENT-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'grand_total' => 0,
            'is_active' => true,
        ]);

        return $order->attachments()->create([
            'title' => 'Payment proof',
            'original_name' => 'payment-proof.pdf',
            'file_path' => 'order-attachments/payment-proof.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'is_private' => true,
        ]);
    }
}
