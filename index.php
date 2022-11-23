

<?php

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);  

$response = file_get_contents("https://isot.okte.sk/api/v1/dam/results?deliveryDayFrom=".date("Y-m-d", strtotime("-7 day"))."&deliveryDayTo=".date("Y-m-d", strtotime("+1 day")), false, stream_context_create($arrContextOptions));

$data = json_decode($response);

function get_average($data, $index){
    $yesterday_average_price=0;
    $yesterday_sum_price=0;
    for($i=$index;$i<($index+24);$i++){
        if((($i-$index>5) && ($i-$index<15)) && ($index>23)){
            $yesterday_sum_price+=$data[$i]->price;
        }
    }
    return round($yesterday_sum_price/9,2);
}

$today_average_price=0;
$today_sum_price=0;
for($i=24;$i<48;$i++){
    $today_sum_price+=$data[$i]->price;
}
$today_average_price=round($today_sum_price/24,2);

if(array_key_exists(192,$data)){
    $tomorrow_average_price=0;
    $tomorrow_sum_price=0;
    for($i=48;$i<72;$i++){
        $tomorrow_sum_price+=$data[$i]->price;
    }
    $tomorrow_average_price=round($tomorrow_sum_price/24,2);
}

?>


<?php
//

    $from="info@cenyelektriny.sk";
                               
   //$to="stucka@mkem.sk";
$to="martak@mkem.sk,michal.dic@mkem.sk,sovikova@mkem.sk,irha@mkem.sk,stasak@mkem.sk,buc@mkem.sk,palencarova@mkem.sk,brehovsky@mkem.sk,prejsa@mkem.sk,jpetrus@mkem.sk,habinak@mkem.sk,reti@mkem.sk,stricova@mkem.sk,mezes@mkem.sk, sipko@mkem.sk,arifi@mkem.sk,stucka@mkem.sk";
                               $subject="Ceny elektriny";
                               mb_internal_encoding('UTF-8');
                               $encoded_subject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n", strlen('Subject: '));
                               
                               $msg = '<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>
                               <table>
<tr>
<th style="background-color:#ffffff;color:#000000; width:5%; height: 50px;"><b>Hodina→<br>Deň↓</b></th>
<th style="background-color:#45cdff;color:#000000; width:5%; height: 50px; border-width: thick;"><b>Priemer<br>(6:00-15:00)</b></th>';
    
$dh=0;
for($i=1,$jh=0;$i<25;$i++,$jh++){ 
    $msg.='<th style="background-color:';
    $msg.=$i>6 && $i<16?'#45cdff':'#ffe200';
    $msg.=';color:#000000; width:3% "><b>'.$i.'</b><br><span style="color:#000000; font-size: 12px;">';
    if($jh==10){
        $jh=0;
        $dh+=1;         
    }
    if($jh==9 && $dh==0){
        $msg.= $dh.$jh.":00-10:00";
    }elseif($jh==9 && $dh==1){
        $msg.= $dh.$jh.":00-20:00";
    }elseif($jh==3 && $dh==2){
        $msg.= $dh.$jh.":00-00:00";
    }else{
        $msg.= $dh.$jh.":00-".$dh.($jh+1).":00"; 
    }
    $msg.='</span></th>';
} 
    $msg.='</tr>';
for($i=7,$j=0;$i>0;$i--,$j++){ 
    if($i<7){
        $msg.='<tr style="height: 10px;">
        <td style="background-color:#f2ca44;color:#000000; width:4%; text-align: center;"><b>'.date("d.m.Y", strtotime("-".$i." day")).'</b></td>
        <td style="background-color:';
        if(get_average($data, $j*24)>get_average($data, ($j-1)*24)){ 
            $msg.="#f86161";
        }elseif(get_average($data, $j*24)==get_average($data, ($j-1)*24)){ 
            $msg.="#f2ca44"; 
        }else{ $msg.="#7fd05c"; }
        $average=get_average($data, $j*24);
        $msg.=';color:#000000; text-align: center; border-width: thick;">'.$average.' ';
        if(get_average($data, $j*24)>get_average($data, ($j-1)*24)){
            $msg.="➚";
        }elseif(get_average($data, $j*24)==get_average($data, ($j-1)*24)){ 
            $msg.="➙"; 
        }else{ $msg.="➘";}
        $msg.='</td>';
        for($index=($j*24);$index<(($j*24)+24);$index++){
            $msg.='<td style="background-color:';
            $msg.=$average<$data[$index]->price?"#f86161":"#7fd05c";
            $msg.=';color:#000000; text-align: center;">'.$data[$index]->price.'</td>';
        }
        $msg.='</tr>';
    }
}
    
    $msg.='<tr style="height: 10px;">
    <td style="background-color:#f2ca44;color:#000000; width:4%; text-align: center;"><b>'.date("d.m.Y").'</b></td>
    <td style="background-color:'; 
    if(get_average($data, 168)>get_average($data, 144)){ 
        $msg.="#f86161";
    }elseif(get_average($data, 168)==get_average($data, 144)){
        $msg.="#f2ca44"; 
    }else{ 
        $msg.="#7fd05c";
    } 
    $average=get_average($data, $j*24);
    $msg.=';color:#000000; text-align: center; border-width: thick;">'.$average.' ';
    if(get_average($data, 168)>get_average($data, 144)){ 
        $msg.="➚";
    }elseif(get_average($data, 168)==get_average($data, 144)){ 
        $msg.="➙"; 
    }else{ 
        $msg.="➘";
    }
    $msg.='</td>';
for($i=168;$i<192;$i++){
    $msg.='<td style="background-color:';
    $msg.=$average>$data[$i]->price?"#7fd05c":"#f86161";
    $msg.=';color:#000000; text-align: center;">'.$data[$i]->price.'</td>';
}
    $msg.='</tr>';
if(array_key_exists(192,$data)){
    $msg.='<tr style="height: 10px;">
    <td style="background-color:#f2ca44;color:#000000; width:4%; text-align: center;"><b>'.date("d.m.Y", strtotime("+1 day")).'</b></td>
    <td style="background-color:';
    if(get_average($data, 192)>get_average($data, 168)){ 
        $msg.="#f86161";
    }elseif(get_average($data, 192)==get_average($data, 168)){
        $msg.="#f2ca44"; 
    }else{ 
        $msg.="#7fd05c";
    } 
    $average=get_average($data, ($j+1)*24);
    $msg.=';color:#000000; text-align: center; border-width: thick;">'.$average.' ';
    if(get_average($data, 192)>get_average($data, 168)){ 
        $msg.="➚";
    }elseif(get_average($data, 192)==get_average($data, 168)){
        $msg.="➙"; 
    }else{ 
        $msg.="➘";
    }
    $msg.='</td>';
    for($i=192;$i<216;$i++){
        $msg.='<td style="background-color:';
        $msg.=$average>$data[$i]->price?"#7fd05c":"#f86161";
        $msg.=';color:#000000; text-align: center;">'.$data[$i]->price.'</td>';
    }
}
$msg.='</tr></table>';

$msg.='<br><br><br> <a href="http://srv-production/domains/electricity_price/table.php">Pre grafické zobrazenie kliknite na tento odkaz.</a>';
    
                               $headers = "MIME-Version: 1.0" . "\r\n";
                               $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                               $headers .= 'From: <'.$from.'>' . "\r\n";
                               $m=mail($to,$encoded_subject,$msg,$headers);
    
