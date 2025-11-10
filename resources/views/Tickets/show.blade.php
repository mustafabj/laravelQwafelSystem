<div class="formS cust tab print active" id="ticket">
    <div class="container">
        <!-- Company Details -->
        <div class="combanyDetails printFlex">
            <div class="img">
                @if(auth()->user()->office && auth()->user()->office->officeImage)
                    <img src="{{ asset('admin/upload/' . auth()->user()->office->officeImage) }}" 
                         alt="{{ auth()->user()->office->officeImage }}">
                @endif
            </div>

            @include('components.details_title')
        </div>

        <!-- Buttons Section -->
        <div class="printBtns">
            <div class="printBtn printInlineFlex" onclick="window.print()">
                <img src="{{ asset('image/printing.png') }}" alt="printer" />
                طباعة
            </div>

            @if($ticket->accept === 'no' && $ticket->userId != auth()->id())
                <div class="printBtn printInlineFlex" onclick="acceptticket({{ $ticket->ticketId }})">
                    <img src="{{ asset('image/accept.png') }}" alt="accept" />
                    قبول
                </div>
            @endif
        </div>

        <h1 class="title">تذكرة سفر</h1>
    </div>

    <div class="container">
        <form>
            <!-- Passenger Details -->
            <div class="top">
                <div>
                    <div>
                        <label>رقم التذكرة :</label>
                        <input type="text" readonly value="{{ $ticket->tecketNumber }}">
                    </div>
                    <div>
                        <label>اسم المسافر :</label>
                        <input type="text" readonly value="{{ $ticket->customer->FName }} {{ $ticket->customer->LName }}">
                    </div>
                    <div>
                        <label>هاتف المسافر :</label>
                        <input type="text" readonly value="{{ $ticket->customer->custNumber }}">
                    </div>
                </div>

                <div>
                    <div>
                        <label>تاريخ التذكرة :</label>
                        <input type="text" readonly value="{{ $ticket->ticketDate }}">
                    </div>
                    <div>
                        <label>رقم جواز السفر :</label>
                        <input type="text" readonly value="{{ $ticket->customer->customerPassport }}">
                    </div>
                </div>
            </div>

            <!-- Address Section -->
            @if($ticket->address)
                <hr class="noPrint">
                <h2 style="text-align: center;" class="noPrint">عنوان المسافر</h2>

                <div class="top noPrint">
                    <div>
                        <div>
                            <label>المدينة :</label>
                            <input type="text" readonly value="{{ $ticket->address->city }}">
                        </div>
                        <div>
                            <label>المنطقة :</label>
                            <input type="text" readonly value="{{ $ticket->address->area }}">
                        </div>
                    </div>

                    <div>
                        <div>
                            <label>اسم الشارع :</label>
                            <input type="text" readonly value="{{ $ticket->address->street }}">
                        </div>
                        <div>
                            <label>رقم المبنى :</label>
                            <input type="text" readonly value="{{ $ticket->address->buildingNumber }}">
                        </div>
                    </div>
                </div>

                <div class="top noPrint">
                    <div>
                        <div>
                            <label>معلومات إضافية :</label>
                            <textarea style="resize: none;" readonly>{{ $ticket->address->info }}</textarea>
                        </div>
                    </div>
                </div>
                <hr class="noPrint">
            @endif

            <!-- Travel Details -->
            <div class="top">
                <div>
                    <div>
                        <label>تاريخ السفر :</label>
                        <input type="text" readonly value="{{ $ticket->travelDate }}">
                    </div>
                    <div>
                        <label>رقم المقعد :</label>
                        <input type="text" readonly value="{{ $ticket->Seat }}">
                    </div>
                </div>

                <div>
                    <div>
                        <label>وقت السفر :</label>
                        <input type="text" readonly value="{{ $ticket->formatted_time }}">
                    </div>
                    <div>
                        <label>جهة السفر :</label>
                        <input type="text" readonly value="{{ $ticket->destination }}">
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <hr>
            <div class="top">
                <div>
                    <div>
                        <label>سعر التذكرة :</label>
                        <input type="text" readonly value="{{ $ticket->cost }} {{ $ticket->currency_name }}">
                    </div>
                    <div>
                        <label>باقي المبلغ غير الواصل :</label>
                        <input type="text" readonly value="{{ $ticket->unpaid_amount }} {{ $ticket->currency_name }}">
                    </div>
                </div>
                <div>
                    <div>
                        <label>المبلغ المدفوع :</label>
                        <input type="text" readonly value="{{ $ticket->costRest }} {{ $ticket->currency_name }}">
                    </div>
                </div>
            </div>
        </form>

        <!-- Notes -->
        <ol class="printOnly" type="1">
            <li>في حال الغاء موعد السفر يفقد قيمة التذكرة كاملة.</li>
            <li>يحق لكل راكب شنطة 30 كيلو فقط.</li>
        </ol>
    </div>
</div>
