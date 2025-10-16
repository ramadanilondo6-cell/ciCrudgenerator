<?php

if (!defined('BASEPATH'))

    exit('No direct script access allowed');



class Module extends CI_Controller {



    function __construct() {

        parent::__construct();

        ob_start();



        if (version_compare(CI_VERSION, '2.1.0', '<')) {

            $this->load->library('security');

        }

        $this->load->helper('url');
        $this->load->helper('file');
        $this->load->library('ion_auth');

        $this->load->library('form_validation');

    }



    function index() {

        if (!$this->ion_auth->logged_in()) {
            redirect('/auth/login/');
        } else {
            $data['tables'] = $this->db->list_tables();
            $data['modules'] = $this->get_modules();
            $this->load->view('admin/module/manage', $data);
        }
    }

    public function add() {

        if (!$this->ion_auth->logged_in())
        {
            redirect('auth/login');
        }
        
        $table = $_POST['table_name'];
        $table_new = $_POST['table_name'];
        $tab_fields = $this->db->field_data($table);
        $primary_key = 'id';
        foreach ($tab_fields as $field)
        {
            if($field->primary_key===1){
                $primary_key = $field->name;
            }
        }
        $cntlr = ucfirst($table);

        $file = $this->config->item('base_path') . "application/views/header.php";
        $data = file_get_contents($file);
        $temp_strng = 'BO : ' . ucfirst($table);
        if (strpos($data, $temp_strng) !== false) {

        } else {
            $newMenu = '
				<!-- BO : Module -->
                <li <?php if($contr == \'module\'){?>class="active "<?php } ?>  >
                    <a href="javascript:;"><i class="fa fa-users"></i><span class="title">Module</span>
                        <?php if($contr == \'module\'){?><span class="selected"></span><?php } ?>
                      <span class="arrow <?php if($contr == \'module\'){?>open<?php } ?>"></span>
                    </a>
                    <ul class="nav nav-second-level">
                      <li <?php if($contrnew == \'module/add\'){?>class="active "<?php } ?>>
                        <a href="<?php echo base_url()?>admin/module/add"><i class="fa fa-angle-double-right">
                            </i>Add Module</a>
                      </li>
                      <li <?php if($contrnew == \'module/\'){?>class="active"<?php } ?>>
                        <a href="<?php echo base_url()?>admin/module/index"><i class="fa fa-gear"></i>Manage Module</a>
                      </li>                       
                    </ul>
                </li>
                <!--  EO : Module -->

               <!--  @@@@@#####@@@@@ -->

                ';
            $newMenu = str_replace("module", $table, $newMenu);
            $newMenu = str_replace("Module", ucfirst($table), $newMenu);
            $final_data = str_replace("<!--  @@@@@#####@@@@@ -->", $newMenu, $data);
            file_put_contents($file, $final_data);
        }

        $controller_path = $this->config->item('base_path') . "application/controllers/admin/";
        $file = $controller_path . ucfirst($table) . '.php';
        $handle = fopen($file, 'w') or die('Cannot open file:  ' . $file);
        $current = "";
        $myfile = fopen($controller_path . "module_files/controller.php", "r") or die("Unable to open file!");
        $current = fread($myfile, filesize($controller_path . "module_files/controller.php"));
        fclose($myfile);

        file_put_contents($file, $current);
        $data = file_get_contents($file);

        $data = str_replace("=*=", "$", $data);
        $data = str_replace("=@=", "->", $data);

        $alias = "";
        $select_join = "";
        $validations = "";
        $fields = "";
        $foreign_tables = '';
        $sort_fields_arr = array();
        $status_field = 'status';
        $valds = "";
        $status_field_string="";
        $status_arr =array();
        foreach ($_POST['ischeck'] as $key => $value) {
            $vals_str = "";

            if (isset($_POST['required_' . $value])) {
                $vals_str = implode('|', $_POST['required_' . $value]);
            } else{
                $vals_str = 'trim';
            }

            if ($_POST[$value][0] != 'select') {
                $sort_fields_arr[] = "'" . $value . "'";
                $sort_fields_arr2[] = "'" . $value . "'";
            }

            $all_one_to_many_relations = json_decode($_POST['one_to_many']);

            foreach ($all_one_to_many_relations as $key => $value_rel) {
                $one_to_many_table = $value_rel->rel_table;
                $one_to_many_field = $value_rel->rel_field;
                if (isset($one_to_many_table) && !empty($one_to_many_table)) {
                }
            }

            if ($_POST[$value][0] == 'select') {
                $foreign_tables .= '$data["' . $_POST[$value]["selected_table"] . '"]=$this->' . $table . '->getListTable("' . $_POST[$value]["selected_table"] . '");';
                $alias .= " , " . $_POST[$value]["selected_table"] . "." . $_POST[$value]["value"] . " as $value ";
                $select_join .= ' $this->db->join("' . $_POST[$value]["selected_table"] . '", "' . $table . '.' . $value . ' = ' . $_POST[$value]["selected_table"] . '.' . $_POST[$value]["key"] . '", "left"); ';

                $sort_fields_arr[] = "'" . $_POST[$value]["selected_table"] . "." . $_POST[$value]["value"] . "'";
                $sort_fields_arr2[] = "'" . $value . "'";
            }
            $cap_field = ucfirst($value);
            if ($_POST[$value][0] == "status") {
                $validations .= "@@@this->form_validation->set_rules('$value', '$cap_field Name', 'trim|xss_clean');";
                $fields .= "\t\t\t@@@saveData['$value'] = set_value('$value');\n";
                $status_field = $value;
                $status_arr[] = "'$value'";
            } elseif ($_POST[$value][0] == "image") {
                $validations .= '$this->form_validation->set_rules("' . $value . '", "' . ucfirst($value) . '", "trim|xss_clean");
         $this->' . $table . '->uploadData($photo_data, "' . $value . '", "photo_path","","gif|jpg|png|jpeg");
	    if(isset($photo_data["photo_err"]) and !empty($photo_data["photo_err"]))
	    {
	     $data["errors"]=$photo_data["photo_err"];
	     $this->form_validation->set_rules("' . $value . '","' . ucfirst($value) . '","' . $vals_str . '");
	    }';

                $fields .= 'if(isset($photo_data["' . $value . '"]) && !empty($photo_data["' . $value . '"]))
		{
	      $saveData["' . $value . '"] = $photo_data["' . $value . '"];
        }';
            } elseif ($_POST[$value][0] == "checkbox") {

                $validations .= "\t\t@@@this->form_validation->set_rules('$value', '$cap_field Name', 'xss_clean');\n";
                $fields .= "\t\t@@@saveData['$value'] = addslashes(implode(', ', \$_POST['$value']));\n";
            } elseif ($_POST[$value][0] == "radio") {
                $validations .= "\t\t@@@this->form_validation->set_rules('$value', '$cap_field Name', '$vals_str');\n";
                $fields .= "\t\t\t@@@saveData['$value'] = set_value('$value');\n";
            } else {
                $validations .= "\t\t@@@this->form_validation->set_rules('$value', '$cap_field Name', '$vals_str');\n";
                $fields .= "\t\t\t@@@saveData['$value'] = set_value('$value');\n";
            }
        }

        $return_multi_selected_id = "";
        if (isset($_POST["multiselect"])) {
            for ($i=0; $i < count($_POST["multiselect"]["table"]); $i++) {
                if ($_POST["multiselect"]["table"][$i]) {
                    $rtable = $_POST["multiselect"]["r_table"][$i];
                    $field1 = $_POST["multiselect"]["r_main"][$i];
                    $field2 = $_POST["multiselect"]["r_multi"][$i];

                    $call_multi_add .= "\n\t\t\t@@@this->==table==->multiSelectInsert(\"$rtable\", \"$field2\", @@@insert_id, \"$field1\", @@@_POST['".$_POST["multiselect"]["table"][$i]."']);\n";
                    $call_multi_edit .= "\n\t\t\t@@@this->==table==->multiSelectInsert(\"$rtable\", \"$field2\", @@@id, \"$field1\", @@@_POST['".$_POST["multiselect"]["table"][$i]."']);\n";
                    $list_tbl .= "\t@@@data['".$_POST["multiselect"]["table"][$i]."']=@@@this->==table==->getList('".$_POST["multiselect"]["table"][$i]."');\n";
                    $return_multi_selected_id.= "\n\t\t\t@@@data['selected_".$_POST["multiselect"]["table"][$i]."'] = @@@this->==table==->getSelectedIds(\"$rtable\", @@@id, \"$field1\", \"$field2\");\n";
                    $multi_selected_id.= "\n\t\t\t@@@selected_".$_POST["multiselect"]["table"][$i]."_id = @@@this->==table==->getSelectedIds(\"$rtable\", @@@id, \"$field1\", \"$field2\");\n";
                    $return_multi_selected_data.= "\n\t\t\t@@@data['selected_".$_POST["multiselect"]["table"][$i]."_data'] = array();
            if (isset(@@@selected_".$_POST["multiselect"]["table"][$i]."_id) && !empty(@@@selected_".$_POST["multiselect"]["table"][$i]."_id)) {
        \n\t\t\t\t@@@data['selected_".$_POST["multiselect"]["table"][$i]."_data'] = @@@this->==table==->getSelectedData('".$_POST["multiselect"]["table"][$i]."', '".$_POST["multiselect"]["value"][$i]."', @@@selected_".$_POST["multiselect"]["table"][$i]."_id);\n\t\t}\n";
                }
            }
        }

        $sort_fields_arr = implode(', ', $sort_fields_arr);
        $sort_fields_arr2 = implode(', ', $sort_fields_arr2);
        $data = str_replace('***foreign_table***', $foreign_tables, $data);
        $data = str_replace("==validation==", $validations, $data);
        $data = str_replace("==call_multi_add==", $call_multi_add, $data);
        $data = str_replace("==call_multi_edit==", $call_multi_edit, $data);
        $data = str_replace("==return_multi_selected_id==", $return_multi_selected_id, $data);
        $data = str_replace("==multi_selected_id==", $multi_selected_id, $data);
        $data = str_replace("==return_multi_selected_data==", $return_multi_selected_data, $data);
        $data = str_replace("==fields==", $fields, $data);
        $data = str_replace("{{status_field}}", $status_field, $data);
        $data = str_replace("++sort_fields_arr++", $sort_fields_arr, $data);
        $data = str_replace("++sort_fields_arr2++", $sort_fields_arr2, $data);

        $data = str_replace("==list_tbl==", $list_tbl, $data);
        $data = str_replace("==table==", $table, $data);
        $data = str_replace("controller_name", ucfirst($table), $data);
        $data = str_replace("==primary_key==", $primary_key, $data);
        $data = str_replace("@@@", "$", $data);

        $status_field_string = implode(", ", $status_arr);
        $data = str_replace("==status_field_string==", $status_field_string, $data);
        file_put_contents($file, $data);

        $model_path = $this->config->item('base_path') . "application/models/admin/";
        $file = $model_path . ucfirst($table) . '_model.php';
        $handle = fopen($file, 'w') or die('Cannot open file:  ' . $file);

        $current = "";
        $exist_model_path = $this->config->item('base_path') . "application/controllers/admin/";
        $myfile = fopen($exist_model_path . "module_files/model.php", "r") or die("Unable to open file!");
        $current = fread($myfile, filesize($exist_model_path . "module_files/model.php"));
        fclose($myfile);

        file_put_contents($file, $current);

        $data = file_get_contents($file);
        $current = str_replace("@@@", "$", $data);
        $current = str_replace("++sort_fields_arr++", $sort_fields_arr, $current);
        $current = str_replace("==table==", ucfirst($table), $current);
        $current = str_replace("==select_alias==", $alias, $current);
        $current = str_replace("==primary_key==", $primary_key, $current);
        $current = str_replace("==select_join==", $select_join, $current);
        file_put_contents($file, $current);

        $ori_path = $this->config->item('base_path') . "application/views/admin/";
        $view_path = $this->config->item('base_path') . "application/views/admin/$table/";
        $add_file = $view_path . 'add.php';
        $edit_file = $view_path . 'edit.php';
        $manage_file = $view_path . 'manage.php';
        if (file_exists($add_file)) {
            $handle = fopen($add_file, 'w') or die('Cannot open file:  ' . $add_file);
        } else {
            mkdir($ori_path . $table, 0700);
            $handle = fopen($add_file, 'w') or die('Cannot open file:  ' . $add_file);
        }

        $current = "";
        $myfile = fopen($controller_path . "module_files/add.php", "r") or die("Unable to open file!");
        $current = fread($myfile, filesize($controller_path . "module_files/add.php"));
        fclose($myfile);

        file_put_contents($add_file, $current);
        $data = file_get_contents($add_file);

        $data = str_replace("@@@", "$", $data);
        $data = str_replace("cntlr", $cntlr, $data);

        $formfields = "";
        foreach ($_POST['ischeck'] as $key => $value) {
            if (isset($value) && !empty($value)) {

                if ($_POST[$value][0] == "input") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	  <div class="col-sm-4">
	    <input type="text" class="form-control" id="' . $value . '" name="' . $value . '" 
	    value="<?php echo set_value("' . $value . '"); ?>"
	    >
	  </div>
	  <div class="col-sm-5" >
	    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
	  </div>
	</div> 
	<!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "date") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	  <div class="col-sm-4">
	    <input type="text" class="form-control span2 datepicker" id="' . $value . '" name="' . $value . '" value="<?php echo set_value("' . $value . '","' . date('Y-m-d') . '"); ?>"	    >
	  </div>
	  <div class="col-sm-5" >
	    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
	  </div>
	</div> 
	<!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "time") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	  <div class="col-sm-4 clockpicker" data-autoclose="true">
	    <input type="text" class="form-control" value="09:30" name="' . $value . '">
	  </div>
	  <div class="col-sm-5" >
	    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
	  </div>
	</div>
	<!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "datetime") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	  <div class="col-sm-4">
	    <input type="text" class="form-control datetimepicker" id="' . $value . '"  name="' . $value . '"/>
	  </div>
	  <div class="col-sm-5" >
	    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
	  </div>
	</div>
	<!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "textarea") {
                    $formfields .= '
			<!-- ' . ucfirst($value) . ' Start -->
			<div class="form-group">
			  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
			  <div class="col-sm-4">
			    <textarea class="form-control" id="' . $value . '" name="' . $value . '"><?php echo set_value("' . $value . '"); ?></textarea>
			  </div>
			  <div class="col-sm-5" >
			    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
			  </div>
			</div> 
			<!-- ' . ucfirst($value) . ' End -->
			';
                }
                elseif ($_POST[$value][0] == "select") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
        <label class="control-label col-md-3"> ' . ucfirst($value) . ' </label>
          <div class="col-md-4">
              <select class="form-control select2" name="' . $value . '" id="' . $value . '">
              <option value="">Select ' . ucfirst($value) . '</option>
      <?php 
      if(isset($' . $_POST[$value]['selected_table'] . ') && !empty($' . $_POST[$value]['selected_table'] . ')):
      foreach($' . $_POST[$value]['selected_table'] . ' as $key => $value): ?>
          <option value="<?php echo $value->' . $_POST[$value]['key'] . '; ?>">
            <?php echo $value->' . $_POST[$value]['value'] . '; ?>
          </option>
      <?php endforeach; ?>
      <?php endif; ?>
      </select>
        </div>
    </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "status") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
        <label class="control-label col-md-3">' . ucfirst($value) . '</label>
         <div class=" col-md-4 switch">
                    <div class="onoffswitch">
     <input type="checkbox" class="onoffswitch-checkbox" checked data-on-label="Yes" data-off-label="No"  name="' . $value . '" value="1" id="' . $value . '" <?php echo set_checkbox("' . $value . '","1")?>/>
    <?php echo form_error("' . $value . '","<span class=err-msg>,</span>")?>
                        <label class="onoffswitch-label" for="' . $value . '">
                            <span class="onoffswitch-switch"></span>
                            <span class="onoffswitch-inner"></span>
                        </label>
                    </div>
                </div>
      </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "radio") {
                    $formfields .= '
 <!-- ' . ucfirst($value) . ' Start -->
 <div class="form-group">
          <label class="col-sm-3 control-label">Select ' . ucfirst($value) . '</label>
          <div class="col-sm-4">';
                    $rad_arr = $_POST[$value]['radios'];
                    for ($aaa = 0; $aaa < count($rad_arr); $aaa++) {
                        $formfields .= '
            <span style="margin-right:20px;"><input type="radio" style="width:20px; height:20px;" name="' . $value . '" value="' . $rad_arr[$aaa] . '"> ' . $rad_arr[$aaa] . ' </span>';
                    }
                    $formfields .= '
        </div>
    </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "checkbox") {
                    $formfields .= '
 <!-- ' . ucfirst($value) . ' Start -->
 <div class="form-group">
          <label class="col-sm-3 control-label">Select ' . ucfirst($value) . '</label>
          <div class="col-sm-4">';
                    $rad_arr = $_POST[$value]['checks'];
                    for ($aaa = 0; $aaa < count($rad_arr); $aaa++) {
                        $formfields .= '
            <span style="margin-right:20px;"><input type="checkbox" style="width:20px; height:20px;" name="' . $value . '[]" value="' . $rad_arr[$aaa] . '"> ' . $rad_arr[$aaa] . ' </span>';
                    }
                    $formfields .= '
        </div>
    </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "image") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
    <div class="form-group">
      <label for="address" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
      <div class="col-sm-6">
      <input type="file" name="' . $value . '" />
      <input type="hidden" name="old_' . $value . '" value="<?php if (isset($' . $value . ') && $' . $value . '!=""){echo $' . $value . '; } ?>" />
        <?php if(isset($' . $value . '_err) && !empty($' . $value . '_err)) 
        { foreach($' . $value . '_err as $key => $error)
        { echo "<div class=\"error-msg\">' . $error . '</div>"; } }?>
      </div>
        <div class="col-sm-3" >
      </div>
    </div>
    <!-- ' . ucfirst($value) . ' End -->
    ';
                }
            }
        }

        if (isset($_POST["multiselect"])) {
            for ($i=0; $i < count($_POST["multiselect"]["table"]); $i++) {
                if ($_POST["multiselect"]["table"][$i]) {
                    $value = $_POST["multiselect"]["table"][$i];
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
    <div class="form-group">
        <label class="control-label col-md-3"> ' . ucfirst($value) . ' </label>
          <div class="col-md-4">
              <select class="form-control select2" name="' . $value . '[]" id="' . $value . '" multiple="multiple">
              <option value="">Select ' . ucfirst($value) . '</option>
      <?php 
      if(isset($' . $value . ') && !empty($' . $value . ')):
      foreach($' . $value . ' as $key => $value): ?>
          <option value="<?php echo $value->' . $_POST["multiselect"]['key'][$i] . '; ?>">
            <?php echo $value->' . $_POST["multiselect"]['value'][$i] . '; ?>
          </option>
      <?php endforeach; ?>
      <?php endif; ?>
      </select>
        </div>
    </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
            }
        }

        $data = str_replace("==formfields==", $formfields, $data);
        $data = str_replace("singlequote", "'", $data);
        file_put_contents($add_file, $data);

        $ori_path = $this->config->item('base_path') . "application/views/admin/";
        $view_path = $this->config->item('base_path') . "application/views/admin/$table/";
        $edit_file = $view_path . 'edit.php';
        if (file_exists($edit_file)) {
            $handle = fopen($edit_file, 'w') or die('Cannot open file:  ' . $edit_file);
        } else {
            mkdir($ori_path . $table, 0700);
            $handle = fopen($edit_file, 'w') or die('Cannot open file:  ' . $edit_file);
        }

        $current = "";
        $myfile = fopen($controller_path . "module_files/edit.php", "r") or die("Unable to open file!");
        $current = fread($myfile, filesize($controller_path . "module_files/add.php"));
        fclose($myfile);

        file_put_contents($edit_file, $current);
        $data = file_get_contents($edit_file);

        $data = str_replace("@@@", "$", $data);
        $data = str_replace("cntlr", $cntlr, $data);

        $formfields = "";
        foreach ($_POST['ischeck'] as $key => $value) {
            if (isset($value) && !empty($value)) {
                if ($_POST[$value][0] == "input") {
                    $formfields .= '
<!-- ' . ucfirst($value) . ' Start -->
<div class="form-group">
  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
  <div class="col-sm-4">
    <input type="text" class="form-control" id="' . $value . '" name="' . $value . '" 
    value="<?php echo set_value("' . $value . '",html_entity_decode($' . $table . '->' . $value . ')); ?>"
    >
  </div>
  <div class="col-sm-5" >
    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
  </div>
</div> 
<!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "textarea") {
                    $formfields .= '
<!-- ' . ucfirst($value) . ' Start -->
<div class="form-group">
  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
  <div class="col-sm-4">
    <textarea class="form-control" id="' . $value . '" name="' . $value . '"><?php echo set_value("' . $value . '",html_entity_decode($' . $table . '->' . $value . ')); ?></textarea>
  </div>
  <div class="col-sm-5" >
    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
  </div>
</div> 
<!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "date") {
                    $formfields .= '
<!-- ' . ucfirst($value) . ' Start -->
<div class="form-group">
  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
  <div class="col-sm-4">
    <input type="text" class="form-control span2 datepicker" id="' . $value . '" name="' . $value . '" 
    value="<?php echo set_value("' . $value . '",$' . $table . '->' . $value . '); ?>"
    >
  </div>
  <div class="col-sm-5" >
    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
  </div>
</div> 
<!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "time") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	  <div class="col-sm-4 clockpicker" data-autoclose="true">
	 <input type="text" class="form-control" value="<?php echo set_value("' . $value . '",$' . $table . '->' . $value . '); ?>" name="' . $value . '" id="' . $value . '">
	  </div>
	  <div class="col-sm-5" >
	    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
	  </div>
	</div> 
	<!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "datetime") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	  <div class="col-sm-4">
	    <input type="text" class="form-control datetimepicker" name="' . $value . '" id="' . $value . '" value="<?php echo set_value("' . $value . '",$' . $table . '->' . $value . '); ?>"/> 
	  </div>
	  <div class="col-sm-5" >
	    <?php echo form_error("' . $value . '","<span class=singlequotelabel label-dangersinglequote>","</span>")?>
	  </div>
	</div> 
	<!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "select") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	<div class="form-group">
        <label class="control-label col-md-3"> ' . ucfirst($value) . ' </label>
          <div class="col-md-4">
              <select class="form-control select2" name="' . $value . '" id="' . $value . '">
              <option value="">Select ' . ucfirst($value) . '</option>
      <?php 
      if(isset($' . $_POST[$value]['selected_table'] . ') && !empty($' . $_POST[$value]['selected_table'] . ')):
      foreach($' . $_POST[$value]['selected_table'] . ' as $key => $value): ?>
          <option value="<?php echo $value->' . $_POST[$value]['key'] . '; ?>" <?php echo $value->' . $_POST[$value]['key'] . '==$' . $table . '->' . $value . '?\'selected="selected"\':""; ?>>
            <?php echo $value->' . $_POST[$value]['value'] . '; ?>
          </option>
      <?php endforeach; ?>
      <?php endif; ?>
      </select>
        </div>
    </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "status") {
                    $formfields .= '
	<!-- ' . ucfirst($value) . ' Start -->
	 <div class="form-group">
        <label class="control-label col-md-3">' . $value . '
        </label>                    
         <div class=" col-md-4 switch">
                    <div class="onoffswitch">
     <input type="checkbox" class="onoffswitch-checkbox"  data-on-label="Yes" data-off-label="No"  name="' . $value . '" value="1" id="' . $value . '" <?php if(set_value("' . $value . '",$' . $table . '->' . $value . ' == 1)){echo "checked=checked";}?>/>
      <?php echo form_error("' . $value . '","<span class=err-msg>","</span>")?>
                        <label class="onoffswitch-label" for="' . $value . '">
                            <span class="onoffswitch-switch"></span>
                            <span class="onoffswitch-inner"></span>
                        </label>
                    </div>
                </div>
      </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
                elseif ($_POST[$value][0] == "radio") {
                    $formfields .= '
	 <!-- ' . ucfirst($value) . ' Start -->
	 <div class="form-group">
	          <label class="col-sm-3 control-label">Select ' . ucfirst($value) . '</label>
	          <div class="col-sm-4">';
                    $rad_arr = $_POST[$value]['radios'];
                    for ($aaa = 0; $aaa < count($rad_arr); $aaa++) {
                        $formfields .= '
	            <span style="margin-right:20px;"><input type="radio" style="width:20px; height:20px;" <?php echo $' . $table . '->' . $value . '=="' . $rad_arr[$aaa] . '"?\'checked="checked"\':""; ?> name="' . $value . '" value="' . $rad_arr[$aaa] . '"> ' . $rad_arr[$aaa] . ' </span>';
                    }
                    $formfields .= '
	        </div>
	    </div>
	      <!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "checkbox") {
                    $formfields .= '
		<!-- ' . ucfirst($value) . ' Start -->
		<div class="form-group">
		<label class="col-sm-3 control-label">Select ' . ucfirst($value) . '</label>
		<div class="col-sm-4">
		<?php $arr=explode(", ", $' . $table . '->' . $value . ') ?>
		';
                    $rad_arr = $_POST[$value]['checks'];
                    for ($aaa = 0; $aaa < count($rad_arr); $aaa++) {
                        $formfields .= '
			<span style="margin-right:20px;"><input type="checkbox" style="width:20px; height:20px;" <?php echo in_array("' . $rad_arr[$aaa] . '", $arr)?\'checked="checked"\':""; ?> name="' . $value . '[]" value="' . $rad_arr[$aaa] . '"> ' . $rad_arr[$aaa] . ' </span>';
                    }
                    $formfields .= '
	</div>
	</div>
	<!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "image") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
    <div class="form-group">
      <label for="address" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
      <div class="col-sm-6">
      <input type="file" name="' . $value . '" />
      <input type="hidden" name="old_' . $value . '" 
      value="<?php if (isset($' . $value . ') && $' . $value . '!=""){echo $' . $value . '; }?>" />  
        <?php if(isset($' . $value . '_err) && !empty($' . $value . '_err)) 
        {foreach($' . $value . '_err as $key => $error)
        {echo "<div class=\"error-msg\">' . $error . '</div>"; } }?>
        <?php if (isset($' . $table . '->' . $value . ') && $' . $table . '->' . $value . '!=""){?>
            <br>
  <img src="<?php echo $this->config->item("photo_url");?><?php echo $' . $table . '->' . $value . '; ?>" alt="pic" width="50" height="50" />
    <?php } ?>
      </div>
        <div class="col-sm-3" >
      </div>
    </div>
    <!-- ' . ucfirst($value) . ' End -->
    ';
                }
            }
        }

        if (isset($_POST["multiselect"])) {
            for ($i=0; $i < count($_POST["multiselect"]["table"]); $i++) {
                if ($_POST["multiselect"]["table"][$i]) {
                    $value = $_POST["multiselect"]["table"][$i];
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
    <div class="form-group">
        <label class="control-label col-md-3"> ' . ucfirst($value) . ' </label>
          <div class="col-md-4">
              <select class="form-control select2" name="' . $value . '[]" id="' . $value . '" multiple="multiple">
              <option value="">Select ' . ucfirst($value) . '</option>
      <?php 
      if(isset($' . $value . ') && !empty($' . $value . ')):
      foreach($' . $value . ' as $key => $value): ?>
          <option <?php if(in_array($value->' . $_POST["multiselect"]['key'][$i] . ', $selected_'.$_POST["multiselect"]["table"][$i].')){ echo "selected"; } ?> value="<?php echo $value->' . $_POST["multiselect"]['key'][$i] . '; ?>">
            <?php echo $value->' . $_POST["multiselect"]['value'][$i] . '; ?>
          </option>
      <?php endforeach; ?>
      <?php endif; ?>
      </select>
        </div>
    </div>
      <!-- ' . ucfirst($value) . ' End -->
';
                }
            }
        }

        $data = str_replace("==formfields==", $formfields, $data);
        $data = str_replace("singlequote", "'", $data);
        file_put_contents($edit_file, $data);

        $ori_path = $this->config->item('base_path') . "application/views/admin/";
        $view_path = $this->config->item('base_path') . "application/views/admin/$table/";
        $edit_file = $view_path . 'view.php';
        if (file_exists($edit_file)) {
            $handle = fopen($edit_file, 'w') or die('Cannot open file:  ' . $edit_file);
        } else {
            mkdir($ori_path . $table, 0700);
            $handle = fopen($edit_file, 'w') or die('Cannot open file:  ' . $edit_file);
        }

        $current = "";
        $myfile = fopen($controller_path . "module_files/view.php", "r") or die("Unable to open file!");
        $current = fread($myfile, filesize($controller_path . "module_files/add.php"));
        fclose($myfile);

        file_put_contents($edit_file, $current);
        $data = file_get_contents($edit_file);

        $data = str_replace("@@@", "$", $data);
        $data = str_replace("cntlr", $cntlr, $data);

        $formfields = "
