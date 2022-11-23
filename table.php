<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>

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

?>
<table>
<tr style="padding: 0px;">
<th style="background-color:#ffffff;color:#000000; width:5%; height: 50px;"><b>Hodina→<br>Deň↓</b></th>
<th style="background-color:#45cdff;color:#000000; width:5%; height: 50px; border-width: thick;"><b>Priemer<br>(6:00-15:00)</b></th>
<?php $dh=0; ?>
<?php for($i=1,$jh=0;$i<25;$i++,$jh++){ ?>
    <th style="background-color:<?php echo $i>6 && $i<16?"#45cdff":"#ffe200" ?>;color:#000000; width:3% "><b><?php echo $i; ?></b><br><span style="color:#000000; font-size: 12px;"><?php 
    if($jh==10){
        $jh=0;
        $dh+=1;
    }
    if($jh==9 && $dh==0){
        echo $dh.$jh.":00-10:00";
    }elseif($jh==9 && $dh==1){
        echo $dh.$jh.":00-20:00";
    }elseif($jh==3 && $dh==2){
        echo $dh.$jh.":00-00:00";
    }else{
        echo $dh.$jh.":00-".$dh.($jh+1).":00"; 
    }
?>
    </span></th>
<?php } ?>
</tr>
<?php for($i=7,$j=0;$i>0;$i--,$j++){ 
        if($i<7){
    ?>
    <tr style="height: 10px;">
        <td style="background-color:#f2ca44;color:#000000; width:4%; text-align: center;"><b><?php echo date("d.m.Y", strtotime("-".$i." day")); ?></b></td>
           <td style="background-color:<?php if(get_average($data, $j*24)>get_average($data, ($j-1)*24)){ echo "#f86161";}elseif(get_average($data, $j*24)==get_average($data, ($j-1)*24)){ echo "#f2ca44"; }else{ echo "#7fd05c";} ?>;color:#000000; text-align: center; border-width: thick;">
           <?php $average=get_average($data, $j*24);
            echo $average." "; if(get_average($data, $j*24)>get_average($data, ($j-1)*24)){ echo "➚";}elseif(get_average($data, $j*24)==get_average($data, ($j-1)*24)){ echo "➙"; }else{ echo "➘";}?></td>
            <?php for($index=($j*24);$index<(($j*24)+24);$index++){ ?>
            <td style="background-color:<?php echo $average<$data[$index]->price?"#f86161":"#7fd05c"?>;color:#000000; text-align: center;"><?php echo $data[$index]->price; ?></td>
            <?php } ?>
    </tr>
<?php } } ?>

<tr style="height: 10px;">
    <td style="background-color:#f2ca44;color:#000000; width:4%; text-align: center;"><b><?php echo date("d.m.Y"); ?></b></td>
    <td style="background-color:<?php if(get_average($data, 168)>get_average($data, 144)){ echo "#f86161";}elseif(get_average($data, 168)==get_average($data, 144)){ echo "#f2ca44"; }else{ echo "#7fd05c";} ?>;color:#000000; text-align: center; border-width: thick;">
    <?php $today_average_price = get_average($data, 168);
        echo $today_average_price." "; if(get_average($data, 168)>get_average($data, 144)){ echo "➚";}elseif(get_average($data, 168)==get_average($data, 144)){ echo "➙"; }else{ echo "➘";}?></td>
    <?php for($i=168;$i<192;$i++){ ?>
    <td style="background-color:<?php echo $today_average_price>$data[$i]->price?"#7fd05c":"#f86161"?>;color:#000000; text-align: center;"><?php echo $data[$i]->price; ?></td>
<?php } ?>
</tr>

