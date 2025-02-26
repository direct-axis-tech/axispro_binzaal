<body style="margin-top: 0;">
    <img class="w-100" src="<?= pdf_header_path() ?>">
    <h2 class="my-4">Application For Employment</h2>
    <table class="w-100">
        <tr>
            <td class="align-top">
                <table class="table-bordered w-100 table-md">
                    <tbody>
                        <tr>
                            <td><b>Reference No</b></td>
                            <td>{{ $labour['id'] }}</td>
                            <td class="text-right" lang="ar"><b>رقم المرجع</b></td>
                        </tr>
                        <tr>
                            <td><b>Post Applied For</b></td>
                            <td>{{ $labour_types[$labour['job_type']] ?? '' }}</td>
                            <td class="text-right" lang="ar"><b>الوظيفة المتقدم عليها</b></td>
                        </tr>
                        <tr>
                            <td><b>Monthly Salary</b></td>
                            <td>{{ $labour['salary'] ?: '' }}</td>
                            <td class="text-right" lang="ar"><b>راتب شهري</b></td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="width: 120px">
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td class="border"><img style="height: 120px;" src="{{ $passportPhoto }}" alt="Passport Photo"></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table class="w-100 my-3">
        <tr>
            <td>
                <table class="w-100 table-md border">
                    <tr>
                        <td class="text-left"><b>NAME IN FULL:</b></td>
                        <td class="text-center"><b>{{ $labour['name'] }}</b></td>
                        <td class="text-right"><b>الاسم بالكامل</b></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="mt-2 w-100">
        <tr>
            <td style="width: 60%" class="align-top">
                <table class="w-100">
                    <tr>
                        <td>
                            <table class="w-100 border table-sm">
                                <thead>
                                    <tr class="border" style="background-color: #140c8e;">
                                        <td style="color: white"><b>Display Of Application</b></td>
                                        <td>&nbsp;</td>
                                        <td class="text-right" style="color: white"><b><span lang="ar">بيانات صاحب الطل</span></b></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Nationality</b></td>
                                        <td> {{ $labour['country_name'] }}</td>
                                        <td class="text-right"><span lang="ar"><b>الجنسية</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Religion</b></td>
                                        <td>{{ $labour['religion_name'] }}</td>
                                        <td class="text-right"><span lang="ar"><b>الديانة</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Date Of Birth</b></td>
                                        <td>{{ $labour['dob'] ? \Carbon\Carbon::parse($labour['dob'])->format(dateformat()) : '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>تاريخ الميلاد</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Place of birth</b></td>
                                        <td>{{ $labour['place_of_birth'] ?: '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>د مكان</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Age</b></td>
                                        <td>{{ $labour['computed_age'] }}</td>
                                        <td class="text-right"><span lang="ar"><b>عمر</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Living Town</b></td>
                                        <td>{{ $labour['formatted_locations'] }}</td>
                                        <td class="text-right"><span lang="ar"><b>مدينة المعيشة</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Marital Status</b></td>
                                        <td>{{ $marital_statuses[$labour['marital_status']] ?? '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>الحالة الاجتماعية</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>No Of Children</b></td>
                                        <td>{{ $labour['no_of_children'] ?: '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>عدد الأطفال</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Weight</b></td>
                                        <td>{{ $labour['weight'] ?: '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>وزن</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Height</b></td>
                                        <td>{{ $labour['height'] ?: '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>ارتفاع</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Education Level</b></td>
                                        <td>{{ $labour['education'] ?: '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>مستوى التعليم</b></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Address</b></td>
                                        <td>{{ $labour['address'] ?: '' }}</td>
                                        <td class="text-right"><span lang="ar"><b>عنوان</b></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="mt-2 w-100 border table-sm">
                                <thead>
                                    <tr class="border" style="background-color: #140c8e;">
                                        <td style="color: white"><b>Language</b></td>
                                        <td style="width: 70%; color: white" class="text-center"><b>Proficiency</b></td>
                                    </tr>
                                </thead>
                                <tbody>
                                @if (!empty($labour['languages']))
                                    @foreach ($labour['languages'] as $l)
                                    <tr>
                                        <td>{{ $languages[$l['id']] }}</td>
                                        <td class="text-center">
                                            {{ $proficiencies_en[$l['proficiency']] ?? '' }} -
                                            <span lang="ar">{{ $proficiencies_ar[$l['proficiency']] ?? '' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2" class="text-center">-</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="mt-2 w-100 border">
                                <thead>
                                    <tr class="border" style="background-color: #140c8e;">
                                        <td style="color: white"><b>Work Experience</b></td>
                                        <td>&nbsp;</td>
                                        <td class="text-right" style="color: white"><span lang="ar"><b>خبرة العمل</b></span></td>
                                    </tr>
                                </thead>
                                <tbody>
                                @if (!empty($labour['skills']))
                                    @foreach ($labour['skills'] as $s)
                                    <tr>
                                        <td>{{ $skills_en[$s] ?? '' }}</td>
                                        <td style="color: green">Yes</td>
                                        <td class="text-right"><span lang="ar">{{ $skills_ar[$s] ?? '' }}</span></td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center">-</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>

            <td width="40%" class="align-top">
                <table class="w-100 border table-sm">
                    <thead>
                        <tr class="border" style="background-color: #140c8e;">
                            <td style="color: white"><b>Passport Details</b></td>
                            <td>&nbsp;</td>
                            <td class="text-right" style="color: white"><b><span lang="ar">بيانات صاحب الطل</span></b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>Number</b></td>
                            <td>{{ $passport->reference ?: '' }}</td>
                            <td class="text-right"><span lang="ar"><b>رقم</b></span></td>
                        </tr>
                        <tr>
                            <td><b>Issue Date</b></td>
                            <td>{{ $passport->issued_on ? \Carbon\Carbon::parse($passport->issued_on)->format(dateformat()) : '' }}</td>
                            <td class="text-right"><span lang="ar"><b>تاريخ الإصدار</b></span></td>
                        </tr>
                        <tr>
                            <td><b>Place</b></td>
                            <td>{{ $passport->context['issue_place'] ?? '' }}</td>
                            <td class="text-right"><span lang="ar"><b>مكان</b></span></td>
                        </tr>
                        <tr>
                            <td><b>Exp Date</b></td>
                            <td>{{ $passport->expires_on ? \Carbon\Carbon::parse($passport->expires_on)->format(dateformat()) : '' }}</td>
                            <td class="text-right"><span lang="ar"><b>تاريخ انتهاء الصلاحية</b></span></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="w-100 text-center">
                                <img src="{{ $fullSizePhoto }}" alt="" style="height: 100mm; width: 40mm">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div style="height: 35mm;"></div>
    <div class="fixed-bottom border-top border-white w-100">
        <img class="w-100" src="<?= pdf_footer_path() ?>" alt="">
    </div>
</body>