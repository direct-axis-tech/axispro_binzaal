<table class="table table-sm">
    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 1: Subject of the Contract</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند الأول : موضوع العقد</b>
        </td>
    </tr>
    <tr>
        <td class="w-50">
            The two parties agreed that the First Party shall provide a domestic worker through the Probation package system.
            and provide such service to the Second Party according to such assignment by the Second Party and within the 
        scope of the Assistant's profession as ({{ labour_types()[$contract->maid->type] ?? '-' }}) The worker details are listed below:
        </td>
        <td class="w-50" style="direction: rtl">
            اتفق الطرفان على أن يقوم الطرف األول بتوفير عامل خدمة مساعدة بنظام تشغيل الباقة التجريبية وتقديم خدماته للطرف الثاني ، وفقا لما يكل 
            حدود مجال عمله بمهنة ({{ labour_types('ar')[$contract->maid->type] ?? '-' }}) ، وبيانات العامل هي :
        </td>
    </tr>

    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 2: Contract Team</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند الثاني : فترة التجربة</b>
        </td>
    </tr>
    <tr>
        <td class="w-50">
            The duration of this contract shall not be less than(3) months and not more than maximum period of (6) months, starting 
            from ({{ $contract->contract_from->format(dateformat()) }}), and ending on ({{ $contract->contract_till->format(dateformat()) }}).
        </td>
        <td class="w-50" style="direction: rtl">
            ولا تقل مدة هذا العقد عن (3) أشهر ولا تزيد على (6) أشهر كحد أقصى ابتداءً من ذلك
            من ({{ $contract->contract_from->format(dateformat()) }})، وينتهي في ({{ $contract->contract_till->format(dateformat()) }}).
        </td>
    </tr>

    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 3: Contract Value</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند الثاني : فترة التجربة</b>
        </td>
    </tr>
    <tr>
        <td class="w-50">
            The Second Party shall undertake to pay the First Party for the agreed duration a monthly amount of (AED {{ $contract->installment_amount }}) per month. 
            The Second Party shall also be obliged to pay a lump sum of ( AED {{ $contract->order->total }} ) in case it is agreed to transfer the 
            sponsorship of the worker to the employer, this can be made on the condition that the worker has completed more than
            three months with his/her employer.
        </td>
        <td class="w-50" style="direction: rtl">
            يتعهد الطرف الثاني بأن يدفع للطرف الأول عن المدة المتفق عليها مبلغاً شهرياً قدره (AED {{ $contract->installment_amount }}) شهرياً. 
            كما يلتزم الطرف الثاني بدفع مبلغ مقطوع قدره ( AED {{ $contract->order->total }}) في حالة الاتفاق على نقل الملكية
            كفالة العامل لصاحب العمل، ويمكن أن يتم ذلك بشرط أن يكون العامل قد أكمل أكثر من ثلاثة أشهر مع صاحب العمل.
        </td>
    </tr>

    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 4: First Party Obligations</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند الثالث : التزامات الطرف الأول</b>
        </td>
    </tr>

    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>The First Party is obligated to:</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>يلتزم الطرف األول باآلتي:</b>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table class="w-100 table-sm">
                <tr>
                    <td class="text-right" style="width: 5px;">1.</td>
                    <td style="width: 328px">
                        Provide the services of domestic worker, as mentioned above, to the Second Party.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        تقديم خدمات العامل المذكور أعاله للطرف الثاني.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
                </tr>

                <tr>
                    <td class="text-right" style="width: 5px;">2.</td>
                    <td style="width: 328px">
                        Provide medical documents proving the worker’s physical fitness and his mental and occupational aptitude to perform the job.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        ضمان ما يثبت لياقة العامل و حالته الصحية و النفسية و المهنية للعمل المطلوب القيام به.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">2.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">3.</td>
                    <td style="width: 328px">
                        Fully ensure that the worker fulfills the conditions and requirements, as required by the legal regulations in force within 
                        the UAE, to practice a particular profession, job or particular duty.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        أن يضمن تماما توافر الشروط و المستلزمات، التي تحددها النظم القانونية المعمول بها داخل دولة اإلمارات، في العامل لممارسة مهنة أو وظيفة أمعين.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">4.</td>
                    <td style="width: 328px">
                        Provide a substitute worker with the same qualifications and expertise to perform the same job for which the domestic 
                        worker was requested, at the request of the Second Party at any time. The replacement can also be made in the case of the 
                        principal worker’s absenteeism or refusal to work, within 48 hours from the time the Second Party notifies the First Party.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        توفير عامل بديل عن العامل المقدم خدماته بنفس المؤهالت و الخبرات ، للقيام بنفس العمل الذي طلب من أجله ، و ذلك بناء على رغبة الطرف الث
                        وقت أو في حالة تغيب العامل عن العمل أو رفضه للعمل ، وذلك خالل 48 ساعة من وقت إبالغ الطرف الثاني للطرف األول بذلك.
                    </td>    
                    <td class="text-right" style="direction: rtl; width: 5px;">4.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">5.</td>
                    <td style="width: 328px">
                        The First Party shall not replace the worker assigned, unless written approval was received from the Second Party.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        عدم استبدال العامل بنظام الباقة التجريبية إال بعد أخذ الموافقة الكتابية من الطرف الثاني.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">5.</td>
                </tr>
                    
                <tr>
                    <td class="text-right" style="width: 5px;">6.</td>
                    <td style="width: 328px">
                        The Second Party shall be compensated for any loss, damage or destruction of his property caused by the domestic 
                        worker , after being proven by the competent authorities
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        تعويض الطرف الثاني مقابل ما يتسبب العامل في فقده أو إتالفه أو تدميره من ممتلكات الطرف الثاني بعد ثبوت ذلك من الجهات المختصة.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">6.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">7.</td>
                    <td style="width: 328px">
                        The Second Party has the right to replace the worker with another whenever necessary and only twice during the contract period.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        استبدال العامل بعامل أخر للطرف الثاني كلما دعت حاجته لذلك و مرتين فقط خالل فترة التقاعد.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">7.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">8.</td>
                    <td style="width: 328px">
                        Provide all the requirements imposed by the legal systems in force in the Ministry of Human Resources and 
                        Emiratisation relating to the employer, unless the Parties agreed otherwise. In all cases, not withstanding such contracts 
                        between the First and Second Parties; the First Party shall not be exempted from Liability in the event that the beneficiary 
                        fails to fulfill his or her obligation, and the abstaining beneficiary shall assume legal responsibility.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        توفير كل ما تفرضه النظم القانونية المعمول بها في الوزارة و متعلقة بذات الشأن على صاحب العمل، ما لم يتفق الطرفان على خالف ذلك. وفي جا
                        ألحوال ال يعفي الطرف األول من المسؤولية في حال امتناع المستفيد عن الوفاء بما التزم به ، و مع تحميل المستفيد الممتنع المسؤولية القانونية.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">8.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">9.</td>
                    <td style="width: 328px">
                        Any other obligations imposed by the relevant legal regulations as applicable in the Ministry.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        التزامات أخرى تفرضها عليه النظم القانونية ذات العالقة و المعمول بها في الوزارة.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">9.</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 5: Second Party Obligations</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند الثالث : التزامات الطرف الأول</b>
        </td>
    </tr>

    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>The Second Party is obligated to:</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>يلتزم الطرف الثاني باآلتي:</b>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table class="w-100 table-sm">
                <tr>
                    <td class="text-right" style="width: 5px;">1.</td>
                    <td style="width: 328px">
                        To pay contract price agreed upon in the third term and providing guarantees for the duration of contract and the agreed lump sum.

                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        سداد القيمة المتفق عليها في البند الثالث باإلضافة لتقديم الضمانات المقررة في هذا الشأن عن مدة العقد باإلضافة للمبلغ المقطوع المتفق عليه.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
                </tr>

                <tr>
                    <td class="text-right" style="width: 5px;">2.</td>
                    <td style="width: 328px">
                        Treat the worker in a good way that preserves his/ her dignity and wellbeing.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        معاملة العامل معاملة حسنة تحفظ كرامته وسالمة بدنه.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">2.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">3.</td>
                    <td style="width: 328px">
                        Provide an adequate accommodation for the worker.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        تهيئة مكان الئق لسكن العامل.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">4.</td>
                    <td style="width: 328px">
                        Notify the First Party of any violations or errors committed by the worker to take the necessary action against him/her.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        إشعار الطرف األول بأية مخالفات ، أو أخطاء يرتكبها العامل التخاذ ما يلزم من إجراءات بحقه.
                    </td>    
                    <td class="text-right" style="direction: rtl; width: 5px;">4.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">5.</td>
                    <td style="width: 328px">
                        The worker shall not be assigned to work in a profession that is different from the nature of his/her work, except upon 
                        his/her consent and provided that such profession is sanctioned by Law.

                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        عدم تشغيل العامل بمهنة تختلف عن طبيعة عمله إال برضاه و بشرط أن تكون من المهن المشمولة بالقانون.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">5.</td>
                </tr>
                    
                <tr>
                    <td class="text-right" style="width: 5px;">6.</td>
                    <td style="width: 328px">
                        To notify the First Party of the worker’s interruption or refusal to work within (24) hours and handing over the First 
                        Party all belongings of the worker.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        إبالغ الطرف األول في حال انقطاع العامل عن العمل أو رفضه للعمل خالل (24) ساعة من وقت االنقطاع أو رفض العامل مع تسليم جميع متعلق للطرف األول.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">6.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">7.</td>
                    <td style="width: 328px">
                        To provide worker with workplace environment and necessary tools in accordance with effective legal systems in the country.

                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        توفير بيئة و أدوات العمل للعامل بما يتوافق مع األنظمة القانونية المعمول بها في الدولة.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">7.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">8.</td>
                    <td style="width: 328px">
                        The worker shall not work for a third parties.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        عدم تشغيل العامل لدى الغير.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">8.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">9.</td>
                    <td style="width: 328px">
                        To provide worker all that have been agreed upon with the First Party.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        توفير كافة ما انفق عليه مع الطرف األول للعامل.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">9.</td>
                </tr>

                <tr>
                    <td class="text-right" style="width: 5px;">10.</td>
                    <td style="width: 328px">
                        The Second Party shall pay a monthly wage of (AED {{ $contract->no_of_installments ? price_format($contract->order->total/$contract->no_of_installments) : '-' }} ) after the end of the probation period in case of transferring the 
                        worker’s sponsorship to the Second Party.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        يلتزم الطرف الثاني بدفع أجر شهري و قدره ({{ $contract->no_of_installments ? price_format($contract->order->total/$contract->no_of_installments) : '-' }}) درهم بعد انتهاء الفترة التجريبية في حالة انتقال العامل.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">10.</td>
                </tr>

                <tr>
                    <td class="text-right" style="width: 5px;">11.</td>
                    <td style="width: 328px">
                        Any other obligations applicable under relevant legal systems of the Ministry.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        أية التزامات أخرى تفرضها عليه النظم القانونية ذات العالقة والمعمول بها في الوزارة.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">11.</td>
                </tr>

            </table>
        </td>
    </tr>


    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 6: Transfer Domestic Worker</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند السادس : انتقال العامل</b>
        </td>
    </tr>
    
    <tr>
    <td colspan="2">
    <table class="w-100 table-sm">
    <tr>
        <td class="text-right" style="width: 5px;">1.</td>
        <td style="width: 328px">
            The two parties hereby agree that if the Second Party desires to transfer the service of the worker directly and for a
            longer contract, the First Party shall commence transfer procedures with the Second Party within a maximum period of
            fifteen days from the date of submitting the moving request and shall also pay all due amounts and accordingly:
        </td>
        <td class="text-right" style="direction: rtl; width: 328px">
            اتفق الطرفان على أنه في حالة رغبة الطرف الثاني في انتقال خدمة العامل إليه بعالقة عمل طول الوقت ومباشرة ، فيلتزم الطرف األول بالسير في 
            انتقال العامل للطرف الثاني خالل مدة أقصاها خمسة عشر يوما من تاريخ طلب الطرف الثاني و التزامه بسداد جميع المبالغ المستحقة عليه لذلك وفق
        </td>
        <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
    </tr>
    
    <tr>
        <td colspan="4">
            <table class="w-100 table-sm">
                <tr>
                    <td class="text-right" style="width: 30px;">a.</td>
                    <td style="width: 300px">
                        The Second Party shall conclude an employment contract with the worker according the Ministry’s form.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 300px">
                        يلتزم الطرف الثاني بإبرام عقد عمل مع العامل وفق النموذج المعد من قبل الوزارة.
                    </td>
                    <td style="direction: rtl; width: 30px;">أ.</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 30px;">b.</td>
                    <td style="width: 300px">
                        The Second Party shall bear transfer fees.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 300px">
                        يتحمل الطرف الثاني رسوم انتقال خدمة العامل إليه.
                    </td>
                    <td style="direction: rtl; width: 30px;">ب.</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 30px;">c.</td>
                    <td style="width: 300px">
                        Worker's guarantees shall expire after being transferred to the employer's file and 
                        can be insured according to regulations.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 300px">
                        ينتهي ضمان العامل بعد انتقاله إلى ملف صاحب العمل وباإلمكان التأمين عليه حسب األنظمة.
                    </td>
                    <td style="direction: rtl; width: 30px;">ت.</td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 30px;">d.</td>
                    <td style="width: 300px">
                        The transfer process of the worker is subject to the approval of the Ministry of Human Resources and Emiratisation 
                        and other competent authorities.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 300px">
                        تخضع عملية انتقال خدمة العامل لموافقة وزارة الموارد البشرية والتوطين الجهات المختصة األخرى.
                    </td>
                    <td style="direction: rtl; width: 30px;">ث.</td>
                </tr>
            </table>
        </td>
    </tr>
    
    <tr>
        <td class="text-right" style="width: 5px;">3.</td>
        <td style="width: 328px">
            Should the Second wish to transfer the work to directly and full time, he/she shall make the prescribed lump sum, 
            before the First Party commences transfer procedures.
        </td>
        <td class="text-right" style="direction: rtl; width: 328px">
            يلتزم الطرف الثاني عند رغبته في انتقال خدمة العامل إليه بعالقة عمل طول الوقت ومباشرة بدفع مبلغ اإلنتقال المقطوع دفعة واحدة قبل البدء الط
            بالسير في إجراءات انتقال العامل.
        </td>
        <td class="text-right" style="direction: rtl; width: 5px;">2.</td>
    </tr>

    <tr>
        <td class="text-right" style="width: 5px;">3.</td>
        <td style="width: 328px">
            The First Party, in case of delay in canceling the work permit for 15 days, shall pay the Second Party all monthly 
            amounts paid by him/her ({{ $contract->customer->name }}) for the duration of contract.
        </td>
        <td class="text-right" style="direction: rtl; width: 328px">
            يلتزم الطرف األول في حالة تأخره في إلغاء تصريح العمل عن 15 يوم بأن يدفع للطرف الثاني جميع المبالغ الشهرية التي دفعها للطرف األول عنالعقد.
        </td>
        <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
    </tr>
    </table>
    </td>


    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 7: Disputes Settlement</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند السابع : تسوية المنازعات</b>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table class="w-100 table-sm">
                <tr>
                    <td class="text-right" style="width: 5px;">1.</td>
                    <td style="width: 328px">
                        No external agreements related to the subject of this contract shall be considered, whether prior or subsequent to its
                        signature, and shall be deemed null and void
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        ل يعتد بأي اتفاقات خارجية تتعلق بموضوع هذا العقد ، سواء كانت سابقة، أو الحقة لتوقيعيه، وتعتبر كأن لم تكن.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
                </tr>

                <tr>
                    <td class="text-right" style="width: 5px;">2.</td>
                    <td style="width: 328px">
                        Without prejudice to the ministry's right to take legal action against the party violating the contract, in case of a dispute 
                        arising between the two parties; the parties shall resort to the Ministry to settle the dispute amicably, and to take whatever 
                        action it deems fit.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        دون اإلخالل بحق الوزارة في اتخاذ اإلجراءات القانونية تجاه الطرف المخل بالعقد، في حالة حدوث خالف بين الطرفين يتم اللجوء إلى الوزارة لت
                        الموضوع وديا بين الطرفين واتخاذ ما تراه مناسبا.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">2.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">3.</td>
                    <td style="width: 328px">
                        Where no provision is made in this contract, the provisions of Federal Law No. (10) of 2017, concerning domestic
                        workers,its executive regulations,and other legal systems applicable in the Ministry of Human Resources and
                        Emiratization, shall apply in this regard.The UAE courts shall be competent to hear any dispute relating to this contract.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 328px">
                        فيما لم يرد به نص في هذا العقد تسري أحكام القانون االتحادي رقم ) 10 ( لسنة ،2017 بشأن عمال الخدمة المساعدة، والئحته التنفيذية، وباقي ال
                        القانونية السارية بوزارة الموارد البشرية و التوطين في هذا الشأن، وتكون محاكم دولة اإلمارات هي جهة االختصاص ينظر أية منازعة متعلقة بهذا ا
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
                </tr>
            </table>
            </td>
            </tr>    




    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 10: Contract Expiry or Termination</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند الثامن : انتهاء العقد أو فسخه</b>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <table class="w-100 table-sm">
                <tr>
                    <td class="text-right" style="width: 5px;">1.</td>
                    <td style="width: 328px">
                        This contract shall end by the expiry of its term agreed upon by the two parties.
                    </td>

                    <td class="text-right" style="direction: rtl; width: 328px">
                        ينتهي هذا العقد بانتهاء المدة المتفق عليها.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
                </tr>
                
                <tr>
                    <td class="text-right" style="width: 5px;">2.</td>
                    <td style="width: 328px">
                        The two parties may agree to terminate this contract before the expiry of its term, provided that the agreement on 
                        termination shall be in writing.
                    </td>

                    <td class="text-right" style="direction: rtl; width: 328px">
                        يجوز للطرفين االتفاق على إنهاء هذا العقد قبل انتهاء مدته بالتراضي، بشرط أن يكون االتفاق على اإلنهاء مكتوبا.
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">2.</td>
                </tr>    

                <tr>
                    <td class="text-right" style="width: 5px;">3.</td>
                    <td style="width: 328px">
                        The contract may be terminated by either party if the other party breaches any of its provisions.
                    </td>

                    <td class="text-right" style="direction: rtl; width: 328px">
                        يحق ألي من الطرفين إنهاء هذا العقد في حال إخالل الطرف اآلخر بأي بند من بنوده
                    </td>
                    <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
                </tr>    
            </table>
        </td>
    </tr>            

    <tr>
        <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
            <b>Clause 9: Contract Expiry or Termination</b>
        </td>
        <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
            <b>البند التاسع : تحرير العقد</b>
        </td>
    </tr>
</table>