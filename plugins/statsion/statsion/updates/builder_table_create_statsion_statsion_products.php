<?php namespace Statsion\Statsion\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateStatsionStatsionProducts extends Migration
{
    public function up()
    {
        Schema::create('statsion_statsion_products', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('statsion_statsion_products');
    }
}
