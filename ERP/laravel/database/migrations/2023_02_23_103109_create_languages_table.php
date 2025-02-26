<?php

use App\Models\Language;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name')->nullable();
        });

        Language::insert([
            [
                'code' => 'AF',
                'name' => 'Afrikaans'
            ],
            [
                'code' => 'SQ',
                'name' => 'Albanian'
            ],
            [
                'code' => 'AR-DZ',
                'name' => 'Arabic (Algeria)'
            ],
            [
                'code' => 'AR-BH',
                'name' => 'Arabic (Bahrain)'
            ],
            [
                'code' => 'AR-EG',
                'name' => 'Arabic (Egypt)'
            ],
            [
                'code' => 'AR-IQ',
                'name' => 'Arabic (Iraq)'
            ],
            [
                'code' => 'AR-JO',
                'name' => 'Arabic (Jordan)'
            ],
            [
                'code' => 'AR-KW',
                'name' => 'Arabic (Kuwait)'
            ],
            [
                'code' => 'AR-LB',
                'name' => 'Arabic (Lebanon)'
            ],
            [
                'code' => 'AR-LY',
                'name' => 'Arabic (Libya)'
            ],
            [
                'code' => 'AR-MA',
                'name' => 'Arabic (Morocco)'
            ],
            [
                'code' => 'AR-OM',
                'name' => 'Arabic (Oman)'
            ],
            [
                'code' => 'AR-QA',
                'name' => 'Arabic (Qatar)'
            ],
            [
                'code' => 'AR-SA',
                'name' => 'Arabic (Saudi Arabia)'
            ],
            [
                'code' => 'AR-SY',
                'name' => 'Arabic (Syria)'
            ],
            [
                'code' => 'AR-TN',
                'name' => 'Arabic (Tunisia)'
            ],
            [
                'code' => 'AR-AE',
                'name' => 'Arabic (U.A.E.)'
            ],
            [
                'code' => 'AR-YE',
                'name' => 'Arabic (Yemen)'
            ],
            [
                'code' => 'EU',
                'name' => 'Basque'
            ],
            [
                'code' => 'BE',
                'name' => 'Belarusian'
            ],
            [
                'code' => 'BG',
                'name' => 'Bulgarian'
            ],
            [
                'code' => 'CA',
                'name' => 'Catalan'
            ],
            [
                'code' => 'ZH-HK',
                'name' => 'Chinese (Hong Kong)'
            ],
            [
                'code' => 'ZH-CN',
                'name' => 'Chinese (PRC)'
            ],
            [
                'code' => 'ZH-SG',
                'name' => 'Chinese (Singapore)'
            ],
            [
                'code' => 'ZH-TW',
                'name' => 'Chinese (Taiwan)'
            ],
            [
                'code' => 'HR',
                'name' => 'Croatian'
            ],
            [
                'code' => 'CS',
                'name' => 'Czech'
            ],
            [
                'code' => 'DA',
                'name' => 'Danish'
            ],
            [
                'code' => 'NL-BE',
                'name' => 'Dutch (Belgium)'
            ],
            [
                'code' => 'NL',
                'name' => 'Dutch (Standard)'
            ],
            [
                'code' => 'EN',
                'name' => 'English'
            ],
            [
                'code' => 'EN-AU',
                'name' => 'English (Australia)'
            ],
            [
                'code' => 'EN-BZ',
                'name' => 'English (Belize)'
            ],
            [
                'code' => 'EN-CA',
                'name' => 'English (Canada)'
            ],
            [
                'code' => 'EN-IE',
                'name' => 'English (Ireland)'
            ],
            [
                'code' => 'EN-JM',
                'name' => 'English (Jamaica)'
            ],
            [
                'code' => 'EN-NZ',
                'name' => 'English (New Zealand)'
            ],
            [
                'code' => 'EN-ZA',
                'name' => 'English (South Africa)'
            ],
            [
                'code' => 'EN-TT',
                'name' => 'English (Trinidad)'
            ],
            [
                'code' => 'EN-GB',
                'name' => 'English (United Kingdom)'
            ],
            [
                'code' => 'EN-US',
                'name' => 'English (United States)'
            ],
            [
                'code' => 'ET',
                'name' => 'Estonian'
            ],
            [
                'code' => 'FO',
                'name' => 'Faeroese'
            ],
            [
                'code' => 'FA',
                'name' => 'Farsi'
            ],
            [
                'code' => 'FI',
                'name' => 'Finnish'
            ],
            [
                'code' => 'FR-BE',
                'name' => 'French (Belgium)'
            ],
            [
                'code' => 'FR-CA',
                'name' => 'French (Canada)'
            ],
            [
                'code' => 'FR-LU',
                'name' => 'French (Luxembourg)'
            ],
            [
                'code' => 'FR',
                'name' => 'French (Standard)'
            ],
            [
                'code' => 'FR-CH',
                'name' => 'French (Switzerland)'
            ],
            [
                'code' => 'GD',
                'name' => 'Gaelic (Scotland)'
            ],
            [
                'code' => 'DE-AT',
                'name' => 'German (Austria)'
            ],
            [
                'code' => 'DE-LI',
                'name' => 'German (Liechtenstein)'
            ],
            [
                'code' => 'DE-LU',
                'name' => 'German (Luxembourg)'
            ],
            [
                'code' => 'DE',
                'name' => 'German (Standard)'
            ],
            [
                'code' => 'DE-CH',
                'name' => 'German (Switzerland)'
            ],
            [
                'code' => 'EL',
                'name' => 'Greek'
            ],
            [
                'code' => 'HE',
                'name' => 'Hebrew'
            ],
            [
                'code' => 'HI',
                'name' => 'Hindi'
            ],
            [
                'code' => 'HU',
                'name' => 'Hungarian'
            ],
            [
                'code' => 'IS',
                'name' => 'Icelandic'
            ],
            [
                'code' => 'ID',
                'name' => 'Indonesian'
            ],
            [
                'code' => 'GA',
                'name' => 'Irish'
            ],
            [
                'code' => 'IT',
                'name' => 'Italian (Standard)'
            ],
            [
                'code' => 'IT-CH',
                'name' => 'Italian (Switzerland)'
            ],
            [
                'code' => 'JA',
                'name' => 'Japanese'
            ],
            [
                'code' => 'KO',
                'name' => 'Korean'
            ],
            [
                'code' => 'KO',
                'name' => 'Korean (Johab)'
            ],
            [
                'code' => 'KU',
                'name' => 'Kurdish'
            ],
            [
                'code' => 'LV',
                'name' => 'Latvian'
            ],
            [
                'code' => 'LT',
                'name' => 'Lithuanian'
            ],
            [
                'code' => 'MK',
                'name' => 'Macedonian (FYROM)'
            ],
            [
                'code' => 'ML',
                'name' => 'Malayalam'
            ],
            [
                'code' => 'MS',
                'name' => 'Malaysian'
            ],
            [
                'code' => 'MT',
                'name' => 'Maltese'
            ],
            [
                'code' => 'NO',
                'name' => 'Norwegian'
            ],
            [
                'code' => 'NB',
                'name' => 'Norwegian (Bokmål)'
            ],
            [
                'code' => 'NN',
                'name' => 'Norwegian (Nynorsk)'
            ],
            [
                'code' => 'PL',
                'name' => 'Polish'
            ],
            [
                'code' => 'PT-BR',
                'name' => 'Portuguese (Brazil)'
            ],
            [
                'code' => 'PT',
                'name' => 'Portuguese (Portugal)'
            ],
            [
                'code' => 'PA',
                'name' => 'Punjabi'
            ],
            [
                'code' => 'RM',
                'name' => 'Rhaeto-Romanic'
            ],
            [
                'code' => 'RO',
                'name' => 'Romanian'
            ],
            [
                'code' => 'RO-MD',
                'name' => 'Romanian (Republic of Moldova)'
            ],
            [
                'code' => 'RU',
                'name' => 'Russian'
            ],
            [
                'code' => 'RU-MD',
                'name' => 'Russian (Republic of Moldova)'
            ],
            [
                'code' => 'SR',
                'name' => 'Serbian'
            ],
            [
                'code' => 'SK',
                'name' => 'Slovak'
            ],
            [
                'code' => 'SL',
                'name' => 'Slovenian'
            ],
            [
                'code' => 'SB',
                'name' => 'Sorbian'
            ],
            [
                'code' => 'ES-AR',
                'name' => 'Spanish (Argentina)'
            ],
            [
                'code' => 'ES-BO',
                'name' => 'Spanish (Bolivia)'
            ],
            [
                'code' => 'ES-CL',
                'name' => 'Spanish (Chile)'
            ],
            [
                'code' => 'ES-CO',
                'name' => 'Spanish (Colombia)'
            ],
            [
                'code' => 'ES-CR',
                'name' => 'Spanish (Costa Rica)'
            ],
            [
                'code' => 'ES-DO',
                'name' => 'Spanish (Dominican Republic)'
            ],
            [
                'code' => 'ES-EC',
                'name' => 'Spanish (Ecuador)'
            ],
            [
                'code' => 'ES-SV',
                'name' => 'Spanish (El Salvador)'
            ],
            [
                'code' => 'ES-GT',
                'name' => 'Spanish (Guatemala)'
            ],
            [
                'code' => 'ES-HN',
                'name' => 'Spanish (Honduras)'
            ],
            [
                'code' => 'ES-MX',
                'name' => 'Spanish (Mexico)'
            ],
            [
                'code' => 'ES-NI',
                'name' => 'Spanish (Nicaragua)'
            ],
            [
                'code' => 'ES-PA',
                'name' => 'Spanish (Panama)'
            ],
            [
                'code' => 'ES-PY',
                'name' => 'Spanish (Paraguay)'
            ],
            [
                'code' => 'ES-PE',
                'name' => 'Spanish (Peru)'
            ],
            [
                'code' => 'ES-PR',
                'name' => 'Spanish (Puerto Rico)'
            ],
            [
                'code' => 'ES',
                'name' => 'Spanish (Spain)'
            ],
            [
                'code' => 'ES-UY',
                'name' => 'Spanish (Uruguay)'
            ],
            [
                'code' => 'ES-VE',
                'name' => 'Spanish (Venezuela)'
            ],
            [
                'code' => 'SV',
                'name' => 'Swedish'
            ],
            [
                'code' => 'SV-FI',
                'name' => 'Swedish (Finland)'
            ],
            [
                'code' => 'TH',
                'name' => 'Thai'
            ],
            [
                'code' => 'TS',
                'name' => 'Tsonga'
            ],
            [
                'code' => 'TN',
                'name' => 'Tswana'
            ],
            [
                'code' => 'TR',
                'name' => 'Turkish'
            ],
            [
                'code' => 'UA',
                'name' => 'Ukrainian'
            ],
            [
                'code' => 'UR',
                'name' => 'Urdu'
            ],
            [
                'code' => 'VE',
                'name' => 'Venda'
            ],
            [
                'code' => 'VI',
                'name' => 'Vietnamese'
            ],
            [
                'code' => 'CY',
                'name' => 'Welsh'
            ],
            [
                'code' => 'XH',
                'name' => 'Xhosa'
            ],
            [
                'code' => 'JI',
                'name' => 'Yiddish'
            ],
            [
                'code' => 'ZU',
                'name' => 'Zulu'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_languages');
    }
}
