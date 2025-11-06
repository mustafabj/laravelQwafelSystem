<div class="formS paks tab print active" id="parcel">
    <div class="container">

        {{-- Company Details --}}
        <div class="combanyDetails printFlex">
            <div class="img">
                @if($parcel->office && $parcel->office->officeImage)
                    <img src="{{ asset('storage/'.$parcel->office->officeImage) }}" 
                         alt="{{ $parcel->office->officeName }}" />
                @endif
            </div>
        </div>

        {{-- Print Buttons --}}
        <div class="printBtns">
            <div>
                <div class="printBtn printInlineFlex" onclick="pagePrint(event)">
                    <img src="{{ asset('image/printing.png') }}" alt="printer" />
                    طباعة
                </div>

                <div class="noPrint">
                    <label class="toggle-container">
                        <span>مع السعر :</span>
                        <input class="toggle-checkbox" type="checkbox" id="toggle-checkbox"
                               onchange="pricePrint()" checked>
                        <div class="toggle-switch"></div>
                    </label>
                </div>
            </div>

            {{-- Payment notice --}}
            @if($parcel->paid === 'unpaid' && $parcel->paidInMainOffice)
                <div class="noPrint">
                    <div class="message">
                        <span>الدفع في مكتب المرسل :</span>
                        <span>{{ $parcel->mainOffice?->officeName }}</span>
                    </div>
                </div>
            @endif

            {{-- Accept button --}}
            @if($parcel->accept === 'no' && $parcel->user_id !== $user->id)
                <div class="printBtn printInlineFlex" onclick="acceptparcel({{ $parcel->id }})">
                    <img src="{{ asset('image/accept.png') }}" alt="accept" /> قبول
                </div>
            @endif
        </div>

        <h1 class="title">ارسالية شحن</h1>
    </div>

    <div class="container">
        <form>
            <div class="top">
                <div>
                    <div>
                        <label>رقم الارسالية :</label>
                        <input type="text" value="{{ $parcel->parcelNumber }}" readonly />
                    </div>
                    <div>
                        <label>هاتف المرسل :</label>
                        <input type="text" value="{{ $parcel->custNumber }}" readonly />
                    </div>
                </div>

                <div>
                    <div>
                        <label>اسم المرسل :</label>
                        <input type="text"
                               value="{{ $parcel->customer->FName }} {{ $parcel->customer->LName }}"
                               readonly />
                    </div>
                    <div>
                        <label>التاريخ :</label>
                        <input type="text" value="{{ $parcel->parcelDate }}" readonly />
                    </div>
                </div>
            </div>

            <div class="top">
                <div>
                    <div>
                        <label>اسم المرسل اليه :</label>
                        <input type="text" value="{{ $parcel->recipientName }}" readonly />
                    </div>
                    <div>
                        <label>هاتف المرسل اليه :</label>
                        <input type="text" value="{{ $parcel->recipientNumber }}" readonly />
                    </div>
                </div>

                <div>
                    <div>
                        <label>العنوان :</label>
                        <input type="text" value="{{ $parcel->sendTo }}" readonly />
                    </div>
                    <div>
                        <label>الى مكتب :</label>
                        <input type="text" value="{{ $parcel->destinationOffice->officeName ?? '' }}" readonly />
                    </div>
                </div>
            </div>
        </form>

        <h4 class="printOnly">
            تعتبر هذه الارسالية لاغية بعد 30 يوم من تاريخ اصدارها ولا يحق للمرسل
            او المرسل اليه باي مطالبة من الشركة
        </h4>

        {{-- Parcel Details --}}
        <table>
            <thead>
                <tr>
                    <td>معلومات الطرد</td>
                    <td>العدد</td>
                </tr>
            </thead>
            <tbody id="printParcels">
                @foreach($parcel->details as $detail)
                    <tr>
                        <td>{{ $detail->detailInfo }}</td>
                        <td>{{ $detail->detailQun }}</td>
                    </tr>
                @endforeach

                {{-- Shipping Cost --}}
                <tr>
                    <td colspan="2">
                        <span class="trCost">
                            <span id="costparcel">
                                رسوم الشحن : {{ intval($parcel->cost) }} {{ $parcel->currency }}
                            </span>
                            <span id="costreceiptparcel">
                                {{ $parcel->paid === 'paid' ? 'مدفوع' : 'غير مدفوع' }}
                            </span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Notes --}}
        <ol class="printOnly">
            <li>يحق للشركة فتح الطرد.</li>
            <li>يتحمل المرسل كامل المسؤولية في حال تلف المواد المنقولة خلال الرحلة.</li>
            <li>يتحمل المرسل كامل المسؤولية في حال مصادرة المواد من قبل السلطات.</li>
            <li>سلامة البضاعة مسؤولية السائق.</li>
            <li>في حال فقدان الطرد تقوم الشركة بتعويض المرسل قيمة مبلغ الشحن.</li>
        </ol>

        {{-- Signatures --}}
        <div class="signature printFlex">
            <label>اسم السائق :</label>
            <label>توقيع السائق :</label>
            <label>توقيع المرسل :</label>
        </div>
    </div>
</div>
