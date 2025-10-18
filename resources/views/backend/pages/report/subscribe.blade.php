<!DOCTYPE html>
<html lang="en" class="light">
    <table>
            <tr >
                <td align="center"><b>No.</b></td>
                <td align="center"><b>Email</b></td>
                <td align="center"><b>Date</b></td>
            </tr>
            @foreach ($data as $da => $row)
            <tr>
                <td align="center" >{{$da+1}}</td>
                <td >{{$row->email}}</td>
                <td align="center"> {{date('d-m-Y H:i',strtotime($row->created_at))}}</td>
            </tr>
            @endforeach
    </table>
</html>
