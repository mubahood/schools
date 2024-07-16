<?php
$width = 320;
$height = 210;
$logo = public_path('storage/' . $user->ent->logo);
// $logo = url('storage/' . $user->ent->logo);

//get file name
$file = pathinfo($user->avatar, PATHINFO_FILENAME) . '.' . pathinfo($user->avatar, PATHINFO_EXTENSION);
$avatar = public_path('storage/images/' . $file);
// $avatar = url('storage/images/' . $file);

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

<div class=" mt-0" style="">
    <div style="width: {{ $width }}px; 
    height: {{ $height }}px;
    background-color: #e8ecef;"
        class=" d-inline-block">
        <div style="background-color: {{ $user->ent->color }};" class="p-1">
            <table>
                <tr>
                    <td>
                        <img src="{{ $logo }}" style="width: 40px;" class="d-inline-block">
                    </td>
                    <td
                        style="text-align: start; align-content: flex-start; vertical-align: top;
                    
                    text-align: center; align-content: center;
                    ">
                        <h2 style="color: white; font-size: 18px; line-height: 17px; " class="p-1 text-center pr-3">
                            {{ strtoupper($user->ent->name) }}
                        </h2>
                    </td>
                </tr>
            </table>
        </div>
        <table class="mt-1">
            <tr>
                <td>
                    <img src="{{ $avatar }}" style="width: 70px; transfosrm: translateY(-32%);"
                        class="d-inline-block">
                </td>
                <td style="
                width: {{ $width - 150 }}px;
                height: {{ 90 }}px; 
                "
                    class="bg-info;">
                    <div style="">
                        <div class="text-uppercase text-center text-center pt-1"
                            style="font-size: 12px!important; 
                                font-weight: bold;
                                color: white;
                                display: inline-block!important;
                                display: block!important;
                                height: {{ $height - 185 }}px;
                                background-color: {{ $user->ent->color }};
                                transfosrm: translateY(-135%)!important;
                            ">
                            <?= $user->user_type ?> ID CARD
                        </div>
                        <p class="value text-center mt-1">{{ $user->name }}</p>

                        <div style="display: flex;
                          height: 16px!important; line-height: 16px!important;
                        "
                            class=" mt-0">
                            <p class="value d-inline-block text-muted p-0 m-0"
                                style="line-height: 14px!important;
                              height: 16px!important; line-height: 16px!important;
                              background-color: p
                            ">
                                ID
                                NO.: </p>
                            <p class="value d-inline-block p-0 m-0"
                                style="line-height: 14px!important; margin: 0!important; padding: 0!important;">
                                {{ strtoupper($user->user_number) }}</p>
                        </div>

                        <div style="display: flex; ; margin: 0!important; padding: 0!important;
                          height: 16px!important; line-height: 16px!important;
                        "
                            class=" mt-0">
                            <p class="value d-inline-block text-muted p-0 m-0" style="line-height: 14px!important;">
                                CLASS: </p>
                            <p class="value d-inline-block p-0 m-0" style="line-height: 14px!important;">
                                {{ strtoupper($user->current_class_text) }}</p>
                        </div>

                        <div
                            style="display: flex; margin: 0!important; padding: 0!important;  
                        height: 16px!important; line-height: 16px!important;
                        ">
                            <p class="value d-inline-block text-muted p-0 m-0"
                                style="
                            line-height: 14px!important; 
                            margin: 0!important; 
                            padding: 0!important; 
                            ">
                                EXPIRY: </p>
                            <p class="value d-inline-block p-0 m-0"
                                style="line-height: 14px!important; margin: 0!important; padding: 0!important;">
                                {{ '31-DEC-' . $current_year }}</p>
                        </div>

                </td>
                <td>
                    <img src="{{ $qr_code }}" style="width: 70px;" class="text-center mt-1">
                </td>
            </tr>
        </table>

        <hr class="mt-1"
            style="
        background-color: {{ $user->ent->color }};
        height: 1px; 
        margin: 0!important;
        margin-bottom: 5!important;
        ">

        <div style="display: flex;
          height: 14px!important; line-height: 14px!important;
          
        ">
            <p class="value d-inline-block text-muted pl-2" style="font-size: 10px!important; ">GUARDIAN: </p>
            &nbsp;
            <p class="value d-inline-block" style="font-size: 10px!important">
                {{ strtoupper($user->emergency_person_name) }},
                {{ strtoupper($user->emergency_person_phone) }}.
            </p>
        </div>

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
            <p class="value text-center pt-3" style="line-height: 15px; font-size: 16px; color: black;">
                {{ strtoupper('TERMS AND CONDITIONS') }}
            </p>

            <table class="mt-1">
                <tr>
                    <td
                        style="
                    font-size: 12px;
                    line-height: 12px;
                    font-weight: 400;
                    ">
                        <ul>
                            <li>This card is a property of {{ $user->ent->name }}</li>
                            <li class="mt-1">If found, please contact the GUARDIAN or return to the address below.
                            </li>
                        </ul>

                    </td>
                    <td>
                        <img src="{{ $qr_code }}" style="width: 80px;" class="text-center mt-1 mr-2">
                    </td>
                </tr>
            </table>

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
