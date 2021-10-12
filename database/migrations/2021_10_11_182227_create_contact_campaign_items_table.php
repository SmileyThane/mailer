<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactCampaignItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_items', function (Blueprint $table) {
            $table->string('external_service_id')->nullable();
            $table->string('is_responded_from_external_service')->default(false);
        });

        Schema::create('contact_campaign_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_item_id');
            $table->unsignedBigInteger('contact_id');
            $table->string('external_service_id')->nullable();
            $table->string('external_service_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_campaign_items');
    }
}
