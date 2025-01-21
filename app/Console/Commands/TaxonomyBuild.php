<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Taxonomy;
use App\Support\TaxonomyBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxonomyBuild extends Command
{
    protected $signature = 'taxonomy:build';

    protected $description = 'Build a hierarchical taxonomy tree for categories';

    public function handle()
    {
        new TaxonomyBuilder();
    }
}
