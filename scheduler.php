<?php 
	include "include.php"; 
	pageHeader("TCP Lighting Scheduler");

	/*** includes Sunrise/Sunset mod by Andrew Tsui ***/

?>

	<style>
		/* The switch - the box around the slider */
		
		.switch {
		  position: relative;
		  display: inline-block;
		  width: 60px;
		  height: 34px;
		}

		/* Hide default HTML checkbox */
		.switch input {display:none;}

		/* The slider */
		.slider {
		  position: absolute;
		  cursor: pointer;
		  top: 0;
		  left: 0;
		  right: 0;
		  bottom: 0;
		  background-color: #ccc;
		  -webkit-transition: .4s;
		  transition: .4s;
		}

		.slider:before {
		  position: absolute;
		  content: "";
		  height: 26px;
		  width: 26px;
		  left: 4px;
		  bottom: 4px;
		  background-color: white;
		  -webkit-transition: .4s;
		  transition: .4s;
		}

		input:checked + .slider {
		  background-color: #2196F3;
		}

		input:focus + .slider {
		  box-shadow: 0 0 1px #2196F3;
		}

		input:checked + .slider:before {
		  -webkit-transform: translateX(26px);
		  -ms-transform: translateX(26px);
		  transform: translateX(26px);
		}

		/* Rounded sliders */
		.slider.round {
		  border-radius: 34px;
		}

		.slider.round:before {
		  border-radius: 50%;
		}
		
		.scheduledTask{
			margin: 20px 0px;
			background-color: #fff;
		}
		
		.scheduledTask  > div{ margin: 10px 0; }
		
		.fxTo{ width: 200px; }
		
		.ifSunTime,
		.ifFixedTime,
		.ifDim,
		.ifSwitch{ display: none; }
		
		.scheduledTask.tm-SunTime .ifSunTime{ display: block; }
		.scheduledTask.tm-FixedTime .ifFixedTime{ display: block; }
		.scheduledTask.fx-Dim .ifDim{ display: block; }
		.scheduledTask.fx-Switch .ifSwitch{ display: block; }
		
		.scheduledTask { border: 1px solid #000; padding: 20px; position: relative; }
		.scheduledTask .delTask{
			position: absolute; top: -1px; right: -1px; color: #f00; cursor: pointer; height: 20px; width: 20px; border: 1px solid #000; text-align: center; margin: 0;
		}
		
		.taskNote{ width: 100%; clear: both; }
		.taskNote textarea{ height: 50px; width: 100%; }
		
	</style>
	<script>
		$(function(){
			function runSchedule(){
				var d = new Date();
				console.log("Running Schedule " + d);
				$.get("/runSchedule.php", function( data ) {
					console.log(data);
				});
			}
			
			$('#runOnce').click(function(){
				runSchedule();
			});
			
			$('#poll').click(function(){
				$('#runOnce').attr('disabled', true);
				$(this).unbind();
				$(this).addClass('running');
				setInterval(runSchedule, 30000);
			});
		

		
		/* Output Add Scheduled Task */
		<?php ob_start(); getScheduleView();$schedule = ob_get_clean();	?>	
		var addSchedule = '<?php echo preg_replace( "/\r|\n/", "",$schedule); ?>';	
		
		function SelectMoveRows(SS1,SS2){
			//function shamelessly borrowed from http://johnwbartlett.com/cf_tipsntricks/index.cfm?TopicID=86
			var SelID='';
			var SelText='';
			// Move rows from SS1 to SS2 from bottom to top
			for (i=SS1.options.length - 1; i>=0; i--){
				if (SS1.options[i].selected == true){
					SelID=SS1.options[i].value;
					SelText=SS1.options[i].text;
					var newRow = new Option(SelText,SelID);
					SS2.options[SS2.length]=newRow;
					SS1.options[i]=null;
				}
			}
		}
		
		function bindEvents(){

			$('select[name="TIME_TYPE"]').change(function(){
				$(this).closest('.scheduledTask').removeClass('tm-SunTime');
				$(this).closest('.scheduledTask').removeClass('tm-FixedTime');
				
				if( $(this).find('option:selected').val() == "FIXED" ){
					$(this).closest('.scheduledTask').addClass('tm-FixedTime');
				}else{
					$(this).closest('.scheduledTask').addClass('tm-SunTime');
				}
			});
		
			$('select[name="FX"]').change(function(){
				$(this).closest('.scheduledTask').removeClass('fx-Dim');
				$(this).closest('.scheduledTask').removeClass('fx-Switch');
				
				if( $(this).find('option:selected').val() == "DIM" ){
					$(this).closest('.scheduledTask').addClass('fx-Dim');
				}else{
					$(this).closest('.scheduledTask').addClass('fx-Switch');
				}
			});
			
			$('.schedule-slider').slider({
				range: "min",
				min: 0,
				max: 100,
				//value: $(this).attr('data-level'),
				create: function( event, ui ){
					$(this).slider("option", "value", $(this).parent().find("input[name='DIM_SCHED']").val() );
				},
				stop: function(event, ui) {
					$(this).parent().find('input[name="DIM_SCHED"]').val( ui.value );
				},
				slide: function( event, ui ) {
					$(this).parent().find('input[name="DIM_SCHED"]').val( ui.value );
				}
			});
			
			$('select[name="TIME_TYPE"]').change();			

			$('select[name="FX"]').change();
			
			$('.btnAdd, .btnRemove').click(function(){
				
				var available =  $(this).closest('.deviceList').find('select.available')[0];
				var selected = $(this).closest('.deviceList').find('select.selected')[0];

				if( $(this).hasClass('btnAdd') ){
					SelectMoveRows(available,  selected );
				}else{
					SelectMoveRows( selected, available );
				}
				
			});
			
			$('.delTask').click(function(){ $(this).parent().remove(); });
		
		}
		
		$('#add').click(function(){
			$('#events').append(addSchedule);
			bindEvents();
		});
		
		
	
		
		$('#save').click(function(){
			$('select[name="DEVICE_SELECTED"] option').each(function(){ $(this).attr('selected','selected'); });
			var schedules = [];
			
			$('.scheduledTask').each(function(){
				schedules.push( $(this).find(':input').serializeArray() );
			});
			
			/* Sample
			[[{"name":"DAY_MON","value":"on"},{"name":"DAY_WED","value":"on"},{"name":"DAY_FRI","value":"on"},{"name":"HOUR","value":"8"},{"name":"MIN","value":"1"},{"name":"FX","value":"SWITCH"},{"name":"DIM_SCHED","value":""},{"name":"SWITCH_SCHED","value":"1"},{"name":"DEVICE_SELECTED","value":"359905593582463444"}],[{"name":"DAY_MON","value":"on"},{"name":"DAY_WED","value":"on"},{"name":"DAY_FRI","value":"on"},{"name":"HOUR","value":"8"},{"name":"MIN","value":"10"},{"name":"FX","value":"SWITCH"},{"name":"DIM_SCHED","value":""},{"name":"DEVICE_SELECTED","value":"359905593582463444"}]]
			*/
			//$.ajaxSetup({ cache: false, async: false }); 
			
		
			
			
			$.ajax({
				type: 'POST',
				url: 'runSchedule.php',
				data: {'schedule': JSON.stringify(schedules)},
				success: function(msg) {
				  alert("Saved");
				}
			  });
			
			
		});
		
		bindEvents();
		
		
		$( "#events" ).sortable({
      placeholder: "ui-state-highlight"
    });
		$( "#events" ).disableSelection();
	});
	</script>
<div id="toolBar"><a href="index.php">Lighting Controls</a> | <a href="apitest.php">API Test Zone</a> | <a href="scenes.php">Scenes/Smart Control</a></div>
<?php
	/*
	 *
	 * TCP Ligthing Web UI Test Script - By Brendon Irwin
	 * 
	 
	 */
	
	function getScheduleView($task=array()){
		?>
		<form class="scheduledTask">
		<div class="delTask">X</div>
			<div class="taskNote">
				<p>Schedule title/note:</p>
				<textarea name="TITLE_NOTE"><?php echo (isset($task["TITLE_NOTE"])) ? $task["TITLE_NOTE"] : ""; ?></textarea>
			</div>
			<div class="daysOfWeek">
				<label><input type="checkbox" name="DAY_MON" <?php echo (isset($task["DAY_MON"]) && $task["DAY_MON"] == "on") ? " checked" : "";  ?>/> Monday</label>
				<label><input type="checkbox" name="DAY_TUE" <?php echo (isset($task["DAY_TUE"]) && $task["DAY_TUE"] == "on") ? " checked" : "";  ?>/> Tuesday</label>
				<label><input type="checkbox" name="DAY_WED" <?php echo (isset($task["DAY_WED"]) && $task["DAY_WED"] == "on") ? " checked" : "";  ?>/> Wednesday</label>
				<label><input type="checkbox" name="DAY_THU" <?php echo (isset($task["DAY_THU"]) && $task["DAY_THU"] == "on") ? " checked" : "";  ?>/> Thursday</label>
				<label><input type="checkbox" name="DAY_FRI" <?php echo (isset($task["DAY_FRI"]) && $task["DAY_FRI"] == "on") ? " checked" : "";  ?>/> Friday</label>
				<label><input type="checkbox" name="DAY_SAT" <?php echo (isset($task["DAY_SAT"]) && $task["DAY_SAT"] == "on") ? " checked" : "";  ?>/> Saturday</label>
				<label><input type="checkbox" name="DAY_SUN" <?php echo (isset($task["DAY_SUN"]) && $task["DAY_SUN"] == "on") ? " checked" : "";  ?>/> Sunday</label>
				<label><input type="checkbox" name="DAY_ALL" <?php echo (isset($task["DAY_ALL"]) && $task["DAY_ALL"] == "on") ? " checked" : "";  ?>/> Everyday</label>
			</div>

			<div class="timeOfDay">
				<label>Time: 
					<select name="TIME_TYPE"><option value="FIXED" <?php echo (isset($task["TIME_TYPE"]) && $task["TIME_TYPE"] == "FIXED") ? " selected" : ""; ?>>FIXED</option><option value="SUNRISE" <?php echo (isset($task["TIME_TYPE"]) && $task["TIME_TYPE"] == "SUNRISE") ? " selected" : ""; ?>>SUNRISE</option><option value="SUNSET" <?php echo (isset($task["TIME_TYPE"]) && $task["TIME_TYPE"] == "SUNSET") ? " selected" : ""; ?>>SUNSET</option>
					</select>
				</label>
                  </div>
					
			<div class="timeType">

<?php /***
       *
       *    Added Sunrise-Sunset settings
       *    2017-06-09 by Andrew Tsui
       *
       */
?>
				
				<div class="ifSunTime">


					<label>Offset Hours: <select name="OFFSET_HOUR"><?php for($x=0; $x<=12; $x++){ echo '<option value="'.sprintf("%02d",$x).'"'.( (isset($task["OFFSET_HOUR"]) && $task["OFFSET_HOUR"] == $x) ? " selected" : "").'>'.sprintf("%02d",$x).'</option>'; } 
						?>
						</select>
					</label>
					<label>Minutes: 
						<select name="OFFSET_MIN">
						<?php for($x=0; $x<=59; $x++){ echo '<option value="'.sprintf("%02d",$x).'"'.( (isset($task["OFFSET_MIN"]) && $task["OFFSET_MIN"] == $x) ? " selected" : "").'>'.sprintf("%02d",$x).'</option>'; } 
						?>
						</select>
					</label>
					<label> 
						<select name="OFFSET_DIR"><option value="before" <?php echo (isset($task["OFFSET_DIR"]) && $task["OFFSET_DIR"] == "before") ? " selected" : ""; ?>>before</option><option value="after" <?php echo (isset($task["OFFSET_DIR"]) && $task["OFFSET_DIR"] == "after") ? " selected" : ""; ?>>after</option>
						</select>
					</label>

				</div>

				<div class="ifFixedTime">
					<label>Hour: 
					<select name="HOUR">
					<?php for($x=0; $x<=23; $x++){
						if( $x == 0 ){ 
							echo '<option value="'.sprintf("%02d",$x).'"'.( (isset($task["HOUR"]) && $task["HOUR"] == $x) ? " selected" : "").'>12 AM - MIDNIGHT</option>';
						}else if($x == 12){
							echo '<option value="'.sprintf("%02d",$x).'"'.( (isset($task["HOUR"]) && $task["HOUR"] == $x) ? " selected" : "").'>12 PM - NOON</option>';	
						}else{
							if( $x > 12 ){
								echo '<option value="'.sprintf("%02d",$x).'"'.( (isset($task["HOUR"]) && $task["HOUR"] == $x) ? " selected" : "").'>'. ($x - 12) .( $x >= 12 ? ' PM' : ' AM' ).'</option>';
								}else{
									echo '<option value="'.sprintf("%02d",$x).'"'.( (isset($task["HOUR"]) && $task["HOUR"] == $x) ? " selected" : "").'>'.$x.( $x >= 12 ? ' PM' : ' AM' ).'</option>';
							}
						}
					} ?>
					</select>
					</label>

					<label>Minute: 
					<select name="MIN">
					<?php for($x=0; $x<=59; $x++){ 
						echo '<option value="'.sprintf("%02d",$x).'"'.( (isset($task["MIN"]) && $task["MIN"] == $x) ? " selected" : "").'>'.sprintf("%02d",$x).'</option>';
					} 
					?>
					</select>
					</label>
				</div>
			</div>



			<div class="functionTrigger">
				<label>Function: <select name="FX"><option value="DIM" <?php echo (isset($task["FX"]) && $task["FX"] == "DIM") ? " selected" : ""; ?>>DIM</option><option value="SWITCH" <?php echo (isset($task["FX"]) && $task["FX"] != "DIM") ? " selected" : ""; ?>>SWITCH</option></select></label>
			</div>
			<div class="fxTo">
				<div class="ifDim">
					<div class="schedule-slider" data-device-id="all"></div>
					<input name="DIM_SCHED" type="hidden" value="<?php echo (isset($task["FX"]) && $task["FX"] == "DIM") ? $task["DIM_SCHED"] : ""; ?>" />
				</div>
				<div class="ifSwitch">
					<label class="switch">
					  <input name="SWITCH_SCHED" value="1" type="checkbox" <?php echo (isset($task["FX"]) && $task["FX"] != "DIM" && $task["SWITCH_SCHED"] == 1) ? "checked" : ""; ?>>
					  <div class="slider round"></div>
					</label>
				</div>
			</div>
			<div class="deviceList">
			<table>
				<tr>
					<td>Available</td><td>&nbsp;</td><td>Selected</td>
				</tr>
				<tr>
					<td>
						<select size="9" class="available" name="DEVICE_AVAILABLE" multiple>
						<?php
							$devices = getDevices();
							$selected = "";
							foreach($devices as $device){
								//if did is not in here
								if( isset($task) && isset($task["devices"]) && is_array($task["devices"]) && in_array($device['did'], $task["devices"]) ){
									$selected.= '<option value="'.$device['did'].'" selected>'.$device['prodtype'].' - '.$device['name'].'</option>';
								}else{
									echo '<option value="'.$device['did'].'">'.$device['prodtype'].' - '.$device['name'].'</option>';
								}
							}
						?>
						</select>
					</td>
					<td>
						<input type="Button" value="Add >>" class="btnAdd"><br />
						<br />
						<input type="Button" value="<< Remove" class="btnRemove">
					</td>
					<td>
						<select size="9" class="selected" name="DEVICE_SELECTED" multiple>
						<?php
							echo $selected;
						?>
						</select>
					</td>
				</tr>
			</table>
                  </div>
		</form>
	

<?php
}

	echo '<div class="container">';
		echo '<h1>Device Schedule</h1>';
		echo '<button id="runOnce">Run Now</button> <button id="poll">Poll continuously</button>';
		echo '<p>Note, polling continuously will work if the tab this script is running on has focus. Consider setting up a batch file to run the schedule. See documentation and runschedule.bat</p>';
		echo '<p>Tasks can be dragged and dropped to re-order. Just click save after.</p>';
		$sun_info = date_sun_info(time(), LATITUDE, LONGITUDE);
		echo "Sunrise Today: " . date("H:i", $sun_info['sunrise']) . "    ";
		echo "Sunset Today:  " . date("H:i", $sun_info['sunset'])  . "<br>";
	echo '</div>';
?>
 
<div id="events" class="container"> 
	<?php
		if( file_exists("schedule.sched") ){
			$array = file_get_contents("schedule.sched");
			$tasks = unserialize ($array);
			ob_start();
			foreach($tasks as $task){
				getScheduleView($task);
			}
			ob_end_flush();
		}else{
			echo $schedule;
		}
	?>
 </div>
 <div class="container">
 <button id="save">Save</button>
 <button id="add">Add Task</button> 
 </div>

<?php

  pageFooter();

?>
