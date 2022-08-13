<?php
$_data = [];
if (isset($data)) {
    $_data = $data;
}
?><div class="table-responsive pb-0">
    <table class="table table-striped bg-success ">
        @foreach ($_data as $v)
            <tr>
                <td width="150px">{{ $v['title'] }}</td>
                <td style="text-align: end">{{ $v['sub_title'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
