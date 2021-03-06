<?php

class health_facility extends module{

	function health_facility(){
		$this->author="darth_ali";
		$this->version="0.3-".date("Y-m-d");
		$this->module="health_facility";
		$this->description="CHITS Module - Health Facility";

		/* the health_facility module will: 
			1. allow assigning of barangays to health facility (i.e. municipality w/ two or more centers)
			2. embed the GPS latitude and longitude of the facility for mapping purposes
			3. contains official DOH health facility code for integration purposes
			4. inclusion of staff assignment and creation of barangay health station
			4. and many more !
		*/
	}

	function init_deps(){
		module::set_dep($this->module,"module");
		module::set_dep($this->module,"barangay");
		module::set_dep($this->module,"healthcenter");
	}	

	function init_menu(){
		if(func_num_args()>0):
			$arg_list = func_get_args();
		endif;

		module::set_menu($this->module,"Health Facility","LIBRARIES","_health_facility");
		module::set_menu($this->module,"Brgy Health Station","LIBRARIES","_form_bhs");
		module::set_detail($this->description,$this->version,$this->author,$this->module);
	}

	function init_help(){}

	function init_lang(){}

	function init_sql(){}

	function drop_tables(){}

	
	//----- CUSTOM BUILT FUNCTION ---- //
	
	function _health_facility(){

		if($_POST["submit_brgy"]):
			$this->assign_hf_brgy($_POST);
		endif;

		//$q_health_facility = mysql_query("SELECT a.facility_id, a.facility_name,b.place_name FROM m_lib_health_facility a, m_lib_psgc_code b WHERE RPAD(a.psgc_provcode,(length(a.psgc_provcode)+2),'00')=b.municipality_code ORDER by b.place_name ASC, a.facility_name ASC") or die("Cannot query 47 ".mysql_error());

		//$q_health_facility = mysql_query("SELECT a.facility_id, a.facility_name,b.place_name FROM m_lib_health_facility a, m_lib_psgc_code b WHERE a.psgc_provcode=b.province_code ORDER by b.place_name ASC, a.facility_name ASC") or die("Cannot query 47 ".mysql_error());

		$q_health_facility = mysql_query("SELECT a.facility_id, a.facility_name,a.psgc_provcode FROM m_lib_health_facility a ORDER BY a.facility_name ASC") or die("Cannot query 47 ".mysql_error());

		$q_barangay = mysql_query("SELECT barangay_id, barangay_name FROM m_lib_barangay ORDER BY barangay_name ASC") or die("Cannot query 47 ".mysql_error());

		$this->unassign_brgy($_POST,$_GET);

		echo "<form action='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]#health_facility' method='POST' name='form_health_facility'>";

		echo "<table  style=\"font-family: arial;background-color:#99CCCC\">";
		echo "<a name='health_facility'></a>";
		echo "<tr><td colspan='2' valign='top' align='center' style=\"background-color: #666666;color: #FFFF66;text-align: center;font-weight: bold;font-size:10pt;\">HEALTH FACILITY - BARANGAY MAPPING OF CATCHMENT AREA</td></tr>";
		echo "<tr>";
		
		echo "<td valign='top' colspan='2'>Health Facility&nbsp;&nbsp;<select name='sel_health_facility' size='1'>";
		
		while(list($fac_id,$fac_name,$prov_code)=mysql_fetch_array($q_health_facility)){
			$q_prov = mysql_query("SELECT place_name FROM m_lib_psgc_code WHERE province_code='$prov_code' AND classification=''") or die("Cannot query 72 ".mysql_error());
			list($place) = mysql_fetch_array($q_prov);

			echo "<option value='$fac_id'>$fac_name ($place)</option>";
		}
		echo "</select></td>";
		
		echo "</tr>";

		echo "<tr>";
		echo "<td valign='center'>Select Barangay </td>";
		echo "<td>";
		echo "<table>";
		
		
		echo "<tr>";			
		echo "<select name='sel_barangay[]' class='textbox' size='10' MULTIPLE>";
		while(list($barangay_id,$barangay_name)=mysql_fetch_array($q_barangay)){
			echo "<option value='$barangay_id'>$barangay_name</option>";
		}
		echo "</select></td>";
		echo "</tr>";

		echo "</table>";
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td colspan='2' align='center'><input type='submit' name='submit_brgy' value='Assign Barangay/s to Health Facility' style='border: 1px solid black' />&nbsp;&nbsp;&nbsp;<input type='reset' name='reset_brgy' value='Reset' style='border: 1px solid black'></td>";
		echo "</tr>";

		echo "</table>";
		echo "</form>";


		$this->show_barangay_hf();
	}


