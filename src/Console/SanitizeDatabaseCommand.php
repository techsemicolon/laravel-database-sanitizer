<?php

namespace Techsemicolon\Sanitizer\Console;

use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class SanitizeDatabaseCommand extends Command
{
    private $faker, $database, $info, $tables, $models, $truncates;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sanitize {--info} {--truncate} {--tables=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sanitize database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = Factory::create();
        $this->models = config('sanitizer.sanitize_models');
        $this->truncates = config('sanitizer.truncate_models');
        $this->database = config('database.connections.mysql.host');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(PHP_EOL);
        $this->info = $this->option('info');
        $this->tables = trim($this->option('tables'));
        $this->tables = array_filter(explode(',', $this->option('tables')));
        
        if(!$this->info){
            $this->warn("Note : Info you want to know which tables will be sanitized before running the command, run command with --info option");
        }

        if (!$this->info && !$this->confirm("Command will sanitize the data on endpoint: {$this->database}, do you wish to continue?")) {
            return $this->info('Aborted...');
        }
        
        if(empty($this->models)){
            return $this->warn('No models specified to be sanitized...');
        }

        if($this->info){
            return $this->showInfo();
        }

        Schema::disableForeignKeyConstraints();

        if(!$this->option('truncate')){
            $this->initiateSanitization();
        }

        $this->truncate();

        Schema::enableForeignKeyConstraints();

        $this->info(PHP_EOL);
    }

    /**
     * Truncate the tables.
     *
     * @return mixed
     */
    private function truncate()
    {
        collect($this->truncates)->each(function($model){
            // Skip if class does not exist
            if(!class_exists($model)){
                $this->warn("Class {$model} does not exist, skipping...");
                return;
            }
            
            $model = $this->getModel($model);
            $table = $model->getTable();

            $this->info('Truncating table : ' . $table);
            DB::table($table)->truncate();
        });
    }

    /**
     * Initiate the sanitization
     * 
     * @return void
     */
    private function initiateSanitization()
    {
        collect($this->models)->each(function($model){
            // Skip if class does not exist
            if(!class_exists($model)){
                $this->warn("Class {$model} does not exist, skipping...");
                return;
            }
            
            $model = $this->getModel($model);
            $table = $model->getTable();

            if(!empty($this->tables) && !in_array($table, $this->tables)){
                return;
            }

            $this->info('Sanitizing table : ' . $table);
            $this->sanitizeModel($model);
        });
    }


    /**
     * Sanitize the tables
     * 
     * @param Model $model
     * 
     * @return void
     */
    private function sanitizeModel(Model $model)
    {
        if(!method_exists($model, 'sanitize')){
            return $this->warn("Class {$model} does not contain method sanitize(), skipping...");
        }
        
        $sanitizer = collect($model->sanitize());
        $bar = $this->output->createProgressBar($model->count());
        $bar->start();
  
        $model->chunk(config('sanitizer.chunk_count', 1000), function($rows) use ($sanitizer, $bar){
            
            $rows->each(function($row) use($sanitizer, $bar){
                
                // Update each record with fresh dummy value
                $row->update($sanitizer->map(function($dummyValue) use($row){
                    
                    // If it's a closure then call it, otherwise return the value as is
                    return is_callable($dummyValue) ? call_user_func($dummyValue, $row, $this->faker) : $dummyValue;

                })->toArray());

                $bar->advance();

            });
        });

        $bar->finish();
        $this->info(PHP_EOL);
    }

    /**
     * Show information about models being sanitized
     * 
     * @return void
     */
    private function showInfo()
    {
        $this->warn("Command will sanitize the data on endpoint: {$this->database}");
        $this->info('Sanitizer will update following tables:');

        collect($this->models)->each(function($model){
            
            // Skip if class does not exist
            if(!class_exists($model)){
                $this->warn("Class {$model} does not exist, skipping...");
                return;
            }

            $model = $this->getModel($model);
            $this->info('- ' . $model->getTable());
        });   
        
        $this->info('Sanitizer will truncate following tables:');

        collect($this->truncates)->each(function($model){
            
            // Skip if class does not exist
            if(!class_exists($model)){
                $this->warn("Class {$model} does not exist, skipping...");
                return;
            }

            $model = $this->getModel($model);
            $this->info('- ' . $model->getTable());
        });   
    }

    /**
     * Get model instance from class
     * 
     * @param string $modelClass
     * 
     * @return Model
     */
    private function getModel(string $modelClass)
    {
        return app()->make($modelClass);
    }
}
