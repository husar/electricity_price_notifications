

<div class="page-content">
      
    <?php
    
    $actual_year=date("Y");
    $months = array("Január","Február","Marec","Apríl","Máj","Jún","Júl","August","September","Október","November","December");
    $month_numbers = array(1,2,3,4,5,6,7,8,9,10,11,12);
    
    ?>
     
      <h1>Priemerné trvanie výroby v hodinách (<?php echo $actual_year; ?>)</h1>
                            
<div id="chart_div" style="height: 500px"></div>
						
 </div>
  <script type="text/javascript" src="js/loader.js"></script>
    <script >
     google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawTrendlines);

function drawTrendlines() {
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'X');
      data.addColumn('number', 'CD');
      data.addColumn('number', 'Moduly');
      data.addColumn('number', 'Balenie');
      data.addColumn('number', 'Výroba celkovo');

      data.addRows([
            <?php 
          
          $max_date=array();
          $max_date_id=array();
          $query_max_date ="SELECT id, CASE WHEN moduly_updated IS NULL AND cd_updated IS NULL THEN date_inserted WHEN moduly_updated IS NOT NULL AND cd_updated IS NULL THEN moduly_updated WHEN cd_updated IS NOT NULL AND moduly_updated IS NULL THEN cd_updated WHEN cd_updated > moduly_updated THEN cd_updated ELSE moduly_updated END AS MostRecentDate FROM polozky WHERE balenie =1";
          $apply_max_date=mysqli_query($connect,$query_max_date);
          while($result_max_date=mysqli_fetch_array($apply_max_date)){
              array_push($max_date,$result_max_date['MostRecentDate']);
              array_push($max_date_id,$result_max_date['id']);
          }
          
          for($i=0;$i<12;$i++){
                
                $moduly_trvanie = array();
                $cd_trvanie = array();
                $celkovo_trvanie = array();
                $balenie_trvanie = array();
                $query_moduly_dni = "SELECT ((DATEDIFF(moduly_updated, date_inserted)) - ((WEEK(moduly_updated) - WEEK(date_inserted)) * 2) - (case when weekday(moduly_updated) = 6 then 1 else 0 end) - (case when weekday(date_inserted) = 5 then 1 else 0 end)) as DifD, id FROM polozky WHERE moduly_hotove='1' AND MONTH(moduly_updated) = ".$month_numbers[$i]." AND YEAR(moduly_updated) = YEAR(NOW()) ";
                $query_cd_dni = "SELECT ((DATEDIFF(cd_updated, date_inserted)) - ((WEEK(cd_updated) - WEEK(date_inserted)) * 2) - (case when weekday(cd_updated) = 6 then 1 else 0 end) - (case when weekday(date_inserted) = 5 then 1 else 0 end)) as DifD, id FROM polozky WHERE cd_hotove='1' AND MONTH(cd_updated) = ".$month_numbers[$i]." AND YEAR(cd_updated) = YEAR(NOW()) ";
                $query_celkovo_dni = "SELECT ((DATEDIFF(balenie_updated, date_inserted)) - ((WEEK(balenie_updated) - WEEK(date_inserted)) * 2) - (case when weekday(balenie_updated) = 6 then 1 else 0 end) - (case when weekday(date_inserted) = 5 then 1 else 0 end)) as DifD, id FROM polozky WHERE balenie='1' AND MONTH(balenie_updated) = ".$month_numbers[$i]." AND YEAR(balenie_updated) = YEAR(NOW()) ";
                $index_max_date=0;
                while($index_max_date<count($max_date)){
                    $query_balenie_dni = "SELECT ((DATEDIFF(balenie_updated, '".$max_date[$index_max_date]."')) - ((WEEK(balenie_updated) - WEEK('".$max_date[$index_max_date]."')) * 2) - (case when weekday(balenie_updated) = 6 then 1 else 0 end) - (case when weekday('".$max_date[$index_max_date]."') = 5 then 1 else 0 end)) as DifD, id FROM polozky WHERE id=".$max_date_id[$index_max_date]." AND MONTH('".$max_date[$index_max_date]."') = ".$month_numbers[$i]." AND YEAR(balenie_updated) = YEAR(NOW()) ";
                    $apply_balenie_dni=mysqli_query($connect,$query_balenie_dni);
                    $result_balenie_dni=mysqli_fetch_array($apply_balenie_dni);
                    if($result_balenie_dni['DifD']==0 && $result_balenie_dni['DifD']!=""){
                        $query_balenie_hodiny="SELECT HOUR(balenie_updated)-HOUR('".$max_date[$index_max_date]."') AS countHours FROM polozky WHERE id=".$max_date_id[$index_max_date]." ";
                        $apply_balenie_hodiny=mysqli_query($connect,$query_balenie_hodiny);
                        $result_balenie_hodiny=mysqli_fetch_array($apply_balenie_hodiny);
                        array_push($balenie_trvanie, $result_balenie_hodiny['countHours']);   
                    }elseif($result_balenie_dni['DifD']!=""){
                        $query_balenie_hodiny="SELECT (16-HOUR('".$max_date[$index_max_date]."'))+(HOUR(balenie_updated)-8)+(8*".($result_balenie_dni['DifD']-1).") AS countHours FROM polozky WHERE id=".$max_date_id[$index_max_date]." ";
                        $apply_balenie_hodiny=mysqli_query($connect,$query_balenie_hodiny);
                        $result_balenie_hodiny=mysqli_fetch_array($apply_balenie_hodiny);
                        array_push($balenie_trvanie, $result_balenie_hodiny['countHours']); 
                    }
                    $index_max_date++;
                }
                
                $apply_moduly_dni=mysqli_query($connect,$query_moduly_dni);
                $apply_cd_dni=mysqli_query($connect,$query_cd_dni);
                $apply_celkovo_dni=mysqli_query($connect,$query_celkovo_dni);
                while($result_moduly_dni=mysqli_fetch_array($apply_moduly_dni)){
                    if($result_moduly_dni['DifD']==0){
                        $query_moduly_hodiny="SELECT HOUR(moduly_updated)-HOUR(date_inserted) AS countHours FROM polozky WHERE id=".$result_moduly_dni['id']." ";
                        $apply_moduly_hodiny=mysqli_query($connect,$query_moduly_hodiny);
                        $result_moduly_hodiny=mysqli_fetch_array($apply_moduly_hodiny);
                        array_push($moduly_trvanie, $result_moduly_hodiny['countHours']);   
                    }else{
                        $query_moduly_hodiny="SELECT (16-HOUR(date_inserted))+(HOUR(moduly_updated)-8)+(8*".($result_moduly_dni['DifD']-1).") AS countHours FROM polozky WHERE id=".$result_moduly_dni['id']." ";
                        $apply_moduly_hodiny=mysqli_query($connect,$query_moduly_hodiny);
                        $result_moduly_hodiny=mysqli_fetch_array($apply_moduly_hodiny);
                        array_push($moduly_trvanie, $result_moduly_hodiny['countHours']); 
                    }
                }
                while($result_cd_dni=mysqli_fetch_array($apply_cd_dni)){
                    if($result_cd_dni['DifD']==0){
                        $query_cd_hodiny="SELECT HOUR(cd_updated)-HOUR(date_inserted) AS countHours FROM polozky WHERE id=".$result_cd_dni['id']." ";
                        $apply_cd_hodiny=mysqli_query($connect,$query_cd_hodiny);
                        $result_cd_hodiny=mysqli_fetch_array($apply_cd_hodiny);
                        array_push($cd_trvanie, $result_cd_hodiny['countHours']);   
                    }else{
                        $query_cd_hodiny="SELECT (16-HOUR(date_inserted))+(HOUR(cd_updated)-8)+(8*".($result_cd_dni['DifD']-1).") AS countHours FROM polozky WHERE id=".$result_cd_dni['id']." ";
                        $apply_cd_hodiny=mysqli_query($connect,$query_cd_hodiny);
                        $result_cd_hodiny=mysqli_fetch_array($apply_cd_hodiny);
                        array_push($cd_trvanie, $result_cd_hodiny['countHours']); 
                    }
                }
                while($result_celkovo_dni=mysqli_fetch_array($apply_celkovo_dni)){
                    if($result_celkovo_dni['DifD']==0){
                        $query_celkovo_hodiny="SELECT HOUR(balenie_updated)-HOUR(date_inserted) AS countHours FROM polozky WHERE id=".$result_celkovo_dni['id']." ";
                        $apply_celkovo_hodiny=mysqli_query($connect,$query_celkovo_hodiny);
                        $result_celkovo_hodiny=mysqli_fetch_array($apply_celkovo_hodiny);
                        array_push($celkovo_trvanie, $result_celkovo_hodiny['countHours']);   
                    }else{
                        $query_celkovo_hodiny="SELECT (16-HOUR(date_inserted))+(HOUR(balenie_updated)-8)+(8*".($result_celkovo_dni['DifD']-1).") AS countHours FROM polozky WHERE id=".$result_celkovo_dni['id']." ";
                        $apply_celkovo_hodiny=mysqli_query($connect,$query_celkovo_hodiny);
                        $result_celkovo_hodiny=mysqli_fetch_array($apply_celkovo_hodiny);
                        array_push($celkovo_trvanie, $result_celkovo_hodiny['countHours']); 
                    }
                }
                if(empty($moduly_trvanie)){
                    array_push($moduly_trvanie, 0);
                }
                $index=0;
                $moduly_celkom=0;
                while($index<count($moduly_trvanie)){
                    $moduly_celkom+=$moduly_trvanie[$index];
                    $index++;
                }
                $moduly_priemer=$moduly_celkom/count($moduly_trvanie);
              
                if(empty($cd_trvanie)){
                    array_push($cd_trvanie, 0);
                }
                $index=0;
                $cd_celkom=0;
                while($index<count($cd_trvanie)){
                    $cd_celkom+=$cd_trvanie[$index];
                    $index++;
                }
                $cd_priemer=$cd_celkom/count($cd_trvanie);
              
                if(empty($celkovo_trvanie)){
                    array_push($celkovo_trvanie, 0);
                }
                $index=0;
                $celkovo_celkom=0;
                while($index<count($celkovo_trvanie)){
                    $celkovo_celkom+=$celkovo_trvanie[$index];
                    $index++;
                }
                $celkovo_priemer=$celkovo_celkom/count($celkovo_trvanie);
              
                if(empty($balenie_trvanie)){
                    array_push($balenie_trvanie, 0);
                }
                $index=0;
                $balenie_celkom=0;
                while($index<count($balenie_trvanie)){
                    $balenie_celkom+=$balenie_trvanie[$index];
                    $index++;
                }
                $balenie_priemer=$balenie_celkom/count($balenie_trvanie);
                
                echo "['".$months[$i]."', ".$cd_priemer.", ".$moduly_priemer.", ".$balenie_priemer.",".$celkovo_priemer."],";
    
            } ?>
           
      ]);

      var options = {
        hAxis: {
          title: 'Mesiac'
        },
        vAxis: {
          title: 'Hodiny'
        },
        colors: ['#AB0D06', '#00108e', '#118911', '#d3d300'],
        
      };

      var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }
    </script>
    