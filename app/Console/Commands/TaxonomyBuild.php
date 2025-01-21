<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Taxonomy;
use App\Support\TaxonomyBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TaxonomyBuild extends Command
{
    protected $signature = 'taxonomy:build {--reset}';

    protected $description = 'Build a hierarchical taxonomy tree for categories';

    public function handle()
    {
        if ($this->option('reset')) {
            Schema::disableForeignKeyConstraints();
            DB::table('category_taxonomy')->truncate();
            DB::table('taxonomies')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        new TaxonomyBuilder();
    }
}
