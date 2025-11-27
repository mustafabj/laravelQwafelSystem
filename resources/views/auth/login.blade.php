<x-guest-layout>

    <h2 class="mt-0 mb-20 fs-28" style="text-align: center;">تسجيل الدخول</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <input
            class="d-block mb-20 w-full p-20 b-none bg-eee rad-6 fs-15 rtl fs-20"
            type="text"
            name="email"
            id="user"
            placeholder="اسم المستخدم"
            value="{{ old('email') }}"
            required />

        <input
            class="d-block mb-20 w-full p-20 b-none bg-eee rad-6 fs-15 rtl fs-20"
            type="password"
            name="password"
            id="password"
            placeholder="كلمة المرور"
            required />

        @if ($errors->any())
        <p style="padding: 12px;
           color: #721c24;
           background-color: #f8d7da;
           border: 1px solid #f5c6cb;
           border-radius: 6px;">
            {{ $errors->first() }}
        </p>
        @endif

        <input
            class="save d-block fs-14 c-white b-none w-full p-10 rad-6"
            type="submit"
            value="تسجيل الدخول" />
    </form>

</x-guest-layout>