<?php if(array_key_exists(192,$data)){ ?>
    <tr style="height: 10px;">
        <td style="background-color:#f2ca44;color:#000000; width:4%; text-align: center;"><b><?php echo date("d.m.Y", strtotime("+1 day")); ?></b></td>
        <td style="background-color:<?php if(get_average($data, 192)>get_average($data, 168)){ echo "#f86161";}elseif(get_average($data, 192)==get_average($data, 168)){ echo "#f2ca44"; }else{ echo "#7fd05c";} ?>;color:#000000; text-align: center; border-width: thick;">
        <?php $tomorrow_average_price=get_average($data, 192);
            echo $tomorrow_average_price." "; if(get_average($data, 192)>get_average($data, 168)){ echo "➚";}elseif(get_average($data, 192)==get_average($data, 168)){ echo "➙"; }else{ echo "➘";}?></td>
        <?php for($i=192;$i<216;$i++){ ?>
            <td style="background-color:<?php echo $tomorrow_average_price>$data[$i]->price?"#7fd05c":"#f86161"?>;color:#000000; text-align: center;"><?php echo $data[$i]->price; ?></td>
        <?php } ?>
    </tr>
<?php } ?>
</table>
   <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <div id="chart_div" style="width: auto; height: 650px; padding:-10px"></div>
  
 <script>
     google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawTrendlines);

function drawTrendlines() {
      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', '<?php echo date("d.m.Y", strtotime("-6 day")); ?>');
      data.addColumn('number', '<?php echo date("d.m.Y", strtotime("-5 day")); ?>');
      data.addColumn('number', '<?php echo date("d.m.Y", strtotime("-4 day")); ?>');
      data.addColumn('number', '<?php echo date("d.m.Y", strtotime("-3 day")); ?>');
      data.addColumn('number', '<?php echo date("d.m.Y", strtotime("-2 day")); ?>');
      data.addColumn('number', '<?php echo date("d.m.Y", strtotime("-1 day")); ?>');
      data.addColumn('number', '<?php echo date("d.m.Y"); ?>');
    <?php if(array_key_exists(192,$data)){ ?>
      data.addColumn('number', '<?php echo date("d.m.Y", strtotime("+1 day")); ?>');
<?php } ?>
      data.addRows([
            <?php 
                $i=1;

                while($i<25){
            ?>
                    [<?php echo $i; ?>, <?php echo $data[$i+23]->price; ?>, <?php echo $data[$i+47]->price; ?>, <?php echo $data[$i+71]->price; ?>, <?php echo $data[$i+95]->price; ?>, <?php echo $data[$i+119]->price; ?>, <?php echo $data[$i+143]->price; ?>, <?php echo $data[$i+167]->price; if(array_key_exists(192,$data)){ ?>, <?php echo $data[$i+191]->price; }?>],  
            <?php 
                    $i++;    
                } 
          ?>
      ]);

      var options = {
        hAxis: {
          title: 'Hodina'
        },
        vAxis: {
          title: 'Hodnota'
        },
        colors: ['#fd4239', '#f2ca44', '#7fd05c', '#f861be', '#4451f2', '#39dffd', <?php echo array_key_exists(192,$data)?"'#8c39fd'":"'#000000'"; if(array_key_exists(192,$data)){ echo ", '#000000'"; } ?> ],
          series: {
            0: { lineWidth: 3 },
            1: { lineWidth: 3 },
            2: { lineWidth: 3 },
            3: { lineWidth: 3 },
            4: { lineWidth: 3 },
            5: { lineWidth: 3 },
            <?php if(array_key_exists(192,$data)){ ?>
                6: { lineWidth: 3 },
                7: { lineWidth: 10 },
              <?php }else{ ?>
                6: { lineWidth: 10 },
              <?php } ?>
          },
        trendlines: {
          /*0: {type: 'exponential', color: '#333', opacity: 1},
          1: {type: 'linear', color: '#111', opacity: .3}*/
        }
      };
        var chart_div = document.getElementById('chart_div');
      var chart = new google.visualization.LineChart(chart_div);
    
    /*google.visualization.events.addListener(chart, 'ready', function () {
        chart_div.innerHTML = '<img src="' + chart.getImageURI() + '">';
        console.log(chart_div.innerHTML);
      });*/
    
      chart.draw(data, options);
    
    }
</script>

                              
                             
