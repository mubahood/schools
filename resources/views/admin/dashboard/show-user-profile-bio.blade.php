<style>
    .item {
        font-size: 1.5rem;
    }
</style>

@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<div class="row">
    <div class="col-xs-12 col-md-12">  
        <p class="mb-2 mb-md-3 item"><b>NAME:</b> {{ $u->first_name }} {{ $u->last_name }}</p>
        <p class="mb-2 mb-md-3 item"><b>Date of birth:</b> {{ $u->date_of_birth }}</p>
        <p class="mb-2 mb-md-3 item"><b>Place of birth:</b> {{ $u->place_of_birth }}</p>
        <p class="mb-2 mb-md-3 item"><b>Sex:</b> {{ $u->sex }}</p>
        <p class="mb-2 mb-md-3 item"><b>Home address:</b> {{ $u->home_address }}</p>
        <p class="mb-2 mb-md-3 item"><b>Current address:</b> {{ $u->current_address }}</p>
        <p class="mb-2 mb-md-3 item"><b>Phone number 1:</b> {{ $u->phone_number_1 }}</p>
        <p class="mb-2 mb-md-3 item"><b>Phone number 2:</b> {{ $u->phone_number_2 }}</p>
        <p class="mb-2 mb-md-3 item"><b>Nationality:</b> {{ $u->nationality }}</p>
        <p class="mb-2 mb-md-3 item"><b>Religion:</b> {{ $u->religion }}</p>
        <p class="mb-2 mb-md-3 item"><b>Father's name:</b> {{ $u->father_name }}</p>
        <p class="mb-2 mb-md-3 item"><b>Father's phone number:</b> {{ $u->father_phone }}</p>
        <p class="mb-2 mb-md-3 item"><b>Mother's name:</b> {{ $u->mother_name }}</p>
        <p class="mb-2 mb-md-3 item"><b>Mother's phone number:</b> {{ $u->mother_phone }}</p>
        <p class="mb-2 mb-md-3 item"><b>Languages/Dilect:</b> {{ $u->languages }}</p>


        @if ($u->user_type == 'employee')
            <hr>
            <p class="mb-2 mb-md-3 item"><b>Emergency person to contact name:</b> {{ $u->emergency_person_name }}</p>
            <p class="mb-2 mb-md-3 item"><b>Emergency person to contact phone number:</b>
                {{ $u->emergency_person_phone }}
            </p>
            <p class="mb-2 mb-md-3 item"><b>Spouse's name:</b> {{ $u->spouse_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Spouse's phone number:</b> {{ $u->spouse_name }} </p>
            <hr>
            <p class="mb-2 mb-md-3 item"><b>Primary school name:</b> {{ $u->primary_school_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Primary school year graduated:</b> {{ $u->primary_school_year_graduated }}
            </p>
            <p class="mb-2 mb-md-3 item"><b>Seconday school name:</b> {{ $u->seconday_school_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Seconday school year graduated:</b>
                {{ $u->seconday_school_year_graduated }} </p>
            <p class="mb-2 mb-md-3 item"><b>High school name:</b> {{ $u->high_school_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>High school name year graduated:</b> {{ $u->high_school_year_graduated }}
            </p>
            <p class="mb-2 mb-md-3 item"><b>Degree university name:</b> {{ $u->degree_university_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Degree university year graduated:</b>
                {{ $u->degree_university_year_graduated }} </p>
            <p class="mb-2 mb-md-3 item"><b>Masters Degree university name:</b> {{ $u->masters_university_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>Masters Degree graduation year:</b>
                {{ $u->masters_university_year_graduated }} </p>
            <p class="mb-2 mb-md-3 item"><b>PHD university name:</b> {{ $u->phd_university_name }} </p>
            <p class="mb-2 mb-md-3 item"><b>PHD university year:</b> {{ $u->phd_university_year_graduated }} </p>
        @endif

    </div>
</div>
