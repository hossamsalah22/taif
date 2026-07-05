<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('make:service')]
#[Description('Create a new service class')]
class MakeService extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Service name (e.g., PaymentService)');

        $directory = app_path('Services');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory.'/'.$name.'.php';

        if (file_exists($filePath)) {
            $this->error('Service already exists!');

            return;
        }

        $stub = "<?php

namespace App\Services;

class $name
{
    // Service methods go here
}

";

        file_put_contents($filePath, $stub);

        $this->info("Service $name created successfully at $filePath");
    }
}