<table class='table table-bordered' style='width:70%;' align='center'>";
        foreach ($_POST['ischeck'] as $key => $value) {
            if (isset($value) && !empty($value)) {
                if ($_POST[$value][0] == "input") {
                    $formfields .= '
	<tr>
	 <td>
	   <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	 </td>
	 <td> 
	   <?php echo set_value("' . $value . '",html_entity_decode($' . $table . '->' . $value . ')); ?>
	 </td>
	</tr>
	';
                }
                elseif ($_POST[$value][0] == "date" || $_POST[$value][0] == "time" || $_POST[$value][0] == "datetime") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
	<tr>
	 <td>
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	 </td>
	 <td> 
	   <?php echo set_value("' . $value . '", html_entity_decode($' . $table . '->' . $value . ')); ?>
	 </td>
	</tr>
    <!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "textarea") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
	<tr>
	 <td>
	  <label for="' . $value . '" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	 </td>
	 <td> 
	   <?php echo set_value("' . $value . '",  html_entity_decode($' . $table . '->' . $value . ')); ?>
	 </td>
	</tr>
    <!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "select") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
	<tr>
	 <td>
	  <label class="control-label col-md-3"> ' . ucfirst($value) . ' </label>
	 </td>
	 <td> 
	   <?php 
	      if(isset($' . $_POST[$value]['selected_table'] . ') && !empty($' . $_POST[$value]['selected_table'] . ')):
	      foreach($' . $_POST[$value]['selected_table'] . ' as $key => $value): 
	       if($value->' . $_POST[$value]['key'] . '==$' . $table . '->' . $value . ')
	             echo $value->' . $_POST[$value]['value'] . ';
	       endforeach; 
	       endif; ?>
	 </td>
	</tr>
    <!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "status") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
	<tr>
	 <td>
	  <label class="control-label col-md-3">' . $value . '</label>
	 </td>
	 <td> 
	   <?php if(set_value("' . $value . '",$' . $table . '->' . $value . ' == 1)){echo "Active";}else{ echo "Inactive";}?>
	 </td>
	</tr>
    <!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "radio") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
	<tr>
	 <td>
	  <label class="col-sm-3 control-label">Select ' . ucfirst($value) . '</label>
	 </td>
	 <td> 
	   ';
                    $rad_arr = $_POST[$value]['radios'];
                    for ($aaa = 0; $aaa < count($rad_arr); $aaa++) {
                        $formfields .= '
	   <?php echo $' . $table . '->' . $value . '=="' . $rad_arr[$aaa] . '"?\'' . $rad_arr[$aaa] . '\':""; ?>';
                    }
                    $formfields .= '
	 </td>
	</tr>
    <!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "checkbox") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
	<tr>
	 <td>
	  <label class="col-sm-3 control-label">' . ucfirst($value) . '</label>
	 </td>
	 <td> 
	   <?php $arr=explode(", ", $' . $table . '->' . $value . ') ?>
	          ';
                    $rad_arr = $_POST[$value]['checks'];
                    for ($aaa = 0; $aaa < count($rad_arr); $aaa++) {
                        $formfields .= '
	            <span style="margin-left:5px;"><?php echo in_array("' . $rad_arr[$aaa] . '", $arr)?\'' . $rad_arr[$aaa] . ', \':""; ?></span>';
                    }
                    $formfields .= '
	 </td>
	</tr>
    <!-- ' . ucfirst($value) . ' End -->
	';
                }
                elseif ($_POST[$value][0] == "image") {
                    $formfields .= '
    <!-- ' . ucfirst($value) . ' Start -->
	<tr>
	 <td>
	  <label for="address" class="col-sm-3 control-label"> ' . ucfirst($value) . ' </label>
	 </td>
	 <td>
	 <?php if (isset($' . $table . '->' . $value . ') && $' . $table . '->' . $value . '!=""){?>
	            <br>
	    <img src="<?php echo $this->config->item("photo_url");?><?php echo $' . $table . '->' . $value . '; ?>" alt="pic" width="50" height="50" />
	    <?php } ?>
	 </td>
	</tr>
    <!-- ' . ucfirst($value) . ' End -->
	';
                }
            }
        }

        if (isset($_POST["multiselect"])) {
            for ($i=0; $i < count($_POST["multiselect"]["table"]); $i++) {
                if ($_POST["multiselect"]["table"][$i]) {
                    $rtable = $_POST["multiselect"]["r_table"][$i];
                    $field1 = $_POST["multiselect"]["r_main"][$i];
                    $field2 = $_POST["multiselect"]["r_multi"][$i];
                    $call_multi_add .= "\n\t@@@this->==table==->multiSelectInsert(\"$rtable\", \"$field2\", @@@insert_id, \"$field1\", @@@_POST['".$_POST["multiselect"]["table"][$i]."']);\n";
                    $call_multi_edit .= "\n\t@@@this->==table==->multiSelectInsert(\"$rtable\", \"$field2\", @@@id, \"$field1\", @@@_POST['".$_POST["multiselect"]["table"][$i]."']);\n";
                    $list_tbl .= "\n\t@@@data['".$_POST["multiselect"]["table"][$i]."']=@@@this->==table==->getList('".$_POST["multiselect"]["table"][$i]."');\n";
                    $return_multi_selected_id.= "\n\t@@@data['selected_".$_POST["multiselect"]["table"][$i]."'] = @@@this->==table==->getSelectedIds(\"$rtable\", @@@id, \"$field1\", \"$field2\");\n";
                    $formfields .= '
                <!-- ' . ucfirst($_POST["multiselect"]["table"][$i]) . ' Start -->
                <tr>
                 <td>
                  <label for="address" class="col-sm-3 control-label"> ' . ucfirst($rtable) . ' </label>
                 </td>
                 <td>
                 <?php echo implode(", ", $selected_'.$_POST["multiselect"]["table"][$i].'_data); ?>
                 </td>
                </tr>
                <!-- ' . ucfirst($_POST["multiselect"]["table"][$i]) . ' End -->
                ';
                }
            }
        }

        $formfields .= '<tr><td colspan="2"><a type="reset" class="btn btn-info pull-right" onclick="history.back()">Back</a></td></tr></table>';
        $data = str_replace("==formfields==", $formfields, $data);
        $data = str_replace("singlequote", "'", $data);

        file_put_contents($edit_file, $data);

        $ori_path = $this->config->item('base_path') . "application/views/admin/";
        $view_path = $this->config->item('base_path') . "application/views/admin/$table/";
        $manage_file = $view_path . 'manage.php';
        if (file_exists($manage_file)) {
            $handle = fopen($manage_file, 'w') or die('Cannot open file:  ' . $manage_file);
        } else {
            mkdir($ori_path . $table, 0700);
            $handle = fopen($manage_file, 'w') or die('Cannot open file:  ' . $manage_file);
        }

        $current = "";
        $myfile = fopen($controller_path . "module_files/manage.php", "r") or die("Unable to open file!");
        $current = fread($myfile, filesize($controller_path . "module_files/manage.php"));
        fclose($myfile);

        file_put_contents($manage_file, $current);
        $data = file_get_contents($manage_file);

        $data = str_replace("@@@", "$", $data);
        $data = str_replace("cntlr", $cntlr, $data);

        $option_fields = "";
        $tableheadrows = '<?php $sortSym=isset($_GET["order"]) && $_GET["order"]=="asc" ? "up" : "down"; ?>';
        $tabledatarows = "<th><input name='input' id='del' onclick=\"callme('show')\"  type='checkbox' class='del' value='<?php echo @@@value->".$primary_key."; ?>'/></th>
            <th><?php if(!empty(@@@value->".$primary_key.")){ echo @@@count; @@@count++; }?></th>";
        foreach ($_POST['ischeck'] as $key => $value) {
            if (isset($value) && !empty($value)) {
                if ($_POST[$value][0] == 'select') {
                    $tableheadrows .= '
				<?php
				 $symbol = isset($_GET["sortBy"]) && $_GET["sortBy"]=="' . $_POST[$value]["selected_table"] . '.' . $_POST[$value]["value"] . '"?"<i class=\'fa fa-sort-$sortSym\' aria-hidden=\'true\'></i>": "<i class=\'fa fa-sort\' aria-hidden=\'true\'></i>"; ?>
				<th> <a href="<?php echo $fields_links["' . $_POST[$value]["selected_table"] . '.' . $_POST[$value]["value"] . '"]; ?>" class="link_css"> ' . ucfirst($value) . ' <?php echo $symbol ?></a></th>
   						';
                    $option_fields .= '<option value="' . $_POST[$value]["selected_table"] . '.' . $_POST[$value]["value"] . '" <?php echo $searchBy=="' . $_POST[$value]["selected_table"] . '.' . $_POST[$value]["value"] . '"?\'selected="selected"\':""; ?>>' . ucfirst($value) . '</option>';
                } else {
                    $tableheadrows .= '
				<?php $symbol = isset($_GET["sortBy"]) && $_GET["sortBy"]=="' . $value . '"?"<i class=\'fa fa-sort-$sortSym\' aria-hidden=\'true\'></i>": "<i class=\'fa fa-sort\' aria-hidden=\'true\'></i>"; ?>
				<th> <a href="<?php echo $fields_links["' . $value . '"]; ?>" class="link_css"> ' . ucfirst($value) . ' <?php echo $symbol ?></a></th>
						';
                    $option_fields .= '<option value="' . $value . '" <?php echo $searchBy=="' . $value . '"?\'selected="selected"\':""; ?>>' . ucfirst($value) . '</option>';
                }

                if ($_POST[$value][0] == 'status') {
                    $tabledatarows .= '<th><a href="<?php echo base_url()?>admin/' . $table . '/status/' . $value . '/<?php echo @@@value->'.$primary_key.'."?redirect=".current_url()."?".urlencode($_SERVER["QUERY_STRING"]); ?>">
                        <?php if(!empty(@@@value->' . $value . ') and @@@value->' . $value . '==1 )
                        { echo "Active"; }else{ echo "Inactive";}?>
                       </a></th>
                ';
                } elseif ($_POST[$value][0] == 'image') {
                    $tabledatarows .= '<th><?php if(!empty(@@@value->' . $value . ')){ ?>
                        <img src="<?php echo $this->config->item(\'photo_url\');?><?php echo @@@value->' . $value . '; ?>" alt="pic" width="50" height="50" />
                         <?php }?></th>';
                } else {
                    $tabledatarows .= '<th><?php if(!empty(@@@value->' . $value . ')){ echo @@@value->' . $value . '; }?></th>
                ';
                }
            }
        }

        $all_one_to_many_relations = json_decode($_POST['one_to_many']);
        foreach ($all_one_to_many_relations as $key => $value) {
            $one_to_many_table = $value->rel_table;
            $one_to_many_field = $value->rel_field;

            if (isset($one_to_many_table) && !empty($one_to_many_table)) {
                $tableheadrows .= '<th class="action-width">'.$one_to_many_table.'</th>';
                $tabledatarows .= '<th class="action-width">
                       <a href="<?php echo base_url()?>admin/' .$one_to_many_table.'/index/'.$one_to_many_field.'/<?php echo @@@value->'.$primary_key.'; ?>/1" title="View">
                        <span class="btn btn-info " >
                            ' . $one_to_many_table . '
                        </span>
                       </a>
                       </th>';
            }
        }

        $tabledatarows .= '<th class="action-width">
		   <a href="<?php echo base_url()?>admin/' . $table_new . '/view/<?php echo @@@value->'.$primary_key.'; ?>" title="View">
            <span class="btn btn-info " ><i class="fa fa-eye"></i></span>
           </a>
           <a href="<?php echo base_url()?>admin/' . $table_new . '/edit/<?php echo @@@value->'.$primary_key.'; ?>" title="Edit">
            <span class="btn btn-info " ><i class="fa fa-edit"></i></span>
           </a>
           <a  title="Delete" data-toggle="modal" data-target="#commonDelete" onclick="set_common_delete(\'<?php echo @@@value->'.$primary_key.'; ?>\',\'' . $table_new . '\');">
           <span class="btn btn-info " ><i class="fa fa-trash-o "></i></span>
           </a>
            </th>';
        $row_id = " id=\"hide<?php echo @@@value->".$primary_key."; ?>\" ";
        $data = str_replace("==tableheadrows==", $tableheadrows, $data);
        $data = str_replace("==tabledatarows==", $tabledatarows, $data);
        $data = str_replace("==searchoptions==", $option_fields, $data);
        $data = str_replace("==table==", $table_new, $data);
        $data = str_replace("++id++", $row_id, $data);
        $data = str_replace("@@@", "$", $data);
        $data = str_replace("singlequote", "'", $data);
        file_put_contents($manage_file, $data);

        $this->session->set_flashdata('message', 'Module created Successfully!');
        redirect('admin/module');
    }

    private function get_modules() {
        $modules = array();
        $files = get_filenames('application/controllers/admin');
        foreach ($files as $file) {
            if ($file != 'Module.php' && $file != 'index.html' && $file != 'module_files') {
                $module_name = str_replace('.php', '', $file);
                $modules[] = $module_name;
            }
        }
        return $modules;
    }
    function delete_module($module_name) {
        if (!$this->ion_auth->logged_in()) {
            redirect('/auth/login/');
        }

        $module_name = ucfirst($module_name);
        $controller_path = 'application/controllers/admin/' . $module_name . '.php';
        $model_path = 'application/models/admin/' . $module_name . '_model.php';
        $view_path = 'application/views/admin/' . strtolower($module_name);

        if (file_exists($controller_path)) {
            unlink($controller_path);
        }
        if (file_exists($model_path)) {
            unlink($model_path);
        }
        if (is_dir($view_path)) {
            delete_files($view_path, TRUE);
            rmdir($view_path);
        }

        $file = 'application/views/header.php';
        $data = file_get_contents($file);
        $menu_to_remove = '<!-- BO : ' . $module_name . ' -->';
        $end_menu_to_remove = '<!--  EO : ' . $module_name . ' -->';
        $data = preg_replace('/' . preg_quote($menu_to_remove, '/') . '.*?' . preg_quote($end_menu_to_remove, '/') . '/s', '', $data);
        file_put_contents($file, $data);

        $this->session->set_flashdata('message', 'Module ' . $module_name . ' deleted successfully!');
        redirect('admin/module');
    }

    public function edit_module() {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login');
        }

        $module_name = $this->input->post('module_name');
        $new_module_name = $this->input->post('new_module_name');
        $new_icon = $this->input->post('new_icon');

        $file = 'application/views/header.php';
        $data = file_get_contents($file);

        $old_menu_item = '<!-- BO : ' . ucfirst($module_name) . ' -->';
        $new_menu_item = '<!-- BO : ' . ucfirst($new_module_name) . ' -->';

        $old_icon_class = '<i class="fa fa-users"></i>';
        $new_icon_class = '<i class="fa ' . $new_icon . '"></i>';

        $data = str_replace($old_menu_item, $new_menu_item, $data);
        $data = str_replace(ucfirst($module_name), ucfirst($new_module_name), $data);
        $data = str_replace($old_icon_class, $new_icon_class, $data);

        file_put_contents($file, $data);

        $this->session->set_flashdata('message', 'Module ' . $module_name . ' updated successfully!');
        redirect('admin/module');
    }
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */