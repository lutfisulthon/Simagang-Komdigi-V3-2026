<?php

namespace Tests\Feature\Workspace;

use App\Models\FinalReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WorkspaceScenario;
use Tests\TestCase;

class ReportRecapLinkTest extends TestCase
{
    use RefreshDatabase, WorkspaceScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpScenario();

        // Rute laporan akhir dijaga middleware permission:view_reports.
        Permission::firstOrCreate(['name' => 'view_reports', 'guard_name' => 'web']);
        $this->admin->givePermissionTo('view_reports');
    }

    private function report(bool $includeRecap): FinalReport
    {
        return FinalReport::create([
            'intern_id' => $this->ahmad->id,
            'file_path' => 'reports/uji.pdf',
            'file_name' => 'uji.pdf',
            'status' => 'pending',
            'submitted_at' => now(),
            'include_task_recap' => $includeRecap,
        ]);
    }

    public function test_admin_melihat_tautan_rekap_bila_intern_menyertakannya(): void
    {
        $report = $this->report(true);

        $this->actingAs($this->admin)->get(route('admin.report.show', $report))
            ->assertOk()
            ->assertSee('Rekap tugas Workspace disertakan')
            ->assertSee(route('workspace.recap', ['intern_id' => $this->ahmad->id]), false);
    }

    public function test_admin_tidak_melihat_tautan_rekap_bila_tidak_disertakan(): void
    {
        $report = $this->report(false);

        $this->actingAs($this->admin)->get(route('admin.report.show', $report))
            ->assertOk()
            ->assertDontSee('Rekap tugas Workspace disertakan');
    }
}
