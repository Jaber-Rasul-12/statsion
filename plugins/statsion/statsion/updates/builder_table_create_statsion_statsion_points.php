<?php namespace Statsion\Statsion\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateStatsionStatsionPoints extends Migration
{
    public function up()
    {
        Schema::create('statsion_statsion_points', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('input_id')->unsigned();
            $table->double('qt', 10, 0);
            $table->double('price', 10, 0);
            $table->string('currency');
            $table->integer('product_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('product_id')
                ->references('id')
                ->on('statsion_statsion_products')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('input_id')
                ->references('id')
                ->on('statsion_statsion_inputs')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('statsion_statsion_points');
    }
}