	function assign_hf_brgy($post_vars){
		$str_brgy = implode(",",$post_vars["sel_barangay"]);
		 
		foreach($post_vars["sel_barangay"] as $key=>$value){
			$q_brgy = mysql_query("SELECT barangay_id FROM m_lib_health_facility_barangay WHERE barangay_id='$value'") or die("Cannot query 116 ".mysql_error());
			if(mysql_num_rows($q_brgy)):
				echo "<script language='Javascript'>";
				echo "window.alert('Health facility and barangay was not mapped. Please check if the barangay is already assigned to a health facility. To unassign a barangay, click the barangay name at the table below.')";
				echo "</script>";
			else:
				$insert_value = mysql_query("INSERT INTO m_lib_health_facility_barangay SET barangay_id='$value',facility_id='$post_vars[sel_health_facility]'") or die("Cannot query 109 ".mysql_error());
			endif;
		}

		if($insert_value):
			echo "<script language='Javascript'>";
			echo "window.alert('Health facility and barangay mapping done!')";
			echo "</script>";
		endif;
	}

	function show_barangay_hf(){
		$q_hf = mysql_query("SELECT DISTINCT(a.facility_id),b.facility_name FROM m_lib_health_facility_barangay a,m_lib_health_facility b WHERE a.facility_id=b.facility_id ORDER by b.facility_name ASC") or die("Cannot query 133 ".mysql_error());		

		if(mysql_num_rows($q_hf)!=0):
			echo "<table style=\"background-color: #99CCCC;text-align: center;font-size:10px;\">";
			echo "<tr style=\"background-color: #666666;color: #FFFF66;text-align: center;font-weight: bold;font-size:10px;\"><td>HEALTH FACILITY</td>";
			echo "<td>BARANGAYS (click barangay name to remove barangay)</td>";
			echo "<td>Barangay Health Stations</td>";
			echo "</tr>";

			while(list($fac_id,$fac_name) = mysql_fetch_array($q_hf)){	
				$arr_brgy = array();
				$q_brgy = mysql_query("SELECT a.barangay_name,a.barangay_id FROM m_lib_barangay a,m_lib_health_facility_barangay b WHERE a.barangay_id=b.barangay_id AND b.facility_id='$fac_id' ORDER BY a.barangay_name") or die("Cannot query 142 ".mysql_error());

				while(list($brgy_name,$brgy_id) = mysql_fetch_array($q_brgy)){	
					array_push($arr_brgy,array($brgy_name,$brgy_id));
				}
				
				$str_brgy = implode(",",$arr_brgy);
				
				echo "<tr>";
				echo "<td>".$fac_name."</td>";
				echo "<td>";
				foreach($arr_brgy as $key=>$value){
					echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&action=delete&brgy_id=$value[1]&facility_id=$fac_id'>".$value[0]."</a> /";
				}
				echo "</td>";

				echo "<td>";
				echo "<input type='button' name='btn_add_bhs' style='border: 1px solid black' value='Add BHS' onclick='add_bhs($fac_id);'/>";
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
		else:

		endif;
	}

	function unassign_brgy(){
		if(func_num_args()>0):
			$arr = func_get_args();
			$postvars = $arr[0];
			$getvars = $arr[1];
		endif;

		if($getvars["action"]=='delete'):
			
			$q_brgy = mysql_query("SELECT barangay_id,barangay_name FROM m_lib_barangay WHERE barangay_id='$getvars[brgy_id]'") or die("Cannot query: 176 ".mysql_error());

			$q_facility = mysql_query("SELECT facility_name FROM m_lib_health_facility WHERE facility_id='$getvars[facility_id]'") or die("Cannot query: 178 ".mysql_error());
			
			list($facility_name) = mysql_fetch_array($q_facility);
		
			if(mysql_num_rows($q_brgy)!=0):
				list($brgy_id,$brgy_name) = mysql_fetch_array($q_brgy);
				
				//echo "You are about to unassign <b>".$brgy_name."</b> from <b>".$facility_name."</b><br>";
				if(module::confirm_delete($getvars["menu_id"],$postvars,$getvars)):
					$delete_brgy = mysql_query("DELETE FROM m_lib_health_facility_barangay WHERE barangay_id='$getvars[brgy_id]'") or die("Cannot query 188 ".mysql_error());

					if($delete_brgy):
						echo "<script language='Javascript'>";
						echo "window.alert('The barangay $brgy_name was successfully been unassigned from the $facility_name!')";
						echo "</script>";
					endif;

				else:

				endif;

			else:
				echo "<script language='Javascript'>";
				echo "window.alert('Cannot delete. The barangay ID does not exists!')";
				echo "</script>";
			endif;
		endif;
		
	}

	function form_bhs(){
		echo "<form action='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]#bhs' method='POST' name='form_bhs'>";
		echo "<table>";
		echo "<thead><td>BARANGAY HEALTH STATIONS</td></thead>";
		echo "<tr><td>Name of Barangay Health Station</td>";
		echo "<td><input type='text' name='txt_bhs' size='10'></td></tr>";
		echo "</form>";

	}
}
?>