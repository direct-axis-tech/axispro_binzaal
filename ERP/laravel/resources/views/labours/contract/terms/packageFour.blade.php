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
                    The two parties agreed that the First Party shall provide a domestic worker through Flexi Service Package: (Hour/Day/Week/Month) 
                    and provide such service to the Second Party according to such assignment by the Second
                    Party and within the scope of the Assistant’s profession as ({{ labour_types()[$contract->maid->type] ?? '-' }}) The worker details are listed below:

                <td class="w-50" style="direction: rtl">
                    اتفق الطرفان على أن يقوم الطرف األول بتوفير عامل خدمة مساعدة بنظام تشغيل الباقة المرنـة وتقديم خدمـاتـه للطــرف الثـاني ، وفقا لما يك
                    حدود مجال عمله بمهــنة ({{ labour_types()[$contract->maid->type] ?? '-' }})، وبيانـات العـامل هي :
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
                    The term of this contract shall be commencing on ({{ $contract->contract_from->format(dateformat()) }}) and ending on ({{ $contract->contract_till->format(dateformat()) }}) , subject 
                    to renewal for one or more similar terms, by virtue of a notice from the Second Party to the First Party before the end of 
                    the agreed term.Both parties agree that the Second Party will notify the First Party about their intentions to renew the contract
                    ( 7 Days ) before it expires.
                </td>
                <td class="w-50" style="direction: rtl">
                    تكون مدة هذا العقد (ساعة/يوم/أسبوع/شهر)تبدأ في ({{ $contract->contract_from->format(dateformat()) }}) وتنتهي في ({{ $contract->contract_till->format(dateformat()) }})
                    ، قابلة للتجديد لمدة مماثلة أو أكثر، بموجب إشعار من الطرف الثاني إلى
                    الطرف الأول قبل نهاية المدة المتفق عليها. يتفق الطرفان على أن يقوم الطرف الثاني بإخطار الطرف الأول بذلك
                    نيتهم ​​تجديد العقد (7 أيام) قبل انتهاء صلاحيته
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
                <td colspan="2">
                    <table class="w-100 table-sm">
                        <tr>
                            <td class="text-right" style="width: 5px;">1.</td>
                            <td style="width: 328px">
                                Pay the First Party the cost of the: hour/day/week/month in consideration for the provision of the services referred to
                                above;according to the value of the hour/day/week/ month being ( AED inf), and in a total amount of
                                ( AED 3150.00), according to the following.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                دفع للطرف الأول تكلفة: ساعة/يوم/أسبوع/شهر مقابل تقديم الخدمات المشار إليها
                    أعلاه؛ وذلك بقيمة الساعة/اليوم/الأسبوع/الشهر وهي (درهم inf)، وبمبلغ إجمالي قدره
                    (3150.00 درهماً) وذلك وفقاً لما يلي.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
                        </tr>

                        <tr>
                            <td class="text-right" style="width: 5px;">1.</td>
                            <td style="width: 328px">
                                Pay the First Party for total amount of the actual hours/days/weeks/months in advance.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                المبلغ اإلجمالي لعدد الساعات / األيام / األسابيع الفعلية مقدما قبل أداء الخدمة المطلوبة.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
                        </tr>

                        <tr>
                            <td class="text-right" style="width: 5px;">1.</td>
                            <td style="width: 328px">
                                Pay at the beginning of each month, and he shall also pay the monthly instalments equal to the contractual period, to the
                                first party (in cash, cheque, or credit card), to be deducted on monthly basis.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                دفعة مقدمة عن كل شهر ، كما يلتزم بدفع بقية األشهر ) نقدًا- شيكات – بطاقة انتمائية ( تعادل مدة العقد ، على أن يتم الخصم بشكل شهري
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">1.</td>
                        </tr>
                    </table>
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
                                ضمان ما يثبت لياقة العامل وحالته الصحية والنفسية و المهنية للعمل المطلوبالقيام به.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">2.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">3.</td>
                            <td style="width: 328px">
                                Pay the monthly salary to the worker in addition to other legal dues as prescribed.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                دفع الراتب الشهري للعامل باإلضافة لباقي المستحقات القانونية المقررة.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">4.</td>
                            <td style="width: 328px">
                                Fully ensure that the worker fulfills the conditions and requirements, as required by the legal regulations in force within
                                the UAE, to practice a particular profession, job or particular duty.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                التأكد التام من استيفاء العامل للشروط والمتطلبات، حسب ما تقتضيه الأنظمة القانونية المعمول بها في الداخل
                                الإمارات العربية المتحدة، لممارسة مهنة أو وظيفة معينة أو واجب معين.
                            </td>    
                            <td class="text-right" style="direction: rtl; width: 5px;">4.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">5.</td>
                            <td style="width: 328px">
                                Provide a substitute worker with the same qualifications and expertise to perform the same job for which the domestic
                                worker was requested, at the request of the Second Party at any time. The replacement can also be made in the case of the
                                principal worker’s absenteeism or refusal to work, within 24 hours from the time the Second Party notifies the First Party.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                توفير عامل بديل يتمتع بنفس المؤهلات والخبرات اللازمة لأداء نفس الوظيفة التي يقوم بها الخادم المنزلي
                                يتم طلب العامل بناء على طلب الطرف الثاني في أي وقت. ويمكن أيضًا إجراء الاستبدال في حالة
                                تغيب العامل الرئيسي أو رفضه العمل خلال 24 ساعة من وقت إخطار الطرف الثاني للطرف الأول.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">5.</td>
                        </tr>
                         
                        <tr>
                            <td class="text-right" style="width: 5px;">6.</td>
                            <td style="width: 328px">
                                The First Party shall not replace the worker assigned, unless written approval was received from the Second Party.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                عدم استبدال العامل بنظام الباقات األسبوعية / الشهرية المقدم خدماته إال بعد أخد الموافقة الكتابية من الطرف الثاني.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">6.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">7.</td>
                            <td style="width: 328px">
                                The Second Party has the right to have the worker replaced with another one when needed, and throughout the
                                contractual period.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                يحق للطرف الثاني استبدال العامل بآخر عند الحاجة طوال فترة التعاقد.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">7.</td>
                        </tr>

                        <tr>
                            <td class="text-right" style="width: 5px;">8.</td>
                            <td style="width: 328px">
                                The Second Party shall be compensated for any loss, damage or destruction of his property caused by the domestic
                                worker, after being proven by the competent authorities.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                تعويض الطرف الثاني مقابل ما يتسبب العامل في فقده أو إتالفه أو تدميره من ممتلكات الطرف الثاني بعد ثبوت ذلك من الجهات المختصة.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">8.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">9.</td>
                            <td style="width: 328px">
                                Provide all the requirements imposed by the legal systems in force by the Ministry of Human Resources and
                                Emiratisation with relations to the employer, unless the parties agree otherwise. In all cases, not withstanding such
                                contracts between the two parties; the First Party shall not be exempted from liability in the event that the beneficiary fails
                                to fulfill his or her obligation, and the abstaining beneficiary shall assume legal responsibility.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                توفير كل ما تفرضه النظم القانونية المعمول بها في الوزارة ومتعلقة بذات الشأن على صاحب العمل ، ما لم يتفق الطرفان على خالف ذلك ، وفي
                                األحوال ، وبصرف النظر عن مثال االتفاقات بين الطرفين )ا((ألول و الثان) ال يعفي الطرف األول من المسؤولية في حال امتناع المستفيد عن الوفاءبه، ومع تحميل المستفيد المسؤولية القانونية.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">9.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">10.</td>
                            <td style="width: 328px">
                                Any other obligations imposed by the relevant legal regulations as applicable in the Ministry.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                أية التزامات أخرى تفرضها عليه النظم القانونية ذات العالقة والمعمول بها في الوزارة.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">10.</td>
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
                                Pay the value agreed upon in clause three.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                سداد القيمة المتفق عليها في البند الثالث.
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
                                Notify the First Party of any violations or errors committed by the worker to take the necessary action against him/her.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                إشعار الطرف األول بأية مخالفات ، أو أخطاء يرتكبها العامل التخاذ ما يلزم من إجراءات بحقه.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">4.</td>
                            <td style="width: 328px">
                                The worker shall not be assigned to work in a profession that is different from the nature of his/her work, except upon
                                his/her consent and provided that such profession is sanctioned by Law.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                عدم تشغيل العامل بمهنة تختلف عن طبيعة عمله إال برضاه وبشرط أن تكون من المهن المشمولة بالقانون.
                            </td>    
                            <td class="text-right" style="direction: rtl; width: 5px;">4.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">5.</td>
                            <td style="width: 328px">
                                Notify the First Party in the event of the worker’s abstention or refusal to work within (24) hours of the date abstention
                                or refusal to work, in addition to the delivery of all the worker's belongings to the First Party.

                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                إبالغ الطرف األول في حال انقطاع العامل عن العمل أو رفضه العمل خالل )24( ساعة من وقت االنقطاع أو رفض العامل مع تسليم جميع متعلق
                                للطرف األول.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">5.</td>
                        </tr>
                         
                        <tr>
                            <td class="text-right" style="width: 5px;">6.</td>
                            <td style="width: 328px">
                                If the worker’s profession is a driver; the vehicle delivered to him/her shall have a valid vehicle insurance and license.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                إذا كانت مهنة العامل سائق يشترط أن تكون السيارة المسلمة له مؤمن عليها وسارية الترخيص.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">6.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">7.</td>
                            <td style="width: 328px">
                                Provide an adequate accommodation for the worker unless otherwise is agreed with the Second Party.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                توفير سكن الئق للعامل ما لم يتم االتفاق مع الطرف الثاني على خالف ذلك.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">7.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">8.</td>
                            <td style="width: 328px">
                                Provide the worker means of sustenance such as meals and appropriate clothing for work performance, unless otherwise
                                is agreed upon with the First Party.

                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                تقديم احتياجات العامل من وجبات الطعام و المالبس المناسبة ألداء العمل. ما لم يتم االتفاق مع الطرف األول على خالف ذلك.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">8.</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right" style="width: 5px;">9.</td>
                            <td style="width: 328px">
                                Provide the worker an adequate environment and work tools in accordance with the legal regulations in force in within
                                the United Arab Emirates.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                توفير بيئة و أدوات العمل للعامل بما يتوافق مع األنظمة القانونية المعمول بها في الدولة.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">9.</td>
                        </tr>

                        <tr>
                            <td class="text-right" style="width: 5px;">10.</td>
                            <td style="width: 328px">
                                The worker shall not work for a third parties.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                عدم تشغيل العامل لدى الغير.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">10.</td>
                        </tr>

                        <tr>
                            <td class="text-right" style="width: 5px;">11.</td>
                            <td style="width: 328px">
                                Provide the worker with all have been agreed with the First Party.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                توفير للعامل كافة ما اتفق عليه مع الطرف للعامل.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">11.</td>
                        </tr>

                        <tr>
                            <td class="text-right" style="width: 5px;">12.</td>
                            <td style="width: 328px">
                                Any other obligations prescribed by the relevant legal regulations followed by the Ministry.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                أية التزامات أخرى تفرضها عليه النظم القانونية ذات العالقة و المعمول بها في الوزارة.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">12.</td>
                        </tr>

                        <tr>
                            <td class="text-right" style="width: 5px;">13.</td>
                            <td style="width: 328px">
                                I undertake, in case the domestic worker is returned to the center, to provide a negative result of the Corona Covid 19
                                (PCR) test from an approved examination center and that the result thereof will not exceed 48 hours.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                                أتعھد أنھ في حالة إرجاع العامل المساعد للمركز بتوفیر نتیجة فحص كورونا (PCR) سلبي من مركز فحص معتمد وان ال تزید نتیجة الفحص ساعة.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">13.</td>
                        </tr>

 
                    </table>
                </td>
            </tr>


            <tr>
                <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
                    <b>Clause 6: Disputes Settlement</b>
                </td>
                <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
                    <b>البند السادس : تسوية المنازعات</b>
                </td>
            </tr>
            <tr>
            <td colspan="2">
            <table class="w-100 table-sm">
            <tr>
                <td class="text-right" style="width: 5px;">1.</td>
                <td style="width: 328px">
                    No external agreements related to the subject of this contract shall be considered, whether prior or subsequent to its
                    signature, and shall be deemed null and void.
                </td>
                <td class="text-right" style="direction: rtl; width: 328px">
                    لا يجوز النظر في أي اتفاقيات خارجية تتعلق بموضوع هذا العقد سواء كانت سابقة عليه أو لاحقة له
                    التوقيع ويعتبر لاغيا وباطلا.
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
                    مع عدم الإخلال بحق الوزارة في اتخاذ الإجراءات القانونية ضد الطرف المخالف للعقد في حالة وجود نزاع
                    الناشئة بين الطرفين؛ وعلى الطرفين اللجوء إلى الوزارة لتسوية النزاع ودياً واتخاذ ما يلزم
                    الإجراء الذي يراه مناسبا.
                </td>
                <td class="text-right" style="direction: rtl; width: 5px;">2.</td>
            </tr>

            <tr>
                <td class="text-right" style="width: 5px;">3.</td>
                <td style="width: 328px">
                    Where no provision is made in this contract, the provisions of Federal Law No. (10) of 2017, concerning domestic
                    workers, its executive regulations, and other legal systems applicable in the Ministry of Human Resources and
                    Emiratization, shall apply in this regard. The UAE courts shall be competent to hear any dispute relating to this contract.
                </td>
                <td class="text-right" style="direction: rtl; width: 328px">
                    فيما لم يرد في هذا العقد نص، تسري أحكام القانون الاتحادي رقم (10) لسنة 2017 في شأن الخدمات المنزلية
                    العاملين ولائحته التنفيذية والأنظمة القانونية الأخرى المعمول بها في وزارة الموارد البشرية
                    ويتم تطبيق التوطين في هذا الشأن. تختص محاكم دولة الإمارات العربية المتحدة بالنظر في أي نزاع يتعلق بهذا العقد.
                </td>
                <td class="text-right" style="direction: rtl; width: 5px;">3.</td>
            </tr>
            </table>
            </td>
            </tr>

          


            <tr>
                <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
                    <b>Clause 7: Contract Expiry or Termination</b>
                </td>
                <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
                    <b>البند السابع : انتهاء العقد أو فسخه</b>
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

                        <tr>
                            <td class="text-right" style="width: 5px;">4.</td>
                            <td style="width: 328px">
                                In the event that the worker abstains from work, or the Second Party wishes to terminate the contract, the First Party
                                shall return the remaining amount for the agreed period of service.
                            </td>

                            <td class="text-right" style="direction: rtl; width: 328px">
                                في حال رفض العامل العمل ، أو رغبة الطرف الثاني في إنهاء العقد ، يلتزم الطرف األول بإرجاع المبلغ المتبقي عن مدة الخدمة المتفق عليها.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 5px;">4    .</td>
                        </tr>    
                    </table>
                </td>
            </tr>            
            <tr>
                <td class="w-50" style="padding-top: 1rem; line-height: 1.5">
                    <b>Clause 8: Counter parts</b>
                </td>
                <td class="w-50" style="direction: rtl; padding-top: 1rem; line-height: 1.5">
                    <b>البند الثامن : تحرير العقد</b>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <table class="w-100 table-sm">
                        <tr>
                            <td class="text-right" style="width: 5px;">1.</td>
                            <td style="width: 328px">
                            This contract has been made into three copies signed by both parties hereto, each party shall keep one copy, and the third
                            copy shall be kept at the Ministry.
                            </td>
                            <td class="text-right" style="direction: rtl; width: 328px">
                            حرر هذا العقد من ثالث نسخ بعد أن تم توقيعه من الطرفين ، تسلم إحداها الطرف األول و األخرى الطرف الثاني و تودع الثالثة لدى الوزارة
                            </td>
                        </tr>    
                    </table>
                </td>
            </tr>   
  
        </table>