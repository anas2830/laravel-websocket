<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEduCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('edu_courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_name');
            $table->string('course_thumb');
            $table->text('course_overview');
            $table->integer('total_enrolled')->default(0);
            $table->tinyInteger('certificate_config')->default(0)->comment = '1=Yes, 0=No';
            $table->string('certificate_name')->nullable();
            $table->tinyInteger('publish_status')->default(1)->comment = '1=Yes, 0=No';
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();
            $table->tinyInteger('valid')->comment = '1=Yes, 0=No';

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('edu_courses');
    }
}
