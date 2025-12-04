<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->string('account_code', 20)->change();
            $table->index('account_code');
            $table->foreign('account_code')
                  ->references('code')
                  ->on('chart_of_accounts')
                  ->onUpdate('cascade') 
                  ->onDelete('restrict'); 
        });
    }

    public function down()
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['account_code']);
            $table->dropIndex(['account_code']);
            $table->string('account_code', 191)->change(); 
        });
    }
};