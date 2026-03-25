<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeWorkflowModule extends Command
{
    protected $signature = 'make:workflow-module {name}';

    protected $description = 'Create a new workflow module';

    public function handle()
    {
        $name = $this->argument('name');
        $data = $name;

        $className = Str::studly($name).'Module';
        $data = $className;
        
        File::ensureDirectoryExists(
            app_path('Workflow/Modules')
        );
        $path = app_path("Workflow/Modules/{$className}.php");

        if(File::exists($path)){
            $this->error("Module already exists!");
            return;
        }

        $stub = <<<PHP
<?php

namespace App\Workflow\Modules;

use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;
use Illuminate\Database\Eloquent\Builder;

class {$data} extends BaseWorkflowModule
{

    public function model():string
    {
        // return \App\Models\SalesOrder::class;
    }
    
    /**
     * Validate records before they enter a batch.
     * Useful for checking data integrity or custom business rules.
     */
    public function validate(array $data): void
    {
        // Default: No validation required
    }

    public function statusColumn(): string
    {
        return 'status';
    }

    public function approvedColumn(): string
    {
        return 'approved_at';
    }

   /**
     * Default priorities: check for 'creator', then 'user'.
     * Individual modules can override this.
     */
    public function ownerRelations(): array
    {
        return [
            //'creator', 'user'
        ];
    }

    public function customRelations(): array
    {
        return [
            //'items', 'attachments'
        ]; // Extra stuff they need
    }
    public function selectColumns(): array
    {
         return [
            // 'id', 'user_id', 'reference_id', 'stage', 'stage_status', 'status', 'approved_at',
        ];
    }

    public function displayColumns(): array
    {
        return [
            // 'order_no' => 'Order No',
            // 'amount' => 'Amount'
        ];
    }

    public function relationModels(): array
    {
        return [
            'user' => \ApurbaLabs\ApprovalEngine\Tests\Support\Models\User::class,
            //'admin' => \App\Models\Admin::class,
        ];
    }

    public function query(): Builder
    {
        return \$this->model()::query();
    }

}
PHP;

        File::put($path,$stub);

        $this->info("Workflow module created: {$data}");
    }
}
