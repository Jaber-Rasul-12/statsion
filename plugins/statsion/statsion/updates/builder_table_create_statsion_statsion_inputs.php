<?php namespace Statsion\Statsion\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateStatsionStatsionInputs extends Migration
{
    public function up()
    {
        Schema::create('statsion_statsion_inputs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->double('buying_price', 10, 0);
            $table->double('selling_price', 10, 0);
            $table->double('qt', 10, 0);
            $table->string('currency');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('product_id')
                ->references('id')
                ->on('statsion_statsion_products')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('statsion_statsion_inputs');
    }
}
