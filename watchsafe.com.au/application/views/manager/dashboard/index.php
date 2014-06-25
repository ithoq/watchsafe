<script type="text/javascript">
		$(document).ready(function () {
			
		    // Easy Pie Charts
			var easyPieChartDefaults = {
				animate: 2000,
				scaleColor: false,
				lineWidth: 12,
				lineCap: 'square',
				size: 100,
				trackColor: '#e5e5e5',
                onStep: function(value) {
		          this.$el.find('span').text(Math.floor(value));
		        }
			}
			
			<?php 
            $colors = array(); 
			foreach($result as $index => $value) {?>
					$('.chart<?=$index?>').easyPieChart($.extend({}, easyPieChartDefaults, {
						barColor: "<?php $colors[$value['imei']] = rand_color() ; echo $colors[$value['imei']];?>"
					}));
			<?php }?>

			setInterval(function(){
			$.post( "/ajax/getDashboard", {user_id:<?=$user_id?>} )
			.done(function( data ) {
				//console.log(data);
				var obj = JSON.parse(data);
                
			$.each(obj, function( index, value ) {
				 	
				    $.each(value, function( index1, value1 ) {
								    	
                          if (index1 === 'points'){


                             $('.chart'+index).data('easyPieChart').update(value1);
                             
                          }
                          if (index1 === 'odometer'){
                          	 $('.odometer'+index).text(value1);
                          }
				    });
				});
			});

			    

			}, 240000);

			setInterval(function(){
		    $.post( "/ajax/getData", {user_id:<?=$user_id?>} )
			.done(function( data ) {
				//console.log(data);
				var obj = JSON.parse(data);
                				 	
				    $.each(obj, function( index, value ) {
								    	
                          if (index === 'TotalAlerts'){
                             $('.alerts').text(value);
                          }
                            if (index === 'SpeedAlerts'){
                             $('.speed').text(value);
                          }
                            if (index === 'BreakingAlerts'){
                             $('.break').text(value);
                          }
                            if (index === 'WatchZoneAlerts'){
                             $('.watchzone').text(value);
                          }
                            if (index === 'mileage'){
                             $('.travell').text((value/1000).toFixed(1));
                          }
                            if (index === 'fuel'){
                             $('.fuel').text(value);
                          }
                           if (index === 'fuelLeft'){
                             $('.fuelLeft').text(value);
                          }
                          /*
                           if (index === 'nextService'){
                             $('.nextService').text(value);
                          }
                          */
				    });
			});
			}, 240000);

           // Weekly report graphs

			var visitsChartData = [

				<?php foreach($result as $key => $value) {?>
                            {
								label: '<?=$value["imei"]?>',
								data: [
								<?php 
                                $i=0;
								foreach ($weeklyScores[$value['imei']] as $key1 => $value1) { ?>
									['<?=$i++?>', '<?=$value1?>'],
								<?php }?>
								],
								filledPoints: true
							},

			    <?php } ?>
						          ];

							$('#visits-chart').simplePlot(visitsChartData, {
								series: {
									points: {
										show: true,
										radius: 1
									},
									lines: {
										show: true
									}
								},
								xaxis: {
									tickDecimals: 0
								},
								yaxis: {
									//tickSize:
								}
							}, {
								height: 205,
								tooltipText: "y + ' points at ' + x + ' weeks ago'"
							});
		});
