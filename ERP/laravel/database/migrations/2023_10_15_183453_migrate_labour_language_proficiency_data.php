<?php

use App\Models\Labour\Labour;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class MigrateLabourLanguageProficiencyData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $labours = Labour::query()
                ->select('id', 'languages')
                ->whereRaw("JSON_TYPE(JSON_UNQUOTE(JSON_EXTRACT(`languages`, '$[0]'))) != 'OBJECT'")
                ->whereRaw("JSON_LENGTH(`languages`) > 0")
                ->lockForUpdate()
                ->get();
            
            $languageProficiencies = array_keys(language_proficiencies());
            $proficiency = next($languageProficiencies);

            foreach ($labours as $labour) {
                $labour->languages = array_map(function ($id) use ($proficiency) {
                    return compact('id', 'proficiency');
                }, $labour->languages);
                $labour->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            $labours = Labour::query()
                ->select('id', 'languages')
                ->whereRaw("JSON_TYPE(JSON_UNQUOTE(JSON_EXTRACT(`languages`, '$[0]'))) = 'OBJECT'")
                ->whereRaw("JSON_LENGTH(`languages`) > 0")
                ->lockForUpdate()
                ->get();

            foreach ($labours as $labour) {
                $labour->languages = array_map(function ($knownLanguage) {
                    return (string)$knownLanguage['id'];
                }, $labour->languages);
                $labour->save();
            }
        });
    }
}
