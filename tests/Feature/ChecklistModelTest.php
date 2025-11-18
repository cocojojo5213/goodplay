<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistResponse;
use App\Models\ChecklistResponseItem;
use App\Models\ChecklistVersion;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_checklist_can_have_many_versions(): void
    {
        $checklist = Checklist::factory()->create();
        
        ChecklistVersion::factory()->count(3)->create([
            'checklist_id' => $checklist->id,
        ]);

        $this->assertCount(3, $checklist->versions);
        $this->assertTrue($checklist->versions->every(fn ($v) => $v->checklist_id === $checklist->id));
    }

    public function test_checklist_version_belongs_to_checklist(): void
    {
        $checklist = Checklist::factory()->create();
        $version = ChecklistVersion::factory()->create([
            'checklist_id' => $checklist->id,
        ]);

        $this->assertInstanceOf(Checklist::class, $version->checklist);
        $this->assertEquals($checklist->id, $version->checklist->id);
    }

    public function test_checklist_version_can_have_many_items(): void
    {
        $version = ChecklistVersion::factory()->create();
        
        ChecklistItem::factory()->count(5)->create([
            'checklist_version_id' => $version->id,
        ]);

        $this->assertCount(5, $version->items);
        $this->assertTrue($version->items->every(fn ($i) => $i->checklist_version_id === $version->id));
    }

    public function test_checklist_item_belongs_to_version(): void
    {
        $version = ChecklistVersion::factory()->create();
        $item = ChecklistItem::factory()->create([
            'checklist_version_id' => $version->id,
        ]);

        $this->assertInstanceOf(ChecklistVersion::class, $item->version);
        $this->assertEquals($version->id, $item->version->id);
    }

    public function test_checklist_version_can_have_many_responses(): void
    {
        $version = ChecklistVersion::factory()->create();
        
        ChecklistResponse::factory()->count(4)->create([
            'checklist_version_id' => $version->id,
        ]);

        $this->assertCount(4, $version->responses);
        $this->assertTrue($version->responses->every(fn ($r) => $r->checklist_version_id === $version->id));
    }

    public function test_checklist_response_belongs_to_version(): void
    {
        $version = ChecklistVersion::factory()->create();
        $response = ChecklistResponse::factory()->create([
            'checklist_version_id' => $version->id,
        ]);

        $this->assertInstanceOf(ChecklistVersion::class, $response->version);
        $this->assertEquals($version->id, $response->version->id);
    }

    public function test_checklist_response_belongs_to_staff(): void
    {
        $staff = Staff::factory()->create();
        $response = ChecklistResponse::factory()->create([
            'staff_id' => $staff->id,
        ]);

        $this->assertInstanceOf(Staff::class, $response->staff);
        $this->assertEquals($staff->id, $response->staff->id);
    }

    public function test_staff_can_have_many_checklist_responses(): void
    {
        $staff = Staff::factory()->create();
        
        ChecklistResponse::factory()->count(3)->create([
            'staff_id' => $staff->id,
        ]);

        $this->assertCount(3, $staff->checklistResponses);
        $this->assertTrue($staff->checklistResponses->every(fn ($r) => $r->staff_id === $staff->id));
    }

    public function test_checklist_response_can_have_many_response_items(): void
    {
        $response = ChecklistResponse::factory()->create();
        
        ChecklistResponseItem::factory()->count(3)->create([
            'checklist_response_id' => $response->id,
        ]);

        $this->assertCount(3, $response->items);
        $this->assertTrue($response->items->every(fn ($i) => $i->checklist_response_id === $response->id));
    }

    public function test_checklist_response_item_belongs_to_response(): void
    {
        $response = ChecklistResponse::factory()->create();
        $item = ChecklistResponseItem::factory()->create([
            'checklist_response_id' => $response->id,
        ]);

        $this->assertInstanceOf(ChecklistResponse::class, $item->response);
        $this->assertEquals($response->id, $item->response->id);
    }

    public function test_checklist_item_can_have_many_response_items(): void
    {
        $item = ChecklistItem::factory()->create();
        
        ChecklistResponseItem::factory()->count(2)->create([
            'checklist_item_id' => $item->id,
        ]);

        $this->assertCount(2, $item->responseItems);
        $this->assertTrue($item->responseItems->every(fn ($ri) => $ri->checklist_item_id === $item->id));
    }

    public function test_checklist_response_item_belongs_to_checklist_item(): void
    {
        $item = ChecklistItem::factory()->create();
        $responseItem = ChecklistResponseItem::factory()->create([
            'checklist_item_id' => $item->id,
        ]);

        $this->assertInstanceOf(ChecklistItem::class, $responseItem->item);
        $this->assertEquals($item->id, $responseItem->item->id);
    }

    public function test_full_relationship_chain(): void
    {
        $staff = Staff::factory()->create();
        $checklist = Checklist::factory()->create();
        $version = ChecklistVersion::factory()->create([
            'checklist_id' => $checklist->id,
        ]);
        
        $items = ChecklistItem::factory()->count(2)->create([
            'checklist_version_id' => $version->id,
        ]);

        $response = ChecklistResponse::factory()->create([
            'checklist_version_id' => $version->id,
            'staff_id' => $staff->id,
        ]);

        foreach ($items as $item) {
            ChecklistResponseItem::factory()->create([
                'checklist_response_id' => $response->id,
                'checklist_item_id' => $item->id,
                'score' => 90,
            ]);
        }

        // Test the full chain
        $this->assertEquals($staff->id, $response->staff->id);
        $this->assertEquals($version->id, $response->version->id);
        $this->assertEquals($checklist->id, $response->version->checklist->id);
        $this->assertCount(2, $response->items);
        
        foreach ($response->items as $responseItem) {
            $this->assertEquals(90, $responseItem->score);
            $this->assertInstanceOf(ChecklistItem::class, $responseItem->item);
        }
    }
}