</script>		
<!-- Server statistics
			================================================== -->
		<section class="row-fluid">
			<div class="well widget-pie-charts">
				<h3 class="box-header" align=center>
					Driver Scores
				</h3>
				<div class="box no-border non-collapsible" >
				<table align=center>
				<tr align=center>
				<?php foreach($result as $index => $value) {?>
				    <td align=center>				
					
					<div class="span2 pie-chart">
						<div class="chart<?=$index?>" data-percent="<?=$value['points']?>">
							<span><?=$value['points']?></span>%
						</div>
						<div class="caption">
							<?=$value['imei']?>
						</div>
						<div class="counter small" align=center>
							<span class='odometer<?=$index?>' align=center>
							<?=$value['odometer']?>
							</span>
						</div>
					</div>

					</td>
			    <?php }?>
			    </tr>
			    </table>				
				</div>
			</div>
		</section>
		<!-- / Server statistics -->
		<section class="row-fluid">
		
			<!-- Daily visits chart
				================================================== -->
			<div class="span8">
				<h3 class="box-header">
					<i class="icon-home"></i>
					
				</h3>
             <h3 class="box-header">
					<i class="icon-signal"></i>
					Weekly	Driver Scores
				</h3>
				<div class="box">
					<div id="visits-chart"></div>
				</div>
			</div>


			<!-- Daily statistics
				================================================== -->
			<div id="counters" class="span4">
				<h3 class="box-header">
					<i class="icon-signal"></i>
					Daily statistics
				</h3>
				<div class="box no-border no-padding widget-statistics">
				
					<div class="rounded-borders">
						<div class="counter small">
							<span class='alerts'>
							<?=$result1['TotalAlerts']?>
							</span>
						</div>
						<div class="counter-label">
							Driver alerts Today
						</div>
					</div>
					
					<div class="rounded-borders">
						<div class="counter small">
							<span class='speed'>
							<?=$result1['SpeedAlerts']?>
							</span>
						</div>
						<div class="counter-label">
							Speed Alerts Today	
						</div>
					</div>
					
					<div class="rounded-borders">
						<div class="counter small">
							<span class='break'>
							<?=$result1['BreakingAlerts']?>
							</span>
						</div>
						<div class="counter-label">
							Emergency Breaking Alerts Today
						</div>
					</div>
					
					<div class="rounded-borders">
						<div class="counter small">
							<span class='watchzone'>
							<?=$result1['WatchZoneAlerts']?>
							</span>
						</div>
						<div class="counter-label">
							Watchzone alerts today
						</div>
					</div>
										
					<div class="rounded-borders">
						<div class="counter small">
							<span class='travell'>
							<?=round($result1['mileage']/1000,1)?>
							</span>
						</div>
						<div class="counter-label">
							Kilometers travelled today	
						</div>
					</div>

					<div class="rounded-borders">
						<div class="counter small">
							<span class='nextService'>
							<?php
							if ($result1['nextService']!='N/A') {
                            $temp = explode('#',$result1['nextService']);
                            } else {
                            	$temp[0] = 'N/A';
                            	$temp[1] = '';
                            }
							echo round($temp[0],1)?>
							</span>
						</div>
						<div class="counter-label">
							Kilometers till next service <?php if ($temp[1]!='') echo '('.$temp[1].')'?>	
						</div>
					</div>

				    <div class="rounded-borders">
						<div class="counter small">
							<span class='fuelLeft'>
							<?=$result1['fuelLeft']?>
							</span>
						</div>
						<div class="counter-label">
							Fuel left in vehicle with lowest level
						</div>
					</div>

					<div class="rounded-borders">
						<div class="counter small">
							<span class='fuel'>
							<?=$result1['fuel']?>
							</span>
						</div>
						<div class="counter-label">
							Liters of fuel used	today
						</div>
					</div>
				</div>
			</div>
			<!-- / Daily statistics -->

	    </section>	
	    <!-- Alerts -->
	    <section class="row-fluid">
	        <div class="span6">
			<h3 class="box-header">
					<i class="icon-bullhorn"></i>
					Driver and Vehicle Alert
				</h3>
				<div class="box widget-support-tickets">

			<?php 
            //var_dump($result2);
            //exit();
			foreach ($result2 as $key => $value) {
             //var_dump($value);
             //exit();
			 ?>			
					<div class="ticket">
						<span class="label label-success" style="background-color:<?=$colors[$value['name']]?>;"><?=$value['name']?></span>
						<a href="#" title=""><?=$value['Type']?></span></a>
						<i class="<?=$value['icon']?> icon-2x" style="margin-right:10px;"></i>
						<span class="opened-by">

			                <?=isset($value['Data']) ? $value['Data'] : ''?> <br>
							<?=isset($value['Date']) ? "Date - ".date("l jS F g.i a",strtotime($value['Date'])) : ''?>
							
						</span>
					</div>
				

			<?php } ?>
			</div>
			</div>
    </section>	

<?php
function rand_color() {
return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}
?>