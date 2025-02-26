<body>
    <!-- Begin: Header Section -->
    <img class="w-100" src="{{ pdf_header_path(\App\Models\Accounting\Dimension::TADBEER, $contract->type) }}" alt="Header">
    <div class="text-center pt-2">
        <h2 class="font-weight-normal"><span class="pb-3 border-bottom"><?= $title ?> <br> <span lang="ar"><?= $title_ar ?></span></span></h2>
    </div>
    <!-- End: Header Section -->

    <table class="w-100 mt-3">
        <tr>
            <td class="text-right">
                <table class="text-left mb-2">
                    <tr>
                        <td><b>Date - التاريخ</b></td>
                        <td>:</td>
                        <td>{{ $contract->created_at->format(dateformat() . 'h:i A') }}</td>
                    </tr>
                    <tr>
                        <td><b>Reference - مرجع</b></td>
                        <td>:</td>
                        <td>{{ $contract->reference }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="table">
        <tr>
            <td class="border px-2 text-center" style="padding-top: 3.5rem; width: 5%; text-rotate: 90;">
                <b><span lang="ar">First Party - الطرف الأول</span></b>
            </td>
            <td class="align-top border" style="width: 45%;">
                <table class="table table-sm">
                    <tr>
                        <td>
                            <b>
                                Tadbeer Center <br>
                                <span lang="ar">مكتب الاستقدام</span>
                            </b>
                        </td>
                        <td>{{ $company['name'] }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Phone Number <br>
                                <span lang="ar">رقم الهاتف</span>
                            </b>
                        </td>
                        <td>{{ $company['mobile_no'] }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Email <br>
                                <span lang="ar">لبريد اإللكتروني</span>
                            </b>
                        </td>
                        <td>{{ $company['email'] }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Address <br>
                                <span lang="ar">العنوان</span>
                            </b>
                        </td>
                        <td>{{ $company['address'] }}</td>
                    </tr>
                </table>
            </td>
            <td class="border px-2 text-center" style="padding-top: 3rem; width: 5%; text-rotate: 90;">
                <b><span lang="ar">Second Party - الطرف الثاني</span></b>
            </td>
            <td class="align-top border" style="width: 45%;">
                <table class="table table-sm">
                    <tr>
                        <td>
                            <b>
                                Name <br>
                                <span lang="ar">اسم</span>
                            </b>
                        </td>
                        <td>{{ $contract->customer->name }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Nationality <br>
                                <span lang="ar">لجنسية</span>
                            </b>
                        </td>
                        <td>{{ data_get($contract->customer->country, 'name') }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Mobile No. <br>
                                <span lang="ar">رقم الهاتف</span>
                            </b>
                        </td>
                        <td>{{ $contract->customer->mobile }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Emirates ID No. <br>
                                <span lang="ar">رقم الهوية الإماراتية</span>
                            </b>
                        </td>
                        <td>{{ $contract->customer->eid }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Email <br>
                                <span lang="ar">لبريد اإللكتروني</span>
                            </b>
                        </td>
                        <td>{{ $contract->customer->debtor_email }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Address <br>
                                <span lang="ar">العنوان</span>
                            </b>
                        </td>
                        <td>{{ $contract->customer->address }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="w-100 text-center">
        <h4 class="mb-2">Domestic worker - <span lang="ar">العامل المساعد</span></h4>
    </div>
    <table class="table table-sm">
        <tr>
            <td class="align-top border w-50">
                <table class="table table-sm">
                    <tr>
                        <td>
                            <b>
                                Name <br>
                                <span lang="ar">اسم</span>
                            </b>
                        </td>
                        <td>{{ $contract->maid->name }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Gender <br>
                                <span lang="ar">الجنس</span>
                            </b>
                        </td>
                        <td>{{ genders()[$contract->maid->gender] ?? null }}</td>
                    </tr>
                </table>
            </td>
            <td class="align-top border w-50">
                <table class="table table-sm">
                    <tr>
                        <td>
                            <b>
                                Nationality <br>
                                <span lang="ar">جنسية</span>
                            </b>
                        </td>
                        <td>{{ data_get($contract->maid->country, 'name') }}</td>
                    </tr>
                    <tr>
                        <td>
                            <b>
                                Passport No. <br>
                                <span lang="ar">رقم جواز السفر</span>
                            </b>
                        </td>
                        <td>{{ data_get($contract->maid, 'passport_no') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Begin: Terms and conditions -->
    <div class="w-100 text-center mt-3">
        <h4 class="mb-1"><span class="border-bottom pb-2">Terms and Conditions</span></h4>
    </div>
    {!! $terms !!}
    <!-- End: Terms and conditions -->

    <table class="table table-sm table-bordered text-center break-inside-avoid">
        <tr>
            <td colspan="2"><b>Signatures - <span lang="ar">الامضاءات</span></b></td>
        </tr>
        <tr>
            <td class="w-50">
                <b>First Party (Center) - <span lang="ar">الطرف الأول (مركز)&rlm;</span></b>
            </td>
            <td class="w-50">
                <b>Second Party - <span lang="ar">الطرف الثاني</span></b>
            </td>
        </tr>
        <tr>
            <td class="w-50" style="height: 3cm; border-bottom: 0;">&nbsp;</td>
            <td class="w-50" style="height: 3cm; border-bottom: 0;">&nbsp;</td>
        </tr>
        <tr>
            <td class="w-50" style="border-top: 0;">
                <b>{{ strtoupper($company['name']) }}</b>
            </td>
            <td class="w-50" style="border-top: 0;">
                <b>{{ strtoupper($contract->customer->name) }}</b>
            </td>
        </tr>
    </table>
</body>