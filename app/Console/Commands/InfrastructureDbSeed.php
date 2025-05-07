<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Symfony\Component\Finder\Finder;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Eloquent\Model;

class InfrastructureDbSeed extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infrastructure:db:seed
                {--class= : The class name of the seeder}
                {--database= : The database connection to seed}
                {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with records from Infrastructure seeders';

    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $resolver
     * @return void
     */
    public function __construct(Resolver $resolver)
    {
        parent::__construct();

        $this->resolver = $resolver;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->info('Seeding database...');

        // Get the base path for seeders
        $seedersPath = app_path('infrastructure/Database/Seeders');

        // Get the seeder class
        $seederClass = $this->option('class')
            ? $this->getSeederClass($this->option('class'))
            : $this->getSeederClass('DatabaseSeeder');

        Model::unguarded(function () use ($seederClass) {
            if ($this->option('database')) {
                $this->resolver->setDefaultConnection($this->option('database'));
            }

            $seeder = $this->laravel->make($seederClass);
            $seeder->setContainer($this->laravel)->setCommand($this);
            $seeder->__invoke();
        });

        $this->info('Database seeding completed successfully.');

        return 0;
    }

    /**
     * Get the seeder class name.
     *
     * @param  string  $class
     * @return string
     */
    protected function getSeederClass($class)
    {
        if (str_contains($class, '\\')) {
            return $class;
        }

        $namespace = 'App\\infrastructure\\Database\\Seeders\\';

        return $namespace . $class;
    }

    /**
     * Confirm before proceeding with the action.
     *
     * @return bool
     */
    protected function confirmToProceed()
    {
        if ($this->option('force')) {
            return true;
        }

        if (App::environment('production')) {
            $this->error('This command is not available in production.');
            return false;
        }

        return true;
    }
}
