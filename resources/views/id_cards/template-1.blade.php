<?php
$width = 323;
$height = 210;
$logo = public_path('storage/' . $user->ent->logo);

//get file name
$file = pathinfo($user->avatar, PATHINFO_FILENAME) . '.' . pathinfo($user->avatar, PATHINFO_EXTENSION);
$avatar = public_path('storage/images/' . $file);

//check if  avatar exists
if (!file_exists($avatar)) {
    $avatar = $logo;
}

$qr_code = public_path($user->qr_code);
if (!file_exists($qr_code)) {
    $qr_code = $logo;
}

$current_year = date('Y');
$next_year = date('Y', strtotime('+1 year'));

?>

<div class="mt-2" style="">
    <div style="width: {{ $width }}px; 
                height: {{ $height }}px;
                background-color: #e8ecef;
                "
        class=" d-inline-block">
        <div style="background-color: {{ $user->ent->color }};" class="p-1">
            <table>
                <tr>
                    <td>
                        <img src="{{ $logo }}" style="width: 40px;" class="d-inline-block">
                    </td>
                    <td
                        style="text-align: start; align-content: flex-start; vertical-align: top;
                    width: {{ $width - 40 }}px;
                    text-align: center; align-content: center;
                    ">
                        <h2 style="color: white; font-size: 18px; line-height: 17px; " class="p-1 text-center pr-3">
                            {{ strtoupper($user->ent->name) }}
                        </h2>
                    </td>
                </tr>
            </table>
        </div>
        <table class="mt-2 ml-1 ">
            <tr>
                <td>
                    <img src="{{ $avatar }}" style="width: 80px;" class="d-inline-block">
                </td>
                <td class="pl-2">
                    <p class="label">NAME</p>
                    <p class="value">{{ $user->name }}</p>

                    <p class="label">POISITION</p>
                    <p class="value">{{ strtoupper($user->user_type) }}</p>

                    <p class="label">{{ strtoupper($user->user_type) }} NUMBER</p>
                    <p class="value">{{ strtoupper($user->user_number) }}</p>

                    <p class="label">CARD VALIDITY</p>
                    <p class="value">{{ '01-JAN-' . $current_year }} - {{ '31-DEC-' . $current_year }}</p>

                </td>
            </tr>
        </table>
    </div>
    &nbsp;
    &nbsp;
    <div style="
    width: {{ $width }}px; 
    height: {{ $height }}px;
    background-color: {{ $user->ent->color }};
    "
        class="d-inline-block">
        <div class=""
            style="
        background-color: #e8ecef;
         height: {{ $height / 1.4 }}px;
        ">
            <p class="value text-center pt-3" style="line-height: 15px; font-size: 12px; color: black;">
                {{ strtoupper('This card is the property of ' . $user->ent->name) }}.
            </p>
            <center><img src="{{ $qr_code }}" style="width: 85px;" class="text-center mt-1"></center>
        </div>

        <div>
            <p class="value text-center pt-3" style="line-height: 15px; font-size: 12px; color: white;">
                P.O.BOX {{ $user->ent->p_o_box }}, TEL: {{ $user->ent->phone_number }} <br>
                EMAIL: {{ $user->ent->email }}
            </p>
        </div>

    </div>
</div>
<br>
