<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;

class AddNewLanguagesToLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Language::insert([
            [
                "code" => null,
                "name" => "Oromo",
            ],
            [
                "code" => null,
                "name" => "Amharic",
            ],
            [
                "code" => null,
                "name" => "Somali",
            ],
            [
                "code" => null,
                "name" => "Tigrinya",
            ],
            [
                "code" => null,
                "name" => "Sidama",
            ],
            [
                "code" => null,
                "name" => "Wolaytta",
            ],
            [
                "code" => null,
                "name" => "Sebat Bet Gurage",
            ],
            [
                "code" => null,
                "name" => "Afar",
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Language::whereIn('name', [
            'Oromo',
            'Amharic',
            'Somali',
            'Tigrinya',
            'Sidama',
            'Wolaytta',
            'Sebat Bet Gurage',
            'Afar',
        ])->delete();
    }
}